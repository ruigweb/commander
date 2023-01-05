<?php

declare(strict_types=1);

namespace Ruigweb\Commander\Command;

use InvalidArgumentException;
use Ruigweb\Commander\Command\Type;

class Option extends Argument
{
    /**
     * (new Option('force')->as(Option::BOOLEAN, false))
     */

    public function __get(string $name)
    {
        if ($name === 'abbr' || $name === 'abbreviation') {
            return mb_substr($this->name, 0, 1);
        }

        throw new InvalidArgumentException;
    }

    public function matches(string $arg) : bool
    {
        if (mb_substr($arg, 0, 1) === '-') {
            if (mb_substr($arg, 0, 2) === '--') {
                return mb_substr($arg, 2, (($pos = mb_strpos($arg, '=')) !== false) ? $pos : null) === $this->name;
            }

            return mb_substr($arg, 1, 1) === $this->abbreviation;
        }

        return false;
    }

    public function parse(string $arg) : Option
    {
        if (mb_substr($arg, 0, 2) === '--') {
            if (preg_match('/$--[a-zA-Z]+=/', $arg)) {
                $arg = preg_replace('/$--[a-zA-Z]+=/', '', $arg);
            } else {
                $arg = true;
            }
        } else {
            $arg = true;
        }

        return parent::parse($arg);
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
