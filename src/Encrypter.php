<?php
/**
 * src/Encryption.php.
 *
 */
namespace ESolution\DBEncryption;

class Encrypter
{
    private static $method = 'aes-128-ecb';

    /**
     * @param string $value
     * 
     * @return string
     */
    public static function encrypt($value)
    {
        return openssl_encrypt($value, self::$method, self::getKey(), 0, $iv = '');
    }

    /**
     * @param string $value
     * 
     * @return string
     */
    public static function decrypt($value)
    {
        return openssl_decrypt($value, self::$method, self::getKey(), 0, $iv = '');
    }

    /**
     * Get app key for encryption key
     *
     * @return string
     */
    protected static function getKey()
    {
        $salt = substr(hash('sha256', env('APP_KEY')), 0, 16);
        return $salt;
    }
}