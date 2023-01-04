<?php

declare(strict_types=1);

namespace Ruigweb\Commander;

class Command {

    protected $args;

    public function __construct(protected string $command, array $arguments = []) {}

    public function __invoke(...$argv)
    {

    }
}
