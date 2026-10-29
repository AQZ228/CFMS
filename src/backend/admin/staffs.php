<?php

/**
 * スタッフ一覧を取得するAPI
 * 入力: {}
 * 出力: {
 *     success: true/false,
 *     staffs: [
 *         {
 *             id: 1,
 *             name: "スタッフ名",
 *             role: "admin",
 *             storeIds: [1, 2]
 *         }
 *     ]
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
        "message" => "POSTメソッドを使用してください。",
        "staffs" => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($data === null && $rawData !== '') {
    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。",
        "staffs" => []
    ]);
    exit;
}
//!SECTION

//SECTION: 入力のバリデーション
try {
    $session = new Lib\Session();
    $staffId = $session->get('staff_id');

    if (empty($staffId)) {
        echo json_encode([
            "success" => false,
            "message" => "ログインしてください。",
            "staffs" => []
        ]);
        exit;
    }

    $db = Lib\DB::connect();

    $stmt = $db->exec(
        "SELECT id, role FROM staff WHERE id = ?",
        [$staffId]
    );
    $loginStaff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$loginStaff || $loginStaff['role'] !== 'admin') {
        echo json_encode([
            "success" => false,
            "message" => "権限がありません。",
            "staffs" => []
        ]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "認証に失敗しました。",
        "staffs" => []
    ]);
    exit;
}
//!SECTION

//SECTION: 処理の実行
try {
    $db = Lib\DB::connect();

    $stmt = $db->exec("
        SELECT
            s.id,
            s.name,
            s.role,
            ss.store_id
        FROM staff s
        LEFT JOIN staff_stores ss
            ON s.id = ss.staff_id
        ORDER BY s.id
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $staffs = [];

    foreach ($rows as $row) {
        $id = (int)$row['id'];

        if (!isset($staffs[$id])) {
            $staffs[$id] = [
                'id' => $id,
                'name' => $row['name'],
                'role' => $row['role'],
                'storeIds' => []
            ];
        }

        if ($row['store_id'] !== null) {
            $staffs[$id]['storeIds'][] = (int)$row['store_id'];
        }
    }

    echo json_encode([
        "success" => true,
        "staffs" => array_values($staffs)
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "処理中にエラーが発生しました。",
        "staffs" => []
    ]);
}
//!SECTION