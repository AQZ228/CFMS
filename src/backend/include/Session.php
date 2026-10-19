<?php

namespace Lib;

class Session
{
    /**
     * コンストラクタ
     * セッションが存在しない場合、session_startします。
     */
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
    /**
     * セッションに値を設定
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
    /**
     * セッションの値を取得
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }
    /**
     * セッションの値を削除
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    /**
     * セッションの値を全て削除
     * @return void
     */
    public function clear(): void
    {
        session_unset();
    }
    /**
     * セッションを破棄
     * @return void
     */
    public function destroy(): void
    {
        session_destroy();
    }
    /**
     * セッションIDを再生成
     * @param bool $deleteOldSession 古いセッションを削除するかどうか
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
    }
}
