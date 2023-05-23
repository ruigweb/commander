<?php

declare(strict_types=1);

namespace Ruigweb\Commander;

use ArrayObject;
use Exception;
use InvalidArgumentException;
use Ruigweb\Commander\Command\Argument;
use Ruigweb\Commander\Command\Option;
use Ruigweb\Commander\Command;

class Argv extends ArrayObject
{
    public function __construct(...$argv)
    {
        if (is_array($argv[0] ?? null)) {
            $argv = $argv[0];
        }

        foreach ($argv as $key => $var) {
            $this->validate($var, $argv, $key);
        }

        parent::__construct($argv);
    }

    public function first(callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            foreach ($this as $argument) {
                return $argument;
            }
        }
    }

    public function each(callable $callback)
    {
        foreach ($this as $key => $argument) {
            if ($callback($argument, $key) === false) {
                break;
            }
        }

        return $this;
    }

    public function filter(callable $callback)
    {
        $arguments = [];
        foreach ($this as $key => $argument) {
            if ($callback($argument, $key) === true) {
                $arguments[] = $argument;
            }
        }

        return new Argv(...$arguments);
    }

    public function get(string | int $search) : Argument | Command
    {
        foreach ($this as $key => $argument) {
            if (is_int($search) && $key === $search) {
                return $argument;
            } else if ($argument->name() === $search) {
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

    protected function validate($value, array $argv, int $key): void
    {
        if (!$value instanceof Option && !$value instanceof Argument && !$value instanceof Command) {
            throw new InvalidArgumentException;
        }

        if ($value instanceof Command && count(array_filter(array_slice($argv, 0, $key), function($argument) {
            return !$argument instanceof Command;
        }))) {
            throw new InvalidArgumentException('Subcommands should always be provided before positional arguments and options');
        }
    }
}
