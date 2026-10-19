<?php

namespace Lib;

class Session
{
    /**
     * コンストラクタ
     * セッションが開始されていない場合は開始する
     * @throws \RuntimeException セッションが無効化されている場合や、ヘッダーが既に送信されている場合にスローされる
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new \RuntimeException('Sessions are disabled.');
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (headers_sent($file, $line)) {
                throw new \RuntimeException("Cannot start session. Headers already sent in {$file} on line {$line}.");
            }

            // セッションクッキーのセキュリティ設定を適用
            $success = session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => Develop::isDevelopment() ? false : true, // 開発環境ではfalse、本番環境ではtrue
                'httponly' => true, // JavaScriptからのアクセスを禁止
                'samesite' => 'Lax' // CSRF対策
            ]);

            if (!session_start() || !$success) {
                throw new \RuntimeException('Failed to start the session.');
            }
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
