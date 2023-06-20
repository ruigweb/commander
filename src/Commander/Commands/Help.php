<?php

declare(strict_types=1);

namespace Ruigweb\Commander\Commands;

use InvalidArgumentException;
use Ruigweb\Commander\Argv;
use Ruigweb\Commander\Command;

class Help extends Command
{
    protected $command = null;

    public function __construct(Command $command = null)
    {
        parent::__construct('help', new Argv);
        if ($command) {
            $this->on($command);
        }
    }

    public function on(Command $command) : Help
    {
        $this->command = $command;
        return $this;
    }

    public function origin() : Command
    {
        return $this->command;
    }

    public function run(Argv $argv = null) : ?string
    {
        if (empty($this->command)) {
            throw new InvalidArgumentException;
        }

        $output = $this->command->usage();
        var_dump($output);
        return $output;
    }
}
