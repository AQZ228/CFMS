<?php
declare(strict_types=1);

// SECTION: 共通 - 入力の取得

header("Content-Type: application/json; charset=UTF-8");

// CORS設定
$appUrl = getenv('APP_URL');

if (
    is_string($appUrl) &&
    $appUrl !== '' &&
    filter_var($appUrl, FILTER_VALIDATE_URL) !== false
) {
    $parsedUrl = parse_url($appUrl);

    if (isset($parsedUrl['scheme'], $parsedUrl['host'])) {
        $origin = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        if (isset($parsedUrl['port'])) {
            $origin .= ':' . $parsedUrl['port'];
        }

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// CORSプリフライトリクエストへの対応
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// autoload.phpの読み込み
// 実際のファイル配置に合わせてパスを指定してください。
require_once __DIR__ . '/../../vendor/autoload.php';

// SECTION: 共通 - レスポンスの送信

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

// SECTION: HTTPメソッドの確認

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Allow: POST, OPTIONS");

    sendLoginResponse(
        false,
        "許可されていないHTTPメソッドです。",
        [],
        405
    );
}

// SECTION: リクエストデータの取得

$rawData = file_get_contents('php://input');
$requestData = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($requestData)) {
    sendLoginResponse(
        false,
        "リクエストデータが正しくありません。",
        [],
        400
    );
}

// SECTION: 入力値の取得と検証

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

if (mb_strlen($staffId, 'UTF-8') > 100) {
    sendLoginResponse(
        false,
        "スタッフIDは100文字以内で入力してください。",
        [],
        400
    );
}

if (mb_strlen($password, 'UTF-8') > 256) {
    sendLoginResponse(
        false,
        "パスワードは256文字以内で入力してください。",
        [],
        400
    );
}

// SECTION: CSRFトークンの検証・スタッフ認証

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
        [':staffId' => $staffId]
    );

    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    $staffDataValid = (
        is_array($staff) &&
        isset(
            $staff['id'],
            $staff['name'],
            $staff['password'],
            $staff['role']
        ) &&
        (is_int($staff['id']) || is_string($staff['id'])) &&
        is_string($staff['name']) &&
        is_string($staff['password']) &&
        is_string($staff['role']) &&
        $staff['name'] !== '' &&
        $staff['password'] !== '' &&
        ctype_digit((string) $staff['id'])
    );

    $dummyPasswordHash =
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.he5eXk7p1e';

    $passwordHash = $staffDataValid
        ? $staff['password']
        : $dummyPasswordHash;

    $passwordVerified = password_verify($password, $passwordHash);

    if (!$staffDataValid || !$passwordVerified) {
        $csrf->clearToken();
        $newCsrfToken = $csrf->generateToken();

        sendLoginResponse(
            false,
            "スタッフIDまたはパスワードが正しくありません。",
            ["csrfToken" => $newCsrfToken],
            401
        );
    }

    if (!in_array($staff['role'], ['staff', 'admin'], true)) {
        $csrf->clearToken();
        $newCsrfToken = $csrf->generateToken();

        sendLoginResponse(
            false,
            "このアカウントは利用できません。",
            ["csrfToken" => $newCsrfToken],
            403
        );
    }

    // SECTION: セッションの更新

    $session = new Lib\Session();
    $session->regenerateId(true);

    $csrf->clearToken();
    $newCsrfToken = $csrf->generateToken();

    $sessionStaff = [
        "id" => (int) $staff['id'],
        "staffId" => $staff['name'],
        "displayName" => $staff['name'],
        "role" => $staff['role']
    ];

    $session->set('staff', $sessionStaff);

    // SECTION: ログイン成功レスポンス

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
