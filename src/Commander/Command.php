<?php

declare(strict_types=1);

namespace Ruigweb\Commander;

use Closure;
use InvalidArgumentException;
use Ruigweb\Commander\Argv;
use Ruigweb\Commander\Command\Argument;

class Command
{
    protected string $name;
    protected ?string $description;
    protected Argv $arguments;
    protected $handler = null;
    protected $parsed = [];

    public function __construct(string $name, Argv $arguments = new Argv, callable $handler = null, string $description = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->arguments = $arguments;
        $this->handler = $handler;
    }

    /**
     * Temporary until command are searchable ArrayObject in coordinator
     */
    public function __get($property)
    {
        if ($property === 'name') {
            return $this->name;
        }
    }

    /**
     * Temporary until command are searchable ArrayObject in coordinator
     */
    public function __isset($property)
    {
        if ($property === 'name') {
            return true;
        }
    }

    public function name() : string
    {
        return $this->name;
    }

    public function description() : ?string
    {
        return $this->description;
    }

    public function argv() : Argv
    {
        return $this->arguments;
    }

    public function usage(bool $short = true) : string
    {
        // usage: {command} [-h] [{--option}] {arguments}
        // positional arguments:
        // ....
        // options:
        // -h --help    show this help message and exit

        $usage = PHP_EOL.
        'Description:'.PHP_EOL.
        '   '.($this->description() ?? '-').PHP_EOL.
        PHP_EOL.
        'Usage:'.PHP_EOL.
        '   '.$this->name().PHP_EOL.
        PHP_EOL.
        'Positional arguments:'.PHP_EOL.
        '   '.$this->getArgumentsUsage().
        PHP_EOL.
        'Options:'.PHP_EOL.
        '   '.$this->getOptionsUsage();

        $columnLength = 0;
        preg_match_all('/((([\-a-zA-Z0-9])+\s)+\({1}[a-zA-Z:]+\){1})\s{3}/', $usage, $matches);
        if (count($matches) > 0) {
            foreach ($matches[1] as $match) {
                $match = trim($match);
                if (mb_strlen($match) > $columnLength) {
                    $columnLength = mb_strlen($match);
                }
            }
        }

        if ($columnLength > 0) {
            $usage = preg_replace_callback('/((([\-a-zA-Z0-9])+\s)+\({1}[a-zA-Z:]+\){1})\s{3}/', function ($matches) use ($columnLength) {
                $currentLength = mb_strlen(trim($matches[1])) - 3;
                $column = $matches[1];
                if (($columnLength - $currentLength) > 0) {
                    for ($i = 0; $i < ($columnLength - $currentLength); $i++) {
                        $column .= " ";
                    }
                }

                return $column;
            }, $usage);
        }

        return $usage;
    }

    protected function getArgumentsUsage()
    {
        $usage = '';
        foreach ($this->arguments->arguments() as $argument) {
            $usage .= $argument->usage().PHP_EOL;
        }

        return $usage;
    }

    protected function getOptionsusage()
    {
        $usage = '';
        foreach ($this->arguments->options() as $option) {
            $usage .= $option->usage().PHP_EOL;
        }

        return $usage;
    }

    public function __invoke(Argv $arguments = null)
    {
        return $this->run($arguments);
    }

    /**
     * Take list of provided command line arguments,
     * and parse to values for Argv of command
     */
    public function take(...$args) : Command
    {
        foreach ($args as $arg) {
            $this->arguments->filter(function ($argument) {
                return !in_array($argument->name(), $this->parsed);
            })->each(function ($argument) use ($arg) {
                if ($argument->matches($arg) && !in_array($argument->name(), $this->parsed())) {
                    if ($argument instanceof Argument) {
                        $argument->parse($arg);
                    }
                    $this->parsed[] = $argument->name();

                    return false;
                }
            });
        }

        return $this;
    }

    public function matches(string $arg) : bool
    {
        return $this->name() === $arg;
    }

    public function parsed() : array
    {
        return $this->parsed;
    }

    public function run(Argv $argv = null) : ?string
    {
        if (empty($this->handler)) {
            throw new InvalidArgumentException;
        }

        $handler = Closure::fromCallable($this->handler);
        $output  = $handler($argv ?: $this->arguments);

        return $output;
    }
}
