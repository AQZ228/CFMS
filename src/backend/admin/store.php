<?php

/**
 * 入力:
 * 新規作成:
 * {
 *     name: "店舗名",
 *     csrf_token: "..."
 * }
 *
 * 更新:
 * {
 *     storeId: 1,
 *     name: "店舗名",
 *     csrf_token: "..."
 * }
 *
 * 出力:
 * {
 *     success: true/false,
 *     store: {
 *         id: 1,
 *         name: "店舗名"
 *     }
 * }
 */

//SECTION: 共通 - 入力の取得
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: " . getenv("APP_URL"));
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST, OPTIONS');

    echo json_encode([
        "success" => false,
        "message" => "POSTメソッドを使用してください。"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($data === null) {
    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。"
    ]);
    exit;
}

//!SECTION

//SECTION: 入力のバリデーション
$name = trim($data['name'] ?? '');
$storeId = $data['storeId'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;

$sessionCsrfToken = new Lib\CSRFToken();

if ($name === '' || $csrfToken === null) {
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。"
    ]);
    exit;
}

if (!$sessionCsrfToken->validateToken($csrfToken)) {
    echo json_encode([
        "success" => false,
        "message" => "CSRFトークンが無効です。"
    ]);
    exit;
}

if ($storeId !== null) {
    $storeId = filter_var($storeId, FILTER_VALIDATE_INT);

    if ($storeId === false || $storeId <= 0) {
        echo json_encode([
            "success" => false,
            "message" => "storeIdが不正です。"
        ]);
        exit;
    }
}

//!SECTION

//SECTION: 処理の実行
try {

    $session = new Lib\Session();

    $staffId = $session->get('staff_id');

    if (empty($staffId)) {
        echo json_encode([
            "success" => false,
            "message" => "ログインしてください。"
        ]);
        exit;
    }

    $db = Lib\DB::connect();

    $stmt = $db->exec(
        "SELECT role
         FROM staff
         WHERE id = ?",
        [$staffId]
    );

    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff || $staff['role'] !== 'admin') {
        echo json_encode([
            "success" => false,
            "message" => "権限がありません。"
        ]);
        exit;
    }

    $db->begin();

    if ($storeId === null) {

        $stmt = $db->exec(
            "SELECT id
             FROM stores
             WHERE name = ?",
            [$name]
        );

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {

            $db->rollback();

            echo json_encode([
                "success" => false,
                "message" => "同じ店舗名が既に存在します。"
            ]);
            exit;
        }

        $db->exec(
            "INSERT INTO stores (
                name
            ) VALUES (
                ?
            )",
            [$name]
        );

        $stmt = $db->exec(
            "SELECT LAST_INSERT_ID() AS id"
        );

        $newStoreId = (int)$stmt
            ->fetch(PDO::FETCH_ASSOC)['id'];

        $stmt = $db->exec(
            "SELECT
                id,
                name
             FROM stores
             WHERE id = ?",
            [$newStoreId]
        );

        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        $db->commit();

        echo json_encode([
            "success" => true,
            "store" => [
                "id" => (int)$store['id'],
                "name" => $store['name']
            ]
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $stmt = $db->exec(
        "SELECT
            id,
            name
         FROM stores
         WHERE id = ?",
        [$storeId]
    );

    $store = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$store) {

        $db->rollback();

        echo json_encode([
            "success" => false,
            "message" => "対象店舗が存在しません。"
        ]);
        exit;
    }

    $stmt = $db->exec(
        "SELECT id
         FROM stores
         WHERE name = ?
           AND id <> ?",
        [
            $name,
            $storeId
        ]
    );

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {

        $db->rollback();

        echo json_encode([
            "success" => false,
            "message" => "同じ店舗名が既に存在します。"
        ]);
        exit;
    }

    $db->exec(
        "UPDATE stores
         SET name = ?
         WHERE id = ?",
        [
            $name,
            $storeId
        ]
    );

    $stmt = $db->exec(
        "SELECT
            id,
            name
         FROM stores
         WHERE id = ?",
        [$storeId]
    );

    $updatedStore = $stmt->fetch(PDO::FETCH_ASSOC);

    $db->commit();

    echo json_encode([
        "success" => true,
        "store" => [
            "id" => (int)$updatedStore['id'],
            "name" => $updatedStore['name']
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {

    if (isset($db)) {
        $db->rollback();
    }

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。"
    ]);
}

//!SECTION