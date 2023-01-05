<?php

declare(strict_types=1);

namespace Ruigweb\Commander;

use Closure;
use ArrayObject;
use Exception;
use InvalidArgumentException;
use Ruigweb\Commander\Command\Argument;
use Ruigweb\Commander\Command\Option;

class Argv extends ArrayObject
{
    public function __construct(...$argv)
    {
        foreach ($argv as $var) {
            $this->validate($var);
        }

        parent::__construct($argv);
    }

    public function each(Closure $callback) 
    {
        foreach ($this as $argument) {
            $callback($argument);
        }

        return $this;
    }

    public function append($value): void
    {
        throw new Exception('Argv is immutable');
    }

    public function offsetSet($key, $value): void
    {
        throw new Exception('Argv is immutable');
    }

    protected function validate($value): void
    {
        if (!$value instanceof Option && !$value instanceof Argument) {
            var_dump(get_class($value));
            throw new InvalidArgumentException;
        }
    }
}
