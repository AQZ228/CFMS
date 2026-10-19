<?php

namespace Lib;

use PDOStatement;

class DB
{
    private \PDO $pdo;
    private ?PDOStatement $stmt;

    /**
     * DBクラスのインスタンスを環境変数から生成する
     * DB_HOST, DB_NAME, DB_USER, DB_PASSの環境変数が必要です。
     * @return self
     */
    public static function connect(): self
    {
        return new self(getenv('DB_HOST'), getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASS'));
    }

    /**
     * DBクラスのコンストラクタ
     * @param string $host
     * @param string $dbname
     * @param string $user
     * @param string $password
     * @throws \RuntimeException 接続に失敗した場合にスローされる
     */
    public function __construct(string $host, string $dbname, string $user, string $password)
    {
        try {
            $this->pdo = new \PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }
    /**
     * トランザクションスタート
     * @return bool
     */
    public function begin(): bool
    {
        return $this->pdo->beginTransaction();
    }
    /**
     * コミット
     * @return bool 成功したかどうか
     * トランザクションが開始されていない場合はfalseを返す
     */
    public function commit(): bool
    {
        if ($this->pdo->inTransaction()) {
            return $this->pdo->commit();
        }
        return false;
    }
    /**
     * ロールバック
     * @return bool 成功したかどうか
     * トランザクションが開始されていない場合はfalseを返す
     */
    public function rollback(): bool
    {
        if ($this->pdo->inTransaction()) {
            return $this->pdo->rollBack();
        }
        return false;
    }
    /**
     * SQLを実行し、影響を受けた行数を返す
     * @param string $sql 実行するSQL文
     * @param array $params バインドするパラメータ
     * @return \PDOStatement 実行されたSQLのステートメント
     */
    public function exec(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
