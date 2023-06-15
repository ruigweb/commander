<?php

use Ruigweb\Commander\Argv;
use Ruigweb\Commander\Command;
use Ruigweb\Commander\Command\Argument;
use Ruigweb\Commander\Command\Option;

it('throws InvalidArgumentException when retrieving non existing argument', function () {
    $argv = new Argv(
        new Argument('bar'),
    );

    $argv->get('test');
})->throws(InvalidArgumentException::class);

it('validates provided arguments on construction', function () {
    $argv = new Argv(
        new stdClass
    );
})->throws(InvalidArgumentException::class);

it('returns a list of arguments in the argv', function () {
    $argv = new Argv(
        new Argument('bar'),
        new Option('foo'),
        new Argument('alpha')
    );

    $arguments = $argv->arguments();
    expect($arguments)->toHaveCount(2);
    expect($arguments[0]::class)->toEqual(Argument::class);
    expect($arguments[1]::class)->toEqual(Argument::class);
});

it('return a list of options in the argv', function () {
    $argv = new Argv(
        new Argument('bar'),
        new Option('foo'),
        new Argument('alpha')
    );

    $arguments = $argv->options();
    expect($arguments)->toHaveCount(1);
    expect($arguments[0]::class)->toEqual(Option::class);
});

it('accepts arguments, options and commands', function () {
    $argv = new Argv(
        new Command('baz'),
        new Argument('foo'),
        new Option('bar'),
    );

    expect($argv->count())->toEqual(3);
    expect($argv[0])->toBeInstanceOf(Command::class);
});

it('parses array of argument, options and commands', function () {
    $argv = new Argv([
        new Command('baz'),
        new Argument('foo'),
        new Option('bar'),
    ]);

    expect($argv->count())->toEqual(3);
    expect($argv[0])->toBeInstanceOf(Command::class);
});

it('throws a Exception when Command is not provided before arguments and options', function () {
    $argv = new Argv(
        new Argument('foo'),
        new Command('baz'),
    );
})->throws(InvalidArgumentException::class);

it('throws a Exception when trying to append a argument', function () {
    $argv = new Argv(
        new Argument('bar'),
    );

    $argv->append(new Option('foo'));
})->throws(Exception::class);

it('throws a Exception when trying to offsetSet a argument', function () {
    $argv = new Argv(
        new Argument('bar'),
    );

    $argv[] = new Option('foo');
})->throws(Exception::class);

it('throws a Exception when trying to offsetUnset a argument', function () {
    $argv = new Argv(
        new Argument('bar'),
    );

    unset($argv[0]);
})->throws(Exception::class);

it('returns a argument through array access', function () {
    $argv = new Argv(
        new Argument('bar'),
    );

    expect($argv['bar'])->toBeInstanceOf(Argument::class);
    expect($argv['bar']->name())->toEqual('bar');
});

it('returns first argument', function () {
    $argv = new Argv(
        new Argument('foo'),
        new Argument('bar'),
        new Option('baz'),
    );

    expect($argv->first())->toBeInstanceOf(Argument::class);
    expect($argv->first()->name())->toEqual('foo');
});

it('create new (cloned) arguments in argv instance when cloning', function () {
    $arg = new Argument('foo');
    $opt = new Option('bar');

    $argv = new Argv($arg, $opt);
    $cloned = clone $argv;

    expect($cloned['foo'])->not->toBe($arg);
    expect($cloned['bar'])->not->toBe($opt);
});
