<?php

use Ruigweb\Commander\Argv;
use Ruigweb\Commander\Command;
use Ruigweb\Commander\Command\Argument;
use Ruigweb\Commander\Command\Option;

it('can construct a new command', function () {
    $command = new Command('foobar', new Argv);
    expect($command)->toBeInstanceOf(Command::class);
});

it('provides name of command', function () {
    $command = new Command('foobar', new Argv);
    expect($command->name())->toBe('foobar');
});

it('provides desciption of command', function () {
    $command = new Command('foobar', new Argv, null, 'foobar command');
    expect($command->description())->toBe('foobar command');
});

it('provides output on usage of command', function () {
    $command = new Command('foobar', new Argv(
        (new Argument('argument_test'))->help('this'),
        (new Option('option_test'))->help('this')
    ));
    
    expect($command->usage())->toContain('foobar');
    expect($command->usage())->toContain('argument_test');
    expect($command->usage())->toContain('option_test');
    expect($command->usage())->toContain('this');
});

it('takes a list of arguments', function () {
    $command = new Command('foobar', new Argv(
        new Argument('argument-1'),
        new Argument('argument-2'),
    ));

    $command = $command->take('test', 'this');
    expect($command)->toBeInstanceOf(Command::class);

    expect($command->argv()->get('argument-1')->value())->toBe('test');
    expect($command->argv()->get('argument-2')->value())->toBe('this');
});

it('constructs a list of parsed arguments', function () {
    $command = new Command('foobar', new Argv(
        new Argument('argument-1'),
        new Argument('argument-2'),
    ));

    $command = $command->take('test', 'this');
    $parsed = $command->parsed();

    expect($parsed)->toBeArray();
    expect($parsed)->toHaveCount(2);
    expect($parsed)->toContain('argument-1');
    expect($parsed)->toContain('argument-2');
});

it('throws InvalidArgumentException when no handler is defined on run', function () {
    $command = new Command('foobar', new Argv);
    $command->run();
})->throws(InvalidArgumentException::class);

it('matches name when provided', function () {
    $command = new Command('foobar');
    expect($command->matches('foobar'))->toBeTrue();
    expect($command->matches('barfoo'))->toBeFalse();
});
