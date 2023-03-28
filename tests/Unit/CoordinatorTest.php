<?php

use Ruigweb\Commander\Argv;
use Ruigweb\Commander\Coordinator;
use Ruigweb\Commander\Command;

beforeEach(function () {
    $coordinator = new Coordinator;
    foreach ($coordinator->commanders() as $commander) {
        $coordinator->purge($commander);
    }
});

it('can construct a Coordinator', function() {
    $coordinator = new Coordinator;
    expect($coordinator->name())->toEqual(Coordinator::DEFAULT);
    expect($coordinator->all())->toHaveCount(0);
});

it('can construct a non default Coordinator', function() {
    $coordinator = new Coordinator('TEST');
    expect($coordinator->name())->toEqual('TEST');
    expect($coordinator->all())->toHaveCount(0);
});

it('can maintain multiple Coordinators', function() {
    $coordinator = new Coordinator('FOO');
    expect($coordinator->name())->toEqual('FOO');

    $coordinator = new Coordinator('BAR');
    expect($coordinator->name())->toEqual('BAR');

    $coordinator = $coordinator->on('FOO');
    expect($coordinator->name())->toEqual('FOO');

    $coordinator = new Coordinator;
    expect($coordinator->name())->toEqual(Coordinator::DEFAULT);
});

it('can register commands', function() {
    $coordinator = new Coordinator;
    $coordinator->register(new Command('foo', new Argv));
    $coordinator->register(new Command('bar', new Argv));

    expect($coordinator->all())->toHaveCount(2);
    expect($coordinator->get('foo'))->toBeInstanceOf(Command::class);
    expect($coordinator->get('bar')->name())->toEqual('bar');
    expect($coordinator->foo)->toBeInstanceOf(Command::class);
    expect($coordinator->list())->toMatchArray(['foo', 'bar']);
});

it('is aware of command existence', function() {
    $coordinator = new Coordinator;
    $coordinator->register(new Command('foo', new Argv));

    expect($coordinator->exists('foo'))->toBeTrue();
    expect($coordinator->exists('bar'))->toBeFalse();
});

it('can provide a list of registered commands', function() {
    $coordinator = new Coordinator('FOOBAR');
    $coordinator->register(new Command('charlie', new Argv));

    $coordinator = new Coordinator('TEST');
    $coordinator->register(new Command('bravo', new Argv));
    $coordinator->register(new Command('alpha', new Argv));

    expect($coordinator->list())->toHaveCount(2);
    expect($coordinator->list())->toBeArray(['bravo', 'alpha']);
});

it('can provide all commands of current coordinator', function() {
    $coordinator = new Coordinator('FOOBAR');
    $coordinator->register(new Command('charlie', new Argv));

    $coordinator = new Coordinator('TEST');
    $coordinator->register(new Command('bravo', new Argv));
    $coordinator->register(new Command('alpha', new Argv));

    $commands = $coordinator->all();

    expect($commands)->toHaveCount(2);
    expect($commands[0]->name())->toEqual('bravo');
    expect($commands[1]->name())->toEqual('alpha');
});

it('can not register duplicate commands', function() {
    $coordinator = new Coordinator;
    $coordinator->register(new Command('foo', new Argv));
    $coordinator->register(new Command('foo', new Argv));
})->throws(InvalidArgumentException::class);

it('throws InvalidArgumentException when purging non existing commander', function() {
    $coordinator = new Coordinator;
    $coordinator->purge('FOOBAR');
})->throws(InvalidArgumentException::class);

it('returns commands from purged commander', function() {
    $coordinator = new Coordinator('TEST');
    $coordinator->register(new Command('foo', new Argv));
    $coordinator->register(new Command('bar', new Argv));

    $commands = $coordinator->purge();

    expect($commands)->toHaveCount(2);
    expect($commands[0]->name())->toEqual('foo');
    expect($commands[1]->name())->toEqual('bar');

    expect($coordinator->commanders())->not->toHaveKey('TEST');

    expect((new Coordinator('TEST'))->list())->not->toContain('alpha');
});

it('can purge other commanders', function() {
    $coordinator = new Coordinator('TEST');
    $coordinator->register(new Command('alpha', new Argv));
    $coordinator->register(new Command('bravo', new Argv));

    $coordinator = new Coordinator('FOOBAR');
    $coordinator->register(new Command('charlie', new Argv));
    $commands = $coordinator->purge('TEST');

    expect($commands)->toHaveCount(2);
    expect($commands[0]->name())->toEqual('alpha');
    expect($commands[1]->name())->toEqual('bravo');

    expect($coordinator->commanders())->not->toHaveKey('TEST');
    expect($coordinator->commanders())->toContain('FOOBAR');

    expect($coordinator->list())->toContain(('charlie'));

    expect((new Coordinator('TEST'))->list())->not->toContain('alpha');
});

it('throws InvalidArgumentException when retrieving non registered command', function() {
    $coordinator = new Coordinator;
    $coordinator->register(new Command('foo', new Argv));

    $coordinator->get('bar');
})->throws(InvalidArgumentException::class);

it ('adds a resolver through callable', function() {
    $coordinator = new Coordinator;
    expect($coordinator->resolver(function() {
        return 'foobar';
    }))->toBe($coordinator);

    expect($coordinator->resolvers())->toHaveKey(Coordinator::DEFAULT);

});

it('throws InvalidArgumentException when no commands or resolver defined', function() {
    $coordinator = new Coordinator;
    $coordinator->ingest();
})->throws(InvalidArgumentException::class);

it('runs resolver when no commands are defined', function() {
    $coordinator = new Coordinator;
    $coordinator->resolver(function() {
        return 'foobar';
    }, new Argv);
    $command = $coordinator->ingest([]);

    expect($command)->toBeInstanceOf(Command::class);
    expect($coordinator->run($command))->toEqual('foobar');
});

it('throws InvalidArgumentException when resolver is not present to get command from', function() {
    $coordinatorMock = Mockery::mock(Coordinator::class)->makePartial();
    $coordinatorMock->shouldReceive('resolvers')->once()->andReturn([
        Coordinator::DEFAULT => []
    ]);

    $coordinatorMock->on(Coordinator::DEFAULT);
    $coordinatorMock->ingest(['test']);
})->throws(InvalidArgumentException::class);

it('can run from command provided as string', function() {
    $coordinatorMock = Mockery::mock(Coordinator::class)->makePartial();
    $coordinatorMock->shouldReceive('get')->once()->andReturn(new Command('foobar', new Argv, null, function() {}));

    $coordinatorMock->on(Coordinator::DEFAULT);
    $coordinatorMock->run('FooBarCommand');
});