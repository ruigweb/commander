<?php

use Ruigweb\Commander\Command;
use Ruigweb\Commander\Command\Option;
use Ruigweb\Commander\Command\Argument;

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
