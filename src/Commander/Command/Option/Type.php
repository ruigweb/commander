<?php

namespace Ruigweb\Commander\Command\Option;

enum Type: string
{
    case BOOLEAN = 'boolean';
    case INTEGER = 'integer';
    case STRING  = 'string';

    public function format(string $value, mixed $default): mixed
    {
        return match($this)
        {
            Type::BOOLEAN => $this->formatBoolean($value, $default)
        };
    }

    protected function formatBoolean(string $value, bool $default) : bool
    {
        $value = mb_strtolower($value);
        if (mb_strlen($value) > 0) {
            return ($value === '1' || $value === 'true' || $value === 't') ? true : false;
        }

        return $default;
    }
}
