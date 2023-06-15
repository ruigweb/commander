<?php
use Ruigweb\Commander\Command;
use Ruigweb\Commander\Commands\Help;

it('construct a new Help command', function () {
    $help = new Help(new Command('foo'));

    expect($help)->toBeInstanceOf(Command::class);
    expect($help->name())->toEqual('help');
});

it('accepts command to be attached', function () {
    $command = new Command('foo');
    $help = new Help;

    $help->on($command);
    expect($help->origin())->toEqual($command);
});

it('attaches provided command on Help', function () {
    $command = new Command('foo');
    $help = new Help($command);

    expect($help->origin())->toEqual($command);
});

it('will return usage of attached command', function () {
    $command = new Command('foo');
    $help = new Help($command);

    // Prevent output being shown in terminal while running test
    ob_start();
    expect($help->run())->toEqual($command->usage());
    ob_end_clean();
});

it('throws a InvalidArgumentException when no Command is attached', function () {
    $help = new Help;
    $help->run();
})->throws(InvalidArgumentException::class);
