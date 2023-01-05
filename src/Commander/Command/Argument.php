<?php

declare(strict_types=1);

namespace Ruigweb\Commander\Command;

class Argument
{
    protected string $name;
    protected ?string $help = null;
    protected $value = null;
    protected Type $type;
    protected $default = null;
    
    public function __construct(string $name, Type $type = Type::STRING, $default = null)
    {
        $this->name = $name;
        $this->as($type, $default);
    }

    public function as(Type $type, $default = null)
    {
        $this->type = $type;
        $this->default = $default;
    }

    public function help(string $help) : Argument
    {
        $this->help = $help;
        return $this;
    }

    public function __invoke(string $arg)
    {

    }

    public function parse(string $arg) : Argument
    {
        $this->value = $this->type->format($arg, $this->default);
        return $this;
    }

    public function matches(string $arg) : bool
    {
        return (mb_substr($arg, 0, 1) === '-') === false;
    }
}
