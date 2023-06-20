<?php

use Ruigweb\Commander\Command;
use Ruigweb\Commander\Command\Option;
use Ruigweb\Commander\Command\Argument;
use Ruigweb\Commander\Argv;
use Ruigweb\Commander\Coordinator;
use Ruigweb\Commander\Commands\Printer;

// @codeCoverageIgnoreStart
if (function_exists('coordinator') === false) {
    function coordinator(...$args)
    {
        return new Coordinator(...$args);
    }
}


if (function_exists('command') === false) {
    function command(...$args)
    {
        return new Command(...$args);
    }
}

if (function_exists('option') === false) {
    function option(...$args)
    {
        return new Option(...$args);
    }
}

if (function_exists('argument') === false) {
    function argument(...$args)
    {
        return new Argument(...$args);
    }
}

if (function_exists('argv') === false) {
    function argv(...$args)
    {
        return new Argv(...$args);
    }
}

if (function_exists('write') === false) {
    function write(...$args)
    {
        return (new Printer)->print(...$args);
    }
}
// @codeCoverageIgnoreEnd
