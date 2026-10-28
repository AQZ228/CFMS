<?php
declare(strict_types=1);

/**
 * 説明:
 * スタッフIDとパスワードを使用してスタッフ認証を行います。
 *
 * 入力:
 * {
 *     "staffId": "スタッフID",
 *     "password": "パスワード",
 *     "csrfToken": "CSRFトークン"
 * }
 *
 * 出力:
 * {
 *     "success": true/false,
 *     "message": "処理結果のメッセージ",
 *     "data": {
 *         "staff": {
 *             "staffId": "スタッフID",
 *             "displayName": "表示名",
 *             "role": "staff/admin"
 *         },
 *         "csrfToken": "新しいCSRFトークン"
 *     }
 * }
 */

// SECTION: 共通 - 入力の取得

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: " . getenv("APP_URL"));
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

/**
 * JSONレスポンスを返して処理を終了します。
 */
function sendLoginResponse(
    bool $success,
    string $message,
    array $data = [],
    int $statusCode = 200
): void {
    http_response_code($statusCode);

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Allow: POST");

    sendLoginResponse(
        false,
        "許可されていないHTTPメソッドです。",
        [],
        405
    );
}

$rawData = file_get_contents('php://input');
$requestData = json_decode($rawData, true);

if (
    json_last_error() !== JSON_ERROR_NONE ||
    !is_array($requestData)
) {
    sendLoginResponse(
        false,
        "JSONのデコードに失敗しました。",
        [],
        400
    );
}

// !SECTION


// SECTION: 入力のバリデーション

$staffId = $requestData['staffId'] ?? null;
$password = $requestData['password'] ?? null;
$csrfToken = $requestData['csrfToken'] ?? null;

if (
    !is_string($staffId) ||
    trim($staffId) === '' ||
    !is_string($password) ||
    $password === '' ||
    !is_string($csrfToken) ||
    $csrfToken === ''
) {
    sendLoginResponse(
        false,
        "スタッフID、パスワード、CSRFトークンは必須です。",
        [],
        400
    );
}

$staffId = trim($staffId);

// スタッフIDは100文字以内
if (mb_strlen($staffId, 'UTF-8') > 100) {
    sendLoginResponse(
        false,
        "スタッフIDは100文字以内で入力してください。",
        [],
        400
    );
}

// パスワードは256文字以内
if (mb_strlen($password, 'UTF-8') > 256) {
    sendLoginResponse(
        false,
        "パスワードは256文字以内で入力してください。",
        [],
        400
    );
}

// !SECTION


// SECTION: CSRFトークンの検証

try {
    $csrf = new Lib\CSRFToken();

    if (!$csrf->validateToken($csrfToken)) {
        sendLoginResponse(
            false,
            "CSRFトークンが無効です。ページを再読み込みしてください。",
            [],
            403
        );
    }
} catch (\Throwable $e) {
    error_log("Staff login CSRF error: " . $e->getMessage());

    sendLoginResponse(
        false,
        "セッションの処理に失敗しました。",
        [],
        500
    );
}

// !SECTION


// SECTION: スタッフ認証

try {
    $db = Lib\DB::connect();

    /*
     * 既存のstaffテーブルにstaffIdカラムがないため、
     * 現状の仕様に合わせてnameをスタッフIDとして照合します。
     */
    $stmt = $db->exec(
        "SELECT id, name, password, role
         FROM staff
         WHERE name = :staffId
         LIMIT 1",
        [
            ':staffId' => $staffId
        ]
    );

    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    /*
     * スタッフが存在しない場合もパスワード検証を実行し、
     * 応答時間の差を抑えます。
     */
    $dummyPasswordHash =
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.he5eXk7p1e';

    $passwordHash = (
        is_array($staff) &&
        isset($staff['password']) &&
        is_string($staff['password'])
    )
        ? $staff['password']
        : $dummyPasswordHash;

    $passwordVerified = password_verify(
        $password,
        $passwordHash
    );

    // ログイン失敗時もCSRFトークンを更新
    if (!$staff || !$passwordVerified) {
        $csrf->clearToken();
        $newCsrfToken = $csrf->generateToken();

        sendLoginResponse(
            false,
            "スタッフIDまたはパスワードが正しくありません。",
            [
                "csrfToken" => $newCsrfToken
            ],
            401
        );
    }

    // 利用可能な権限か確認
    if (!in_array($staff['role'], ['staff', 'admin'], true)) {
        // 権限エラー時もCSRFトークンを更新
        $csrf->clearToken();
        $newCsrfToken = $csrf->generateToken();

        sendLoginResponse(
            false,
            "このアカウントは利用できません。",
            [
                "csrfToken" => $newCsrfToken
            ],
            403
        );
    }

    // セッション管理ライブラリを使用
    $session = new Lib\Session();

    // セッション固定攻撃対策：セッションIDを再生成
    $session->regenerateId(true);

    /*
     * 認証状態が変わったため、古いCSRFトークンを破棄し、
     * 新しいトークンを発行します。
     */
    $csrf->clearToken();
    $newCsrfToken = $csrf->generateToken();

    // 認証済みスタッフ情報をセッションに保存
    $sessionStaff = [
        "id" => (int) $staff['id'],
        "staffId" => $staff['name'],
        "displayName" => $staff['name'],
        "role" => $staff['role']
    ];

    $session->set('staff', $sessionStaff);

    // ログイン成功
    sendLoginResponse(
        true,
        "ログインに成功しました。",
        [
            "staff" => [
                "staffId" => $sessionStaff['staffId'],
                "displayName" => $sessionStaff['displayName'],
                "role" => $sessionStaff['role']
            ],
            "csrfToken" => $newCsrfToken
        ]
    );

} catch (\Throwable $e) {
    error_log("Staff login error: " . $e->getMessage());

    sendLoginResponse(
        false,
        "処理中にエラーが発生しました。",
        [],
        500
    );
}

// !SECTION
