<?php

namespace Ruigweb\Commander\Command;

use InvalidArgumentException;

enum Type: string
{
    case BOOLEAN = 'boolean';
    case INTEGER = 'integer';
    case STRING  = 'string';
    // Float
    // List
    // Date

    public function format(string $value = null, mixed $default): mixed
    {
        return match($this)
        {
            Type::BOOLEAN => $this->formatBoolean($value, $default ?? false),
            Type::INTEGER => $this->formatInteger($value, $default),
            Type::STRING  => $this->formatString($value, $default)
        };
    }

    protected function formatBoolean(string $value = null, bool $default) : bool
    {
        $value = mb_strtolower($value);
        if (mb_strlen($value) > 0) {
            if ($value === '1' || $value === 'true' || $value === 't') {
                return true;
            } elseif ($value === '0' || $value === 'false' || $value === 'f') {
                return false;
            } else {
                throw new InvalidArgumentException;
            }
        }

        return $default;
    }

    protected function formatInteger(string $value = null, int $default = null) : int
    {
        if (preg_match('/^-?[0-9]+$/', $value)) {
            return (int) $value;
        }

        if (!is_null($value) && $value != '') {
            throw new InvalidArgumentException;
        }

        if (is_null($default)) {
            throw new InvalidArgumentException;
        }

        return $default;
    }

    protected function formatString(string $value = null, string $default = null) : string
    {
        if (!is_null($value)) {
            return (string) $value;
        }

        if (is_null($default)) {
            throw new InvalidArgumentException;
        }

        return $default;
    }

    public function toString(mixed $value) : string
    {
        return match($this)
        {
            Type::BOOLEAN => ($value === true) ? 'true' : 'false',
            Type::INTEGER => (string) $value,
            Type::STRING => (string) $value
        };
    }

    public function default(mixed $default = null) : mixed
    {
        return match($this)
        {
            Type::BOOLEAN => ($default === true || $default === false) ? $default : false,
            Type::INTEGER => preg_match('/^[0-9]+$/', $default) ? $default : null,
            Type::STRING  => (is_string($default)) ? $default : null
        };
    }
}
