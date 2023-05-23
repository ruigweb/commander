<?php

declare(strict_types=1);

namespace Ruigweb\Commander;

use InvalidArgumentException;
use Ruigweb\Commander\Command;
use Ruigweb\Commander\Command\Argument;
use Ruigweb\Commander\Commands\Help;

class Coordinator
{
    const DEFAULT = '__DEFAULT__';

    protected string $commander;
    protected static $commanders = [
    ];
    protected static $resolvers = [
    ];

    public function __construct(string $commander = Coordinator::DEFAULT)
    {
        $this->on($commander);
    }

    public function name() : string
    {
        return $this->commander;
    }

    public function commanders() : array
    {
        return array_keys(self::$commanders);
    }

    public function resolvers() : array
    {
        return self::$resolvers;
    }


    public function resolver(callable|Command $handler, Argv $argv = new Argv) : Coordinator
    {
        self::$resolvers[$this->commander] = [
            'handler' => $handler,
            'argv'    => $argv,
        ];
        return $this;
    }

    public function register(...$args) : Coordinator
    {
        if (count($args) > 1) {
            $command = new Command($args[0], $args[1] ?: null, $args[2] = null, $args[3] = null);
        } else {
            $command = $args[0];
        }

        if ($command instanceof Command) {
            if (!in_array($command->name(), $this->list())) {
                array_push(self::$commanders[$this->commander], $command);
            } else {
                throw new InvalidArgumentException();
            }
        }

        return $this;
    }

    public function on(string $commander) : Coordinator
    {
        if (array_key_exists($commander, self::$commanders) === false) {
            self::$commanders[$commander] = [];
        }

        $this->commander = $commander;

        return $this;
    }

    public function ingest(array $argv = []) : Command
    {
        $command = null;

        if (count($argv) > 0) {
            if (count($argv) === 1 && ($argv[0] === '-h' || $argv[0] === '--help')) {
                $command = $this->help();
            } else {
                if ($this->exists($argv[0])) {
                    $command = $this->get($argv[0]);
                } elseif (count(self::$commanders[$this->commander]) === 0) {
                    if (array_key_exists($this->commander, $this->resolvers())) {
                        // resolve the commander itself to a command
                        $command = $this->getCommandFromResolver($this->commander);
                    }
                }
            }
        } elseif (array_key_exists($this->commander, $this->resolvers())) {
            // resolve the commander itself to a command
            $command = $this->getCommandFromResolver($this->commander);
        }

        if ($command instanceof Command) {
            // Keep looping untill no subcommand through argv[0] is found
            while (1 == 1) {
                array_shift($argv);

                if (($argv[0] ?? null) === '--help' || ($argv[0] ?? null) === '-h') {
                    $command = $this->help($command);
                }

                // Command will parse provided argv
                $command->take(...$argv);

                $parsed = $command->parsed();
                // Take a look to see if first provided argument is actually a subcommand
                if (count($parsed) > 0) {
                    $argument = $command->argv()->first();
                    if ($argument->name() == $parsed[0] && $argument instanceof Command) {
                        $command = $argument;
                        continue;
                    }
                }

                break;
            }

            return $command;
        }

        throw new InvalidArgumentException;
    }

    protected function getCommandFromResolver(string $commander) : Command
    {
        if (!empty(self::$resolvers[$commander])) {
            return new Command($this->commander.':RESOLVER', self::$resolvers[$this->commander]['argv'], self::$resolvers[$this->commander]['handler'], 'Resolver of '.$this->commander.' Commander');
        }

        throw new InvalidArgumentException;
    }

    public function __get(string $command)
    {
        return $this->get($command);
    }

    public function exists(string $command)
    {
        return in_array($command, $this->list());
    }

    public function all(): array
    {
        return self::$commanders[$this->commander];
    }

    public function list() : array
    {
        return array_column(self::$commanders[$this->commander], 'name');
    }

    public function help(string | Command $command = null) : ?Help
    {
        if (!empty($command)) {
            if (is_string($command)) {
                if ($this->exists($command)) {
                    $command = $this->get($command);
                } else {
                    throw new InvalidArgumentException;
                }
            }

            return (new Help)->on($command);
        } else {
            $argv = new Argv;
            foreach ($this->all() as $command) {
                (new Argument($command->name()))->help($command->usage(true));
            }

            return new Help(new Command('commander', $argv));
        }

        return null;
    }

    public function get(string $command) : Command
    {
        if ($this->exists($command)) {
            return self::$commanders[$this->commander][
                array_search($command, array_column(self::$commanders[$this->commander], 'name'))
            ];
        }

        throw new InvalidArgumentException;
    }

    public function run(string|Command $command, ...$args) : mixed
    {
        if (is_string($command)) {
            $command = $this->get($command);
        }

        return $command(...$args);
    }

    public function purge(string $commander = null) : array
    {
        if (is_null($commander)) {
            $commander = $this->commander;
        }

        if (!array_key_exists($commander, self::$commanders)) {
            throw new InvalidArgumentException;
        }

        $commands = self::$commanders[$commander];

        unset(self::$commanders[$commander]);
        if (array_key_exists($commander, self::$resolvers)) {
            unset(self::$resolvers[$commander]);
        }

        return $commands;
    }
}
