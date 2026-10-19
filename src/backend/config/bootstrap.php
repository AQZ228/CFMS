<?php
require_once __DIR__ . '/../vendor/autoload.php';
$_DB = new Lib\DB(getenv('DB_HOST'), getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASSWORD'));
