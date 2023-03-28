<?php

use Ruigweb\Commander\Command\Option;
use Ruigweb\Commander\Command\Type;

it('construct a new option', function() {
    $option = new Option('foo');

    expect($option->name())->toEqual('foo');
});

it('return abbrevation of option', function() {
    $option = new Option('foo');

    expect($option->abbr)->toEqual('f');
    expect($option->abbreviation)->toEqual('f');
});

it('throws a InvalidArgumentException when invalid property is requested', function() {
    $option = new Option('foo');
    $option->bar;

})->throws(InvalidArgumentException::class);

it('matches a provided argument by name', function() {
    $option = new Option('foo');
    expect($option->matches('--foo=bar'))->toBeTrue();
});

it('does not match without = sign', function() {
    $option = new Option('foo');
    expect($option->matches('--foo'))->toBeFalse();
});

it('does match empty option', function() {
    $option = new Option('foo');
    expect($option->matches('--foo='))->toBeTrue();
});

it('does match abbreviated boolean option', function() {
    $option = new Option('bar', Type::BOOLEAN);
    expect($option->matches('-b'))->toBeTrue();
});

it('does not match abbreviated option for type other then boolean', function() {
    $option = new Option('bar', Type::STRING);
    expect($option->matches('-b'))->toBeFalse();

    $option = new Option('bar', Type::INTEGER);
    expect($option->matches('-b'))->toBeFalse();
});

it('does not match abbreviated option when short is not allowed', function() {
    $option = new Option('foo');
    $option->short(false);
    expect($option->matches('-f'))->toBeFalse();
});

it('does not match arguments which are not prefixed with a -', function() {
    $option = new Option('foo');
    expect($option->matches('foo=bar'))->toBeFalse();
});

it('parses provided string option', function() {
    $option = new Option('foo', Type::STRING);
    $option->parse('--foo=bar');
    expect($option->value())->toEqual('bar');
});

it('parses provided boolean option', function() {
    $option = new Option('foo', Type::BOOLEAN);
    $option->parse('--foo=true');
    expect($option->value())->toEqual(true);

    $option->parse('--foo=t');
    expect($option->value())->toEqual(true);

    $option->parse('--foo=1');
    expect($option->value())->toEqual(true);

    $option->parse('--foo=false');
    expect($option->value())->toEqual(false);

    $option->parse('--foo=f');
    expect($option->value())->toEqual(false);

    $option->parse('--foo=0');
    expect($option->value())->toEqual(false);
});

it('parses provided abbreviated boolean option', function() {
    $option = new Option('foo', Type::BOOLEAN);
    $option->parse('-f');

    expect($option->value())->toEqual(true);

    $option = new Option('foo', Type::BOOLEAN);

    expect($option->value())->toEqual(false);
});

it('parses provided integer option', function() {
    $option = new Option('foo', Type::INTEGER);
    $option->parse('--f=100');

    expect($option->value())->toEqual(100);


    $option = new Option('foo', Type::INTEGER, 80);
    $option->parse('--f=');

    expect($option->value())->toEqual(80);
});

it('parses empty option to default value', function() {
    $option = new Option('foo', Type::STRING, 'bar');
    $option->parse('--foo=');
    expect($option->value())->toEqual('bar');
});

it('throws Exception when no default is defined', function() {
    $option = new Option('foo', Type::INTEGER);
    $option->parse('--f=');
})->throws(InvalidArgumentException::class);

it('throws Exception when no valid arg is provided', function() {
    $option = new Option('foo', Type::INTEGER);
    $option->parse('foo');
})->throws(InvalidArgumentException::class);