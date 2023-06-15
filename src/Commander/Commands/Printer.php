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

        $this->sheets[] = '\033[1;90m ❯ ['.Printer::DEBUG.']\033[0m \033[0;90m'.$message.'\033[0m';

        return $this;
    }

    public function info(string $message, bool $escape = true) : Printer
    {
        if ($escape) {
            $message = escapeshellcmd($message);
        }

        $this->sheets[] = '\033[1;37m ℹ ['.Printer::INFO.']\033[0m  \033[0;37m'.$message.'\033[0m';

        return $this;
    }

    public function done(string $message, bool $escape = true) : Printer
    {
        if ($escape) {
            $message = escapeshellcmd($message);
        }

        $this->sheets[] = '\033[1;32m ✔️ ['.Printer::DONE.']\033[0m  \033[0;32m'.$message.'\033[0m';

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

        $this->sheets[] = '\033[1;33m ✖ ['.Printer::WARNING.']\033[0m  \033[0;33m'.$message.'\033[0m';

        return $this;
    }

    public function error(string $message, bool $escape = true) : Printer
    {
        if ($escape) {
            $message = escapeshellcmd($message);
        }

        $this->sheets[] = '\033[1;31m ☢ ['.Printer::ERROR.']\033[0m \033[0;31m'.$message.'\033[0m';

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
            var_dump(shell_exec($output));
            fwrite($fp, shell_exec($output));
            fclose($fp);
        }

        return $result;
    }
}
