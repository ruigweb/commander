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
    protected $commands = [];

    protected static $commanders = [
        Coordinator::DEFAULT => []
    ];

    public function __construct(string $commander = Coordinator::DEFAULT)
    {
        $this->on($commander);
    }

    public function register(Command $command) : Coordinator
    {
        array_push($this->commands, $command);
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

    public function ingest(array $argv)
    {
        $loc = array_shift($argv);
        if (count($argv) > 0) {
            $command = array_shift($argv);
            if ($command === '-h' || $command === '--help') {
                return 
            }
        }

        throw new InvalidArgumentException;
    }

    public function __get(string $command)
    {
        if ($this->exists($command)) {
            return $this->commands[$this->commander][$command];
        }
    }

    public function exists(string $command)
    {
        return array_key_exists($command, $this->commands[$this->commander]);
    }

    public function all(): array
    {
        return $this->commands[$this->commander];
    }

    public function list()
    {
        return array_keys($this->commands[$this->commander]);
    }

    public function help(string $command = null) : ?string
    {
        if (!empty($command)) {
            if ($this->exists($command)) {
                return (new Help)->on($this->get($command));
            }
        } else {
            $argv = new Argv;
            foreach ($this->all() as $command) {
                (new Argument(mb_strtoupper($command->name()).'_HELP'))->help($command->help());
            }

            return Help(new Command('COMMANDER_HELP', $argv));
        }

        return null;
    }

    public function get(string $command) : Command
    {
        if ($this->exists($command)) {
            return $this->commands[$this->commander][$command];
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
