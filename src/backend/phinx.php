<?php
require_once __DIR__ . '/vendor/autoload.php';

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASS');
$port = getenv('DB_PORT') ?: '3306';

if (!$host || !$dbname || !$user || !$password) {
    // echo と die() を使って、無理やり画面に出力して終了させる
    echo "\n=== [DEBUG] 環境変数が不足しています ===\n";
    echo "DB_HOST: " . var_export($host, true) . "\n";
    echo "DB_NAME: " . var_export($dbname, true) . "\n";
    echo "DB_USER: " . var_export($user, true) . "\n";
    echo "DB_PASS: " . var_export($password, true) . "\n";
    echo "========================================\n\n";
    die();
}

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
        ],
        'environments' => [
            'default_migration_table' => 'phinxlog',
            'default_environment' => 'development',
            'production' => [
                'adapter' => 'mysql',
                'host' => $host,
                'name' => $dbname,
                'user' => $user,
                'pass' => $password,
                'port' => $port,
                'charset' => 'utf8',
            ],
            'development' => [
                'adapter' => 'mysql',
                'host' => $host,
                'name' => $dbname,
                'user' => $user,
                'pass' => $password,
                'port' => $port,
                'charset' => 'utf8',
            ],
            'testing' => [
                'adapter' => 'mysql',
                'host' => $host,
                'name' => $dbname,
                'user' => $user,
                'pass' => $password,
                'port' => $port,
                'charset' => 'utf8',
            ]
        ],
        'version_order' => 'creation'
    ];
