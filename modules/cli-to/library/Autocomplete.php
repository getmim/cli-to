<?php
/**
 * Autocomplete
 * @package cli-to
 * @version 0.0.1
 */

namespace CliTo\Library;


class Autocomplete extends \Cli\Autocomplete
{
    protected static function getNames()
    {
        $accounts = Storage::get();
        return array_keys($accounts);
    }

    static function account(array $args)
    {
        $groups = self::getNames();
        return implode(' ', $groups);
    }

    static function command(array $args)
    {
        $groups = self::getNames();
        $result = array_merge($groups, ['add', 'remove']);

        return implode(' ', $result);
    }
}
