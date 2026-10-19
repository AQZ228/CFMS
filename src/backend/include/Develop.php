<?php

namespace Lib;

class Develop
{
    /**
     * 開発環境かどうかを判定
     * @return bool
     */
    public static function isDevelopment(): bool
    {
        return getenv('APP_ENV') === 'development';
    }
    /**
     * プロトコルを取得（HTTPまたはHTTPS）
     * @return string
     */
    public static function getProtocol(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    }
}
