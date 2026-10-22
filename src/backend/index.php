<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if($_SERVER['REQUEST_METHOD']==='OPTIONS'){
    http_response_code(200);
    exit;
}

$rawInput =file_get_contents('php://input');
$data = json_decode($rawInput);

if(json_last_error() !==JSON_ERROR_NONE){
    http_response_code(400);
    echo json_encode(['success'=>false, 'message' =>'Invalid JSON input']);
    exit;
}
$message = $data->message ?? null;

echo json_encode(['success' =>true, 'message' => $message]);
