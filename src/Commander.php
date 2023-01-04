<?php

declare(strict_types=1);

namespace Ruigweb\Commander;

use InvalidArgumentException;
use Ruigweb\Commander\Command;

class Commander
{
    protected $commands = [];

    public function register(Command $command) : Commander
    {
        array_push($this->commands, $command);
        return $this;
    }

    public function __get(string $command)
    {
        if ($this->exists($command)) {
            return $this->commands[$command];
        }
    }

    public function exists(string $command)
    {
        return array_key_exists($command, $this->commands);
    }

    public function all(): array
    {
        return $this->commands;
    }

    public function list()
    {
        return array_keys($this->commands);
    }

    public function help(string $command) : ?string
    {
        if ($this->exists($command)) {
            return $this->get($command)('--help');
        }

        return null;
    }

    public function get(string $command) : Command
    {
        if ($this->exists($command)) {
            return $this->commands[$command];
        }

        throw new InvalidArgumentException;
    }

    public function run(string|Command $command, ...$args) : mixed
    {
        if (is_string($command)) {
            $command = $this->get($command);
        }

        if ($command instanceof Command) {
            return $command(...$args);
        }

        throw new InvalidArgumentException;
    }
}
