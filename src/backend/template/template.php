<?php

//TODO: 入出力構造を定義する

/**
 * テンプレートの入出力構造を定義します。
 * json形式での入出力を想定しています。
 * 実際に書く際には以下の例のように記述します。
 * 
 * ageの数だけdata配列に入力のmessageを格納し返します。
 * 入力: {
 *      messsge: "こんにちは",
 *      csrf_token: "CSRFトークン",
 *      age: 20
 * }
 * 出力: {
 *      success: true/false,
 *      message: "処理結果のメッセージ",
 *      data: [
 *          {message: "入力.message"},
 *          ...
 *      ]
 * }
 */

//SECTION: 共通 - 入力の取得
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: " . getenv("APP_URL"));
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($data === null) {
    echo json_encode([
        "success" => false,
        "message" => "JSONのデコードに失敗しました。",
        "data" => []
    ]);
    exit;
}
//!SECTION

//SECTION: 入力のバリデーション
//TODO: 必要なパラメータのバリデーションを行う
$message = $data['message'] ?? null;
$age = $data['age'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;
$sessionCsrfToken = new Lib\CSRFToken();

if ($message === null || $age === null || $csrfToken === null) {
    echo json_encode([
        "success" => false,
        "message" => "必要なパラメータが不足しています。",
        "data" => [$message, $age, $csrfToken]
    ]);
    exit;
}

if (!$sessionCsrfToken->validateToken($csrfToken)) {
    echo json_encode([
        "success" => false,
        "message" => "Bad CSRF Token:" . $csrfToken . "\tto:" . $sessionCsrfToken->generateToken(),
        "data" => []
    ]);
    exit;
}
//!SECTION

//SECTION: 処理の実行
//TODO: 入力に基づく処理を実行する

$resultData = [];
for ($i = 0; $i < $age; $i++) {
    $resultData[] = ["message" => $message];
}
echo json_encode([
    "success" => true,
    "message" => "処理が成功しました。",
    "data" => $resultData
]);
//!SECTION