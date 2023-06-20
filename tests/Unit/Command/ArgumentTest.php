<?php

use Ruigweb\Commander\Command\Argument;
use Ruigweb\Commander\Command\Type;

it('construct a new argument', function () {
    $argument = new Argument('foo');

    expect($argument->name())->toEqual('foo');
});

it('throws a Exception when name contains invalid characters', function () {
    $argument = new Argument('hfj#j');
})->throws(InvalidArgumentException::class);

it('defaults the type to string', function () {
    $argument = new Argument('foo');

    expect($argument->type())->toBeInstanceOf(Type::class);
});

it('parses provided Type as string to Type', function () {
    $argument = new Argument('foo', 'string');

    expect($argument->type())->toBeInstanceOf(Type::class);
});

it('parses argument to default when no value is defined', function () {
    $argument = new Argument('foo', Type::STRING, 'bar');

    expect((string) $argument)->toEqual('bar');
});

it('parses argument type string to string', function () {
    $argument = new Argument('foo', Type::STRING);
    $argument->parse('bar');

    expect((string) $argument)->toEqual('bar');
});

it('parses argument type boolean to string', function () {
    $argument = new Argument('foo', Type::BOOLEAN);
    $argument->parse('true');

    expect((string) $argument)->toEqual('true');

    $argument = new Argument('foo', Type::BOOLEAN);
    $argument->parse('t');

    expect((string) $argument)->toEqual('true');

    $argument = new Argument('foo', Type::BOOLEAN);
    $argument->parse('1');

    expect((string) $argument)->toEqual('true');

    $argument = new Argument('foo', Type::BOOLEAN);
    $argument->parse('false');

    expect((string) $argument)->toEqual('false');

    $argument = new Argument('foo', Type::BOOLEAN);
    $argument->parse('f');

    expect((string) $argument)->toEqual('false');

    $argument = new Argument('foo', Type::BOOLEAN);
    $argument->parse('0');

    expect((string) $argument)->toEqual('false');
});

it('parses argument type integer to string', function () {
    $argument = new Argument('foo', Type::INTEGER);
    $argument->parse('235');

    expect((string) $argument)->toEqual('235');
});

it('returns the parsed value', function () {
    $argument = new Argument('foo', Type::STRING);
    $argument->parse('bar');

    expect($argument->value())->toEqual('bar');

    $argument = new Argument('foo', Type::BOOLEAN);
    $argument->parse('true');

    expect($argument->value())->toEqual(true);

    $argument = new Argument('foo', Type::INTEGER);
    $argument->parse('754');

    expect($argument->value())->toEqual(754);
});

it('provides output on usage of argument', function () {
    $argument = new Argument('foo', Type::INTEGER, 20);
    
    expect($argument->usage())->toContain('foo');
    expect($argument->usage())->toContain('(integer:20)');
});

it('provides output on usage of argument with help', function () {
    $argument = new Argument('foo', Type::INTEGER, 20);
    $argument->help('bar');
    
    expect($argument->usage())->toContain('foo');
    expect($argument->usage())->toContain('(integer:20)');
    expect($argument->usage())->toContain('bar');
});

it('allows setting value directly', function () {
    $argument = new Argument('foo', Type::INTEGER, 20);
    $argument->set([1, 2]);

    expect($argument->value())->toEqual([1, 2]);
});
