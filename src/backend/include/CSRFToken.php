<?php

namespace Lib;

class CSRFToken
{
    private Session $_session;

    /**
     * コンストラクタ
     * セッションからトークンを取得（存在しない場合はnull）
     */
    public function __construct()
    {
        $this->_session = new Session();
    }

    /**
     * トークンを取得（存在しない場合は新規生成）
     */
    public function generateToken(): string
    {
        if (empty($this->_session->get('csrf_token'))) {
            $this->_session->set('csrf_token', bin2hex(random_bytes(32)));
        }

        return $this->_session->get('csrf_token');
    }

    /**
     * トークンを検証（タイミング攻撃対策済み）
     * 
     * @param string|null $token POST等で送信されてきたトークン
     * @param bool $consume 検証後にトークンを消費（破棄）するかどうか
     * @return bool
     */
    public function validateToken(?string $token, bool $consume = false): bool
    {
        if (empty($this->_session->get('csrf_token')) || empty($token)) {
            return false;
        }

        $isValid = hash_equals($this->_session->get('csrf_token'), $token);
        if ($isValid && $consume) {
            $this->clearToken();
        }

        return $isValid;
    }

    /**
     * セッション内のトークンを破棄
     */
    public function clearToken(): void
    {
        $this->_session->remove('csrf_token');
    }
}
