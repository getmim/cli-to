<?php
/**
 * ToController
 * @package cli-to
 * @version 0.0.1
 */

namespace CliTo\Controller;

use Cli\Library\Bash;
use CliTo\Library\Storage;

class ToController extends \Cli\Controller
{
    protected function escapeSend(string $text)
    {
        $specials = ['{', '}', '[', ']'];
        foreach ($specials as $find) {
            $text = str_replace($find, '\\' . $find, $text);
        }

        return $text;
    }

    function addAction()
    {
        $name = Bash::ask([
            'text' => 'Connection name',
            'required' => true
        ]);
        $command = Bash::ask([
            'text' => 'Command text',
            'required' => true
        ]);

        $lines = [];
        while (true) {
            $expect = Bash::ask([
                'text' => 'Expected text',
                'space' => 2
            ]);
            if (!$expect) {
                break;
            }
            $send = Bash::ask([
                'text' => 'Text to send',
                'space' => 4
            ]);

            $lines[] = [
                'expect' => $expect,
                'send' => $send
            ];
        }

        Storage::add($name, [
            'command' => $command,
            'expects' => $lines
        ]);

        Bash::echo('New account created');
    }

    function connectAction()
    {
        $name = $this->req->param->name;
        $account = Storage::getOne($name);
        if (!$account) {
            Bash::error('Account with that name not found');
        }

        $bin = exec('which expect');
        if (!$bin) {
            Bash::error('Command `expect` is not installed on your engine');
        }

        $rows = [
            '#!' . $bin,
            '',
            'spawn ' . $account['command']
        ];

        $space = 0;
        $expects = [];
        foreach ($account['expects'] as $info) {
            $spc = str_repeat(' ', $space);
            $exp = addslashes($info['expect']);
            $snd = $this->escapeSend($info['send']);
            $rows[] = $spc . 'expect "' . $exp . '" {';
            $rows[] = $spc . '    send "' . $snd . '\\n"';
            $space+= 4;
        }

        $interacted = false;
        foreach ($account['expects'] as $info) {
            $space-= 4;
            $spc = str_repeat(' ', $space);
            if (!$interacted) {
                $rows[] = $spc . '    interact';
                $interacted = true;
            }
            $rows[] = $spc . '}';
        }

        $tx = implode(PHP_EOL, $rows);

        $file = tempnam(sys_get_temp_dir(), 'mim-to-');
        file_put_contents($file, $tx);
        chmod($file, 0500);

        passthru($file);
        unlink($file);
    }

    function removeAction()
    {
        $name = $this->req->param->name;
        Storage::remove($name);

        Bash::echo('Successfully removed from storage');
    }
}
