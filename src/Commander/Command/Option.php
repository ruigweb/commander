<?php

declare(strict_types=1);

namespace Ruigweb\Commander\Command;

use InvalidArgumentException;
use Ruigweb\Commander\Command\Type;

class Option extends Argument
{
    protected bool $allow_abbr = true;

    /**
     * (new Option('force')->as(Option::BOOLEAN, false))
     * keeper build --force
     */

    public function __get(string $name)
    {
        if ($name === 'abbr' || $name === 'abbreviation') {
            return mb_substr($this->name, 0, 1);
        }

        throw new InvalidArgumentException;
    }

    public function as(Type $type, $default = null)
    {
        parent::as($type, $default);
        if (Type::from($this->type->value) !== Type::BOOLEAN) {
            $this->short(false);
        }

        return $this;
    }

    public function usage() : string
    {
        $default = $this->type->toString($this->default);
        $usage = '--'.$this->name().($this->allow_abbr ? ' -'.$this->abbreviation : '').' ('.$this->type->value.(($default) ? ':'.$default : '').')';
        if ($help = $this->help) {
            $usage .= '   '.$help;
        }

        return $usage;
    }

    public function short($allow = true)
    {
        if ($allow === true && Type::from($this->type->value) === Type::BOOLEAN) {
            throw new InvalidArgumentException;
        }

        $this->allow_abbr = $allow;
        return $this;
    }

    public function matches(string $arg) : bool
    {
        if (mb_substr($arg, 0, 1) === '-') {
            if (mb_substr($arg, 0, 2) === '--') {
                return mb_substr($arg, 2, (($pos = (mb_strpos($arg, '=') - 2)) !== false) ? $pos : null) === $this->name;
            }

            return $this->allow_abbr && mb_substr($arg, 1, 1) === $this->abbreviation;
        }

        return false;
    }

    public function parse(string $arg) : Option
    {
        if (mb_substr($arg, 0, 2) === '--') {
            if (preg_match('/^--[a-zA-Z]+=/', $arg)) {
                $arg = preg_replace('/^--[a-zA-Z]+=/', '', $arg);
            }
        } elseif (preg_match('/^-[a-zA-Z]{1}$/', $arg)) {
            $arg = 'true';
        } else {
            throw new InvalidArgumentException;
        }

        $this->value = $this->type->format($arg, $this->default);
        return $this;
    }
}
