<?php

use phpmock\mockery\PHPMockery;
use Ruigweb\Commander\Argv;
use Ruigweb\Commander\Command;
use Ruigweb\Commander\Command\Argument;
use Ruigweb\Commander\Commands\Printer;

afterEach(function () {
    Mockery::close();
});

it('construct a new Printer command', function () {
    $printer = new Printer;

    expect($printer)->toBeInstanceOf(Command::class);
    expect($printer->name())->toEqual('printer');
    expect($printer->argv())->toHaveCount(3);
});

it('streams to php://output by default', function () {
    $printer = new Printer;
    expect($printer->argv()['stream']->value())->toEqual('output');
});

it('escapes prints by default', function () {
    $printer = new Printer;
    expect($printer->argv()['escape']->value())->toEqual(true);
});

it('prints on invoking the printer', function () {
    $printerMock = Mockery::mock(Printer::class)->makePartial();
    $printerMock->shouldReceive('run')->once()->with(null)->andReturn('foo');

    $output = $printerMock();
    expect($output)->toEqual('foo');
});

it('prints by writing to default php output stream', function () {
    $fp = fopen('php://temp', 'w');

    $fOpenMock = PHPMockery::mock('Ruigweb\Commander\Commands', 'fopen');
    $fOpenMock->with('php://output', 'w')->once()->andReturn($fp);

    $fOpenMock = PHPMockery::mock('Ruigweb\Commander\Commands', 'fwrite');
    $fOpenMock->with($fp, 'foo'.PHP_EOL)->once()->andReturn(3);

    $fOpenMock = PHPMockery::mock('Ruigweb\Commander\Commands', 'fclose');
    $fOpenMock->with($fp)->once()->andReturn(true);
    
    $output = (new Argument('output', 'string'))->set('foo');
    
    $printer = new Printer;
    $printer->run(new Argv(
        $output,
    ));
});

it('prints to defined php stream', function () {
    $fp = fopen('php://temp', 'w');

    $fOpenMock = PHPMockery::mock('Ruigweb\Commander\Commands', 'fopen');
    $fOpenMock->with('php://bar', 'w')->once()->andReturn($fp);

    $fOpenMock = PHPMockery::mock('Ruigweb\Commander\Commands', 'fwrite');
    $fOpenMock->with($fp, 'foo'.PHP_EOL)->once()->andReturn(3);

    $fOpenMock = PHPMockery::mock('Ruigweb\Commander\Commands', 'fclose');
    $fOpenMock->with($fp)->once()->andReturn(true);
    
    $output = (new Argument('output', 'string'))->set('foo');
    $stream = (new Argument('stream', 'string'))->set('bar');
    
    $printer = new Printer;
    $printer->run(new Argv(
        $output,
        $stream
    ));
});

it('escapes output before sending to stream', function () {
    $fp = fopen('php://temp', 'w');

    $fOpenMock = PHPMockery::mock('Ruigweb\Commander\Commands', 'fopen');
    $fOpenMock->with('php://output', 'w')->once()->andReturn($fp);

    $fOpenMock = PHPMockery::mock('Ruigweb\Commander\Commands', 'fwrite');
    $fOpenMock->with($fp, escapeshellcmd('f[o]o').PHP_EOL)->once()->andReturn(3);

    $fOpenMock = PHPMockery::mock('Ruigweb\Commander\Commands', 'fclose');
    $fOpenMock->with($fp)->once()->andReturn(true);
    
    $output = (new Argument('output', 'string'))->set('f[o]o');
    
    $printer = new Printer;
    $printer->run(new Argv(
        $output,
    ));
});

it('directly prints a message', function () {
    $printerMock = Mockery::mock(Printer::class, [])->makePartial();
    $printerMock->shouldReceive('run')->once()->withArgs(function (...$args) {
        if (count($args) === 1 && $args[0] instanceof Argv) {
            $argv = $args[0];
            if ($argv['escape']->value() === false && $argv['output']->value() === 'foo') {
                return true;
            }
        }

        return false;
    })->andReturn('result');

    expect($printerMock->print('foo'))->toEqual('result');
});

it('prints done message', function () {
    $printerMock = Mockery::mock(Printer::class, [])->makePartial();
    $printerMock->shouldReceive('run')->once()->withArgs(function ($argv) {
        return (bool) preg_match('/\['.Printer::DONE.'\]/', $argv['output']->value());
    })->andReturn('foo');

    $printerMock->done('foo');
    $output = $printerMock->print();
    expect($output)->toBe('foo');
});

it('prints info message', function () {
    $printerMock = Mockery::mock(Printer::class, [])->makePartial();
    $printerMock->shouldReceive('run')->once()->withArgs(function ($argv) {
        return (bool) preg_match('/\['.Printer::INFO.'\]/', $argv['output']->value());
    })->andReturn('foo');

    $printerMock->info('foo');
    $output = $printerMock->print();
    expect($output)->toBe('foo');
});

it('prints debug message', function () {
    $printerMock = Mockery::mock(Printer::class, [])->makePartial();
    $printerMock->shouldReceive('run')->once()->withArgs(function ($argv) {
        return (bool) preg_match('/\['.Printer::DEBUG.'\]/', $argv['output']->value());
    })->andReturn('foo');

    $printerMock->debug('foo');
    $output = $printerMock->print();
    expect($output)->toBe('foo');
});

it('provides warn wrapper method', function () {
    $printerMock = Mockery::mock(Printer::class, [])->makePartial();
    $printerMock->shouldReceive('warn')->once()->with('foo', true)->andReturn($printerMock);

    $printerMock->warning('foo');
    $printerMock->print();
});

it('prints warning message', function () {
    $printerMock = Mockery::mock(Printer::class, [])->makePartial();
    $printerMock->shouldReceive('run')->once()->withArgs(function ($argv) {
        return (bool) preg_match('/\['.Printer::WARNING.'\]/', $argv['output']->value());
    })->andReturn('foo');

    $printerMock->warn('foo');
    $output = $printerMock->print();
    expect($output)->toBe('foo');
});

it('prints error message', function () {
    $printerMock = Mockery::mock(Printer::class, [])->makePartial();
    $printerMock->shouldReceive('run')->once()->withArgs(function ($argv) {
        return (bool) preg_match('/\['.Printer::ERROR.'\]/', $argv['output']->value());
    })->andReturn('foo');

    $printerMock->error('foo');
    $output = $printerMock->print();
    expect($output)->toBe('foo');
});

it('prints message defined by level', function () {
    $printerMock = Mockery::mock(Printer::class, [])->makePartial();
    $printerMock->shouldReceive('run')->once()->withArgs(function ($argv) {
        return (bool) preg_match('/\['.Printer::ERROR.'\]/', $argv['output']->value());
    })->andReturn('foo');

    $output = $printerMock->print('foo', Printer::ERROR);
    expect($output)->toBe('foo');
});
