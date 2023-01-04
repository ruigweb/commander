<?php

declare(strict_types=1);

namespace Ruigweb\Commander\Command;

use InvalidArgumentException;
use Ruigweb\Commander\Command\Option\Type;

class Option extends Argument
{
    /**
     * (new Option('force')->as(Option::BOOLEAN, false))
     */
    public function __construct(string $name, Type $type = Type::STRING, $default = null)
    {
        parent::__construct($name);
    }

    public function __get(string $name)
    {
        if ($name === 'abbr' || $name === 'abbreviation') {
            return mb_substr($this->name, 0, 1);
        }

        throw new InvalidArgumentException;
    }

    public function as(Type $type, $default = null)
    {
    }

    public function matches(string $argv)
    {
        if (mb_substr($argv, 0, 2) === '--') {
            return mb_substr($argv, 2, (($pos = mb_strpos($argv, '=')) !== false) ? $pos : null) === $this->name;
        }

        return mb_substr($argv, 1, 1) === $this->abbreviation;
    }

    public function parse(string $argv)
    {

    }

    public function default()
    {

    }

    public function value()
    {

    }

    public function type()
    {

    }
}
