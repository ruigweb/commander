<?php

declare(strict_types=1);

namespace Ruigweb\Commander;

use ArrayObject;
use InvalidArgumentException;
use Ruigweb\Commander\Command;
use Ruigweb\Commander\Command\Argument;
use Ruigweb\Commander\Commands\Help;

class Coordinator
{
    const DEFAULT = '__DEFAULT__';

    protected string $commander;
    protected static $commanders = [
        Coordinator::DEFAULT => []
    ];
    protected static $resolvers = [
        Coordinator::DEFAULT => []
    ];

    public function __construct(string $commander = Coordinator::DEFAULT)
    {
        $this->on($commander);
    }

    public function resolver(callable|Command $handler) : Coordinator
    {
        self::$resolvers[$this->commander] = $handler;
        return $this;
    }

    public function register(Command $command) : Coordinator
    {
        array_push(self::$commanders[$this->commander], $command);
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

    public function ingest(array $argv) : Command
    {
        $command = null;
        array_shift($argv);
        if (count($argv) > 0) {
            if ($argv[0] === '-h' || $argv[0] === '--help') {
                $command = $this->help();
            }

            if (count(self::$commanders[$this->commander]) === 0) {
                if (!empty(self::$resolvers[$this->commander])) {
                    // resolve the commander itself to a command
                }
            } else {
                if ($this->exists($argv[0])) {
                    $command = $this->get($argv[0]);
                }
            }
        } elseif (!empty(self::$resolvers[$this->commander])) {
            // resolve the commander itself to a command
        }

        if ($command instanceof Command) {
            $command->take(...$argv);
            return $command;
        }

        throw new InvalidArgumentException;
    }

    public function __get(string $command)
    {
        return $this->get($command);
    }

    public function exists(string $command)
    {
        return in_array($command, array_column(self::$commanders[$this->commander], 'name'));
    }

    public function all(): array
    {
        return self::$commanders[$this->commander];
    }

    public function list()
    {
        return array_keys(self::$commanders[$this->commander]);
    }

    public function help(string $command = null) : ?Help
    {
        if (!empty($command)) {
            if ($this->exists($command)) {
                return (new Help)->on($this->get($command));
            }
        } else {
            $argv = new Argv;
            foreach ($this->all() as $command) {
                (new Argument($command->name()))->help($command->usage(true));
            }
            
            return new Help(new Command('Commander', $argv));
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

        if ($command instanceof Command) {
            return $command(...$args);
        }

        throw new InvalidArgumentException;
    }
}
