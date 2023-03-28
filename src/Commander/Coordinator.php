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

    public function register(Command $command) : Coordinator
    {
        if (!in_array($command->name(), $this->list())) {
            array_push(self::$commanders[$this->commander], $command);
        } else {
            throw new InvalidArgumentException;
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
        array_shift($argv);
        if (count($argv) > 0) {
            // /$--?h(elp)?^/
            if ($argv[0] === '-h' || $argv[0] === '--help') {
                $command = $this->help();
            }

            if (count(self::$commanders[$this->commander]) === 0) {
                if (array_key_exists($this->commander, $this->resolvers())) {
                    // resolve the commander itself to a command
                    $command = $this->getCommandFromResolver($this->commander);
                }
            } else {
                if ($this->exists($argv[0])) {
                    $command = $this->get($argv[0]);
                }
            }
        } elseif (array_key_exists($this->commander, $this->resolvers())) {
            // resolve the commander itself to a command
            $command = $this->getCommandFromResolver($this->commander);
        }
        
        if ($command instanceof Command) {
            array_shift($argv);
            $command->take(...$argv);
            return $command;
        }
        
        throw new InvalidArgumentException;
    }

    protected function getCommandFromResolver(string $commander) : Command
    {
        if (!empty(self::$resolvers[$commander])) {
            return new Command($this->commander.':RESOLVER', self::$resolvers[$this->commander]['argv'], 'Resolver of '.$this->commander.' Commander', self::$resolvers[$this->commander]['handler']);
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
 