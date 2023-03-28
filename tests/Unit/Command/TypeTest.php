<?php

use Ruigweb\Commander\Command\Option;
use Ruigweb\Commander\Command\Type;

it('construct a new boolean type', function() {
    $booleantype = Type::BOOLEAN;

    expect($booleantype->value)->toEqual('boolean');
});

it('construct a new string type', function() {
    $stringtype = Type::STRING;

    expect($stringtype->value)->toEqual('string');
});

it('construct a new integer type', function() {
    $integertype = Type::INTEGER;

    expect($integertype->value)->toEqual('integer');
});

it('formats value to boolean', function() {
    $booleanType = Type::BOOLEAN;

    expect($booleanType->format('true', null))->toEqual(true);
    expect($booleanType->format('false', null))->toEqual(false);
    expect($booleanType->format('t', null))->toEqual(true);
    expect($booleanType->format('f', null))->toEqual(false);
    expect($booleanType->format('1', null))->toEqual(true);
    expect($booleanType->format('0', null))->toEqual(false);
});

it('throws exception when invalid boolean value is provided', function() {
    $booleanType = Type::BOOLEAN;
    expect($booleanType->format('foo', null));
})->throws(InvalidArgumentException::class);

it('defaults to false when no value for boolean is provided', function() {
    $booleanType = Type::BOOLEAN;
    expect($booleanType->format(null, null))->toEqual(false);
});

it('throws exception when invalid integer value is provided', function() {
    $booleanType = Type::INTEGER;
    expect($booleanType->format('foo', null));
})->throws(InvalidArgumentException::class);

it('formats value to integer', function() {
    $booleanType = Type::INTEGER;

    expect($booleanType->format('0', null))->toEqual(0);
    expect($booleanType->format('90', null))->toEqual(90);
});

it('throws exception when invalid string value is provided', function() {
    $booleanType = Type::STRING;
    expect($booleanType->format(null, null));
})->throws(InvalidArgumentException::class);

it('formats value to string', function() {
    $booleanType = Type::STRING;

    expect($booleanType->format('foo', null))->toEqual('foo');
    expect($booleanType->format('90', null))->toEqual('90');
    expect($booleanType->format('true', null))->toEqual('true');
});

it('defaults to provided value when no value for string is provided', function() {
    $booleanType = Type::STRING;
    expect($booleanType->format(null, 'foo'))->toEqual('foo');
});
