<?php

//TODO: 入出力構造を定義する

/**
 * CSRFトークン発行APIの入出力構造を定義します。
 * json形式での出力を想定しています。
 * 
 * 入力: なし (GETリクエスト等)
 * 
 * 出力: {
 *      "success": true,
 *      "message": "トークンを発行しました。",
 *      "data": {
 *          "csrf_token": "生成されたCSRFトークン"
 *      }
 * }
 */

use Lib\CSRFToken;

//SECTION: 共通 - 入力の取得
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: " . getenv("APP_URL"));
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

//!SECTION

//SECTION: 入力のバリデーション
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "許可されていないHTTPメソッドです。",
        "data" => []
    ]);
    exit;
}
//!SECTION

//SECTION: 処理の実行
//TODO: 入力に基づく処理を実行する

try {
    $csrfManager = new CSRFToken();
    $token = $csrfManager->generateToken();

    echo json_encode([
        "success" => true,
        "message" => "トークンを発行しました。",
        "data" => [
            "csrf_token" => $token
        ]
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "トークンの発行に失敗しました。",
        "data" => []
    ]);
}
//!SECTION