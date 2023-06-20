<?php

declare(strict_types=1);

namespace Ruigweb\Commander\Command;

use InvalidArgumentException;

class Argument
{
    protected string $name;
    protected ?string $help = null;
    protected $value = null;
    protected Type $type;
    protected $default = null;

    public function __construct(string $name, Type | string $type = Type::STRING, $default = null)
    {
        $this->setName($name);
        if (is_string($type)) {
            $type = Type::from($type);
        }

        $this->as($type, $default);
    }

    protected function setName(string $name) : Argument
    {
        if (preg_match('/^[a-zA-Z0-9]+[a-zA-Z\-_0-9]+[a-zA-Z0-9]+$/', $name)) {
            $this->name = $name;
            return $this;
        }

        throw new InvalidArgumentException;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function as(Type $type, $default = null)
    {
        $this->type = $type;
        $this->default = $this->type->default($default);

        return $this;
    }

    public function type() : Type
    {
        return $this->type;
    }

    public function help(string $help) : Argument
    {
        $this->help = $help;
        return $this;
    }

    public function usage() : string
    {
        $default = $this->type->toString($this->default);
        $usage = $this->name().' ('.$this->type->value.(($default) ? ':'.$default : '').')';
        if ($help = $this->help) {
            $usage .= '   '.$help;
        }

        return $usage;
    }

    public function parse(string $arg) : Argument
    {
        $this->value = $this->type->format($arg, $this->default);
        return $this;
    }

    /**
     * Use with caution, will just set value regardless the value
     * Should not be used in combination with user input,
     * always use parse() with user input
     */
    public function set(mixed $value) : Argument
    {
        $this->value = $value;
        return $this;
    }

    public function matches(string $arg) : bool
    {
        return (mb_substr($arg, 0, 1) === '-') === false;
    }

    public function value()
    {
        return !is_null($this->value) ? $this->value : $this->default;
    }

    public function __toString() : string
    {
        return $this->type->toString($this->value());
    }
}
