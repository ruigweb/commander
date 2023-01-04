<?php

assert(file_exists($_SERVER['APP_PATH'].'/vendor/autoload.php'));
require_once($_SERVER['APP_PATH'].'/vendor/autoload.php');

use Ruigweb\Commander\Coordinator;

// No need to know the file location
array_shift($argv);

// Try to build the coordinator
// Three possible scenarios to do so
// - Defining a env variable COMMANDER_REGISTER which locates to a file to require
// - Create commander.php which is located in the app source path to require
// - Building a new coordinator, based on the env variable COMMANDER_COORDINATOR,
// which is registered somewhere before this file is loaded / required
$coordinator = null;
if (!empty($_SERVER['COMMANDER_REGISTER'])) {
    assert(file_exists($_SERVER['COMMANDER_REGISTER']));
    $coordinator = require_once($_SERVER['COMMANDER_REGISTER']);
} elseif (is_file($_SERVER['APP_PATH'].'./commander.php')) {
    $coordinator = require_once($_SERVER['APP_PATH'].'./commander.php');
}

if ($coordinator instanceof Coordinator === false) {
    $coordinator = new Coordinator($_SERVER['COMMANDER_COORDINATOR'] ?? Coordinator::DEFAULT);
}

$command = array_shift($argv);
$result  = $coordinator->run($command, ...$args);
