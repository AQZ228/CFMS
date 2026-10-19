<?php

namespace lib;

class CSRFToken
{
    private ?string $_token;
    private Session $_session;

    /**
     * コンストラクタ
     * セッションからトークンを取得（存在しない場合はnull）
     */
    public function __construct()
    {
        $this->_session = new Session();
        $this->_token = $this->_session->csrf_token ?? null;
    }

    /**
     * トークンを取得（存在しない場合は新規生成）
     */
    public function generateToken(): string
    {
        if (empty($this->_token)) {
            $this->_token = bin2hex(random_bytes(32));
            $this->_session->csrf_token = $this->_token;
        }

        return $this->_token;
    }

    /**
     * トークンを検証（タイミング攻撃対策済み）
     * 
     * @param string|null $token POST等で送信されてきたトークン
     * @param bool $consume 検証後にトークンを消費（破棄）するかどうか
     * @return bool
     */
    public function validateToken(?string $token, bool $consume = true): bool
    {
        if (empty($this->_token) || empty($token)) {
            return false;
        }

        $isValid = hash_equals($this->_token, $token);
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
        $this->_token = null;
    }
}
