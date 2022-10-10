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

            // TODO
            // make it possible to execute more than one command
            break;
        }

        Storage::add($name, [
            'command' => $command,
            'expects' => $lines
        ]);

        Bash::echo('Successfully added new content');
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
            'spawn ' . $account['command'],
            ''
        ];

        $expects = [];
        foreach ($account['expects'] as $info) {
            $expects[] = [
                'expect "' . hs($info['expect']) . '" {',
                '    send "' . $info['send'] . '\\n";'
            ];
        }

        $last_index = count($expects) - 1;

        foreach ($expects as $index => $lines) {
            if ($index == $last_index) {
                $lines[] = '    interact';
            }
            $lines[] = '}';
            $rows[] = implode(PHP_EOL, $lines);
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
