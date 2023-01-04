<?php

declare(strict_types=1);

namespace Ruigweb\Commander\Command;

class Argument
{
    protected ?string $help = null;
    
    public function __construct(protected $name)
    {

    }

    public function help(string $help) : Argument
    {
        $this->help = $help;
        return $this;
    }

    public function __invoke(...$args)
    {

    }

    public function parse(string $argv)
    {

    }

    public function matches(string $argv)
    {

    }
}
