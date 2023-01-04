<?php

declare(strict_types=1);

namespace Ruigweb\Commander;

use ArrayObject;
use InvalidArgumentException;
use Ruigweb\Commander\Command\Argument;

class Argv extends ArrayObject
{
    public function __construct(...$argv)
    {
        foreach ($argv as $var) {
            $this->validate($var);
        }

        parent::__construct($argv);
    }

    public function append($value): void
    {
        $this->validate($value);
        parent::append($value);
    }

    public function offsetSet($key, $value): void
    {
        $this->validate($value);
        parent::offsetSet($key, $value);
    }

    protected function validate($value): void
    {
        if (!$value instanceof Argument) {
            throw new InvalidArgumentException;
        }
    }
}
