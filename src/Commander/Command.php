<?php

declare(strict_types=1);

namespace Ruigweb\Commander;

use InvalidArgumentException;
use Ruigweb\Commander\Argv;

class Command {

    protected string $name;
    protected string $description;
    protected Argv $arguments;
    protected $handler = null;

    public function __construct(string $name, Argv $arguments, string $description = null, callable $handler = null) {
        $this->name = $name;
        $this->description = $description;
        $this->arguments = $arguments;
        $this->handler = $handler;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function description() : string
    {
        return $this->description;
    }

    public function usage(bool $short = true)
    {
        // usage: {command} [-h] [{--option}] {arguments}
        // positional arguments:
        // ....
        // options:
        // -h --help    show this help message and exit

    }

    public function __invoke(...$args)
    {
        return $this->run(...$args);
    }

    public function run(...$args) 
    {
        if (empty($this->handler)) {
            throw new InvalidArgumentException;
        }
    }
}
