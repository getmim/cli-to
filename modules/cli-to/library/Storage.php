<?php

namespace CliTo\Library;

use Cli\Library\Bash;

class Storage
{
    protected static $file = 'to.ser';
    protected static $sep = '.';

    protected static function checkCipherSupport()
    {
        $cipher = self::getCipher();
        if (!in_array($cipher, openssl_get_cipher_methods())) {
            Bash::error('Cipher ' . $cipher . ' is not supported of your php installation');
        }
    }

    protected static function getCipher()
    {
        return \Mim::$app->config->cliTo->cipher;
    }

    protected static function getFilePath()
    {
        return BASEPATH . '/' . self::$file;
    }

    protected static function getSecret()
    {
        return \Mim::$app->config->cliTo->secret;
    }

    protected static function decrypt(string $encrypted)
    {
        self::checkCipherSupport();
        $cipher = self::getCipher();
        $key = self::getSecret();
        $encrypted = explode(self::$sep, $encrypted);
        $iv = array_shift($encrypted);
        $tag = array_shift($encrypted);
        $ciphertext = implode(self::$sep, $encrypted);
        return openssl_decrypt($ciphertext, $cipher, $key, 0, $iv, $tag);
    }

    protected static function encrypt(string $decrypted)
    {
        self::checkCipherSupport();
        $cipher = self::getCipher();
        $key = self::getSecret();

        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $tag = null;

        $encrypted = openssl_encrypt($decrypted, $cipher, $key, 0, $iv, $tag);
        return implode(self::$sep, [$iv, $tag, $encrypted]);
    }

    protected static function load()
    {
        $file = self::getFilePath();

        if (!is_file($file)) {
            return [];
        }

        $encrypted = file_get_contents($file);
        $decrypted = self::decrypt($encrypted);
        $decoded = unserialize($decrypted);

        return $decoded;
    }

    protected static function save($content)
    {
        $file = self::getFilePath();

        if (!is_file($file)) {
            $dirname = dirname($file);
            if (!is_writable($dirname)) {
                Bash::error('Target storage `' . $dirname . '` is not writable');
            }
        }

        $encoded = serialize($content);
        $encrypted = self::encrypt($encoded);
        file_put_contents($file, $encrypted);
    }

    static function add($name, $content)
    {
        $accounts = self::get();
        $accounts[$name] = $content;
        self::save($accounts);
    }

    static function get()
    {
        return self::load();
    }

    static function getOne($name)
    {
        $accounts = self::get();
        return $accounts[$name] ?? null;
    }

    static function remove($name)
    {
        $accounts = self::get();
        if (!isset($accounts[$name])) {
            return true;
        }

        unset($accounts[$name]);
        self::save($accounts);
    }
}
