<?php

declare(strict_types=1);

namespace Ruigweb\Commander\Commands;

use Ruigweb\Commander\Argv;
use Ruigweb\Commander\Command;
use Ruigweb\Commander\Command\Argument;
use Ruigweb\Commander\Command\Option;

class Printer extends Command
{
    protected $sheets = [];

    public const ERROR   = 'error';
    public const WARNING = 'warn';
    public const INFO    = 'info';
    public const DEBUG   = 'debug';
    public const DONE    = 'done';

    public const ERROR_COLOR = '31';
    public const WARNING_COLOR = '33';
    public const INFO_COLOR = '37';
    public const DEBUG_COLOR = '90';
    public const DONE_COLOR = '32';

    public function __construct()
    {
        parent::__construct('printer', new Argv(
            new Argument('output', 'string'),
            new Option('stream', 'string', 'output'),
            new Option('escape', 'boolean', true)
        ));
    }

    public function __invoke(Argv $arguments = null)
    {
        return $this->run($arguments);
    }

    public function debug(string $message, bool $escape = true) : Printer
    {
        if ($escape) {
            $message = escapeshellcmd($message);
        }

        $this->sheets[] = '\033[1;'.Printer::DEBUG_COLOR.'m ❯ ['.Printer::DEBUG.']\033[0m \033[0;'.Printer::DEBUG_COLOR.'m'.$message.'\033[0m';

        return $this;
    }

    public function info(string $message, bool $escape = true) : Printer
    {
        if ($escape) {
            $message = escapeshellcmd($message);
        }

        $this->sheets[] = '\033[1;'.Printer::INFO_COLOR.'m ℹ ['.Printer::INFO.']\033[0m  \033[0;'.Printer::INFO_COLOR.'m'.$message.'\033[0m';

        return $this;
    }

    public function done(string $message, bool $escape = true) : Printer
    {
        if ($escape) {
            $message = escapeshellcmd($message);
        }

        $this->sheets[] = '\033[1;'.Printer::DONE_COLOR.'m ✓ ['.Printer::DONE.']\033[0m  \033[0;'.Printer::DONE_COLOR.'m'.$message.'\033[0m';

        return $this;
    }

    public function warning(string $message, bool $escape = true) : Printer
    {
        return $this->warn($message, $escape);
    }

    public function warn(string $message, bool $escape = true) : Printer
    {
        if ($escape) {
            $message = escapeshellcmd($message);
        }

        $this->sheets[] = '\033[1;'.Printer::WARNING_COLOR.'m ✖ ['.Printer::WARNING.']\033[0m  \033[0;'.Printer::WARNING_COLOR.'m'.$message.'\033[0m';

        return $this;
    }

    public function error(string $message, bool $escape = true) : Printer
    {
        if ($escape) {
            $message = escapeshellcmd($message);
        }

        $this->sheets[] = '\033[1;'.Printer::ERROR_COLOR.'m ☢ ['.Printer::ERROR.']\033[0m \033[0;'.Printer::ERROR_COLOR.'m'.$message.'\033[0m';

        return $this;
    }

    public function print(string $message = null, string $level = null, bool $escape = true)
    {
        if ($level && method_exists($this, $level)) {
            call_user_func_array([$this, $level], [$message, $escape]);
            return $this->print();
        }

        $argv = clone $this->arguments;
        $output = '';
        if ($message) {
            if ($escape) {
                $message = escapeshellcmd($message);
            }
            $output = $message.PHP_EOL;
        } else {
            foreach ($this->sheets as $sheet) {
                // void
                $output .= $sheet.PHP_EOL;
            }
        }
        
        $argv['escape']->set(false);
        $argv['output']->set(rtrim($output));
        
        return $this->run($argv);
    }

    public function run(Argv $argv = null) : ?string
    {
        $argv = $argv ?: $this->arguments;
        
        $output = $argv->get('output', $this->arguments['output'])->value() ?? null;
        
        $result = '';
        if ($output) {
            if ($argv->get('escape', $this->arguments['escape'])->value() === true) {
                $output = escapeshellcmd($output);
            }
            
            $output = "/bin/echo -e '".$output."'";
            $result .= $output;
            
            $fp = fopen('php://'.$argv->get('stream', $this->arguments['stream'])->value(), 'w');
            fwrite($fp, shell_exec($output));
            fclose($fp);
        }

        return $result;
    }
}
