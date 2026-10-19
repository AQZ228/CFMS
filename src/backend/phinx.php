<?php
require_once __DIR__ . '/vendor/autoload.php';

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASS');
$port = getenv('DB_PORT') ?: '3306';

if (!$host || !$dbname || !$user || !$password) {
    fwrite(STDERR, "\n=== [ERROR] 必要な環境変数が不足しています ===\n");
    exit(1);
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
                'charset' => 'utf8mb4',
            ],
            'development' => [
                'adapter' => 'mysql',
                'host' => $host,
                'name' => $dbname,
                'user' => $user,
                'pass' => $password,
                'port' => $port,
                'charset' => 'utf8mb4',
            ],
            'testing' => [
                'adapter' => 'mysql',
                'host' => $host,
                'name' => $dbname,
                'user' => $user,
                'pass' => $password,
                'port' => $port,
                'charset' => 'utf8mb4',
            ]
        ],
        'version_order' => 'creation'
    ];
