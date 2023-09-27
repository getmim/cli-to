<?php

namespace CliTo\Library;

use Cli\Library\Bash;

class Storage
{
    protected static $file = 'to.ser';
    protected static $sep = '.';

    protected static function getFilePath()
    {
        return BASEPATH . '/' . self::$file;
    }

    protected static function load()
    {
        $file = self::getFilePath();

        if (!is_file($file)) {
            return [];
        }

        $encoded = file_get_contents($file);
        $decoded = json_decode($encoded, true);

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

        $encoded = json_encode($content);
        file_put_contents($file, $encoded);
    }

    static function add($name, $content)
    {
        $accounts = self::get();
        $accounts[$name] = $content;
        self::save($accounts);
    }

    static function get()
    {
        return self::load() ?? [];
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
