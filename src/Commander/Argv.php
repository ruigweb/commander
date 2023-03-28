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
        foreach ($this as $key => $argument) {
            if ($callback($argument, $key) === false) {
                break;
            }
        }

        return $this;
    }

    public function filter(Closure $callback)
    {
        $arguments = [];
        foreach ($this as $key => $argument) {
            if ($callback($argument, $key) === true) {
                $arguments[] = $argument;
            }
        }

        return new Argv(...$arguments);
    }

    public function get(string $name) : Argument
    {
        foreach ($this as $argument) {
            if ($argument->name() === $name) {
                return $argument;
            }
        }

        throw new InvalidArgumentException;
    }

    public function arguments() : array
    {
        $arguments = [];
        $this->each(function($argument) use(&$arguments) {
            if (get_class($argument) === Argument::class) {
                $arguments[] = $argument;
            }
        });

        return $arguments;
    }

    public function options() : array
    {
        $options = [];
        $this->each(function($argument) use(&$options) {
            if (get_class($argument) === Option::class) {
                $options[] = $argument;
            }
        });

        return $options;
    }

    public function append($value): void
    {
        throw new Exception('Argv is immutable');
    }

    public function offsetSet($key, $value): void
    {
        throw new Exception('Argv is immutable');
    }

    public function offsetUnset(mixed $key): void
    {
        throw new Exception('Argv is immutable');
    }

    public function offsetGet(mixed $key): mixed
    {
        return $this->get($key);
    }

    protected function validate($value): void
    {
        if (!$value instanceof Option && !$value instanceof Argument) {
            throw new InvalidArgumentException;
        }
    }
}
