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
        $this->on($command);
    }

    public static function make(Command $command)
    {

    }

    public function on(Command $command)
    {
        $this->command = $command;
    }

    public function run(Argv $argv = null) : ?string
    {
        if (empty($this->command)) {
            throw new InvalidArgumentException;
        }
        
        $output = $this->command->usage();

        return $output;
    }
}