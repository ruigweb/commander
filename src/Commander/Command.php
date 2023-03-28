<?php

declare(strict_types=1);

namespace Ruigweb\Commander;

use Closure;
use InvalidArgumentException;
use Ruigweb\Commander\Argv;

class Command {

    protected string $name;
    protected ?string $description;
    protected Argv $arguments;
    protected $handler = null;
    protected $parsed = [];

    public function __construct(string $name, Argv $arguments, string $description = null, callable $handler = null) {
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

        return 'usage of '.$this->name;
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
            $this->arguments->filter(function($argument) {
                return !in_array($argument->name(), $this->parsed);
            })->each(function($argument) use($arg) {
                if ($argument->matches($arg) && !in_array($argument->name(), $this->parsed())) {
                    $argument->parse($arg);
                    $this->parsed[] = $argument->name();
                    
                    return false;
                }
            });
        }

        return $this;
    }

    public function parsed() : array
    {
        return $this->parsed;
    }

    public function run(Argv $arguments = null) : ?string
    {
        if (empty($this->handler)) {
            throw new InvalidArgumentException;
        }

        $handler = Closure::fromCallable($this->handler);
        $output  = $handler($arguments ?: $this->arguments);

        return $output;
    }
}
