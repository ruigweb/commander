# Commander

[![Test Coverage](https://raw.githubusercontent.com/ruigweb/commander/main/badge-coverage.svg)](https://packagist.org/packages/ruigweb/commander)
[![Tests](https://github.com/ruigweb/commander/actions/workflows/tests.yml/badge.svg)
![Packagist Downloads (custom server)](https://img.shields.io/packagist/dt/ruigweb/commander)
![Packagist Version (custom server)](https://img.shields.io/packagist/v/ruigweb/commander)

🫡 Take command!


```php
command('Make it awesome', null, function() {
    return 'Anytime!';
});
```

Take control of the command line and instruct it to run any command.
Commander provides a framework independent way to create CLI commands for your PHP applications. Simple, as you see fit. 
What happens when one of your commands gets executed? Anything you can come up with!
Your application, your CLI commands.

**Commander** can be found on Packagist, its recommended to install **Commander** through composer by running the following command:

```bash
$ php composer require ruigweb/commander
```

Now create a empty `commander.php` file in the root directory of your application.

So, everything is installed? 
Let's start simple

```php
# ./commander.php

return [
    command('March', null, fn() => echo 'Marching'),
    command('Halt', null, fn() => echo 'At Ease')
];
```

Sure, now we have to commands, so how can we use them? 🤔

```bash
$ php vendor/bin/instruct March
$ Marching
```

Simple, right? Just use `vendor/bin/instruct`, provided by Commander, to execute your commands.

Assuming your own awesome commands will be a little more complicated 😏, we will take it one step further.

```php
return [
    command('March', argv(
        argument('direction'),
    ), fn() => echo 'Marching'),
    command('Halt', argv(
        option('in', 'int', '0'),
    ), fn() => echo 'At Ease')
];
```

We now provide some arguments for our commands, which can be provided when executing a command. These arguments can be *positional arguments*, or *optional arguments*.
Besides of the name of the argument, you can also define how the provided value should be parsed, and its default value.
Arguments can currently be parsed as a string (default), integer, and boolean.

Well, thats all nice, but how to use those arguments?

```bash
$ php vendor/bin/instruct March forward
$ Marching

$ php vendor/bin/instruct Halt --in=5
$ At Ease
```

So we provided arguments, but we aren't really using them... Thats were handlers will make sense. Handlers are callbacks which will be executed once a command gets executed. Commander basically provides a layer between the CLI and your application through the handlers you define. Next up for our example is creating some handlers to do something with the input. The simpelest form of handler is a  basic closure. In the background it will use the `Closure::fromCallable` functionality to execute your handlers.

```php

return [
    command('March', argv(
        argument('direction'),
    ), function($argv) {
        return 'Marching '.$argv['direction'];
    }),
    command('Halt', argv(
        option('in', 'int', '0'),
    ), function($argv) {
        return 'At Ease in '. (string) $argv['in'].' minutes';
    });
];
```

```bash
$ php vendor/bin/instruct March forward
$ Marching forward

$ php vendor/bin/instruct Halt --in=5
$ At Ease in 5 minutes
```

So these are the basics, but with a bit of imagination, the possibilities are endless.
You are the magician, make it awesome!

## Deepdive

The following sections will describe the building blocks of Commander in more detail.

## Coordinator

The Coordinator acts as a container where your Commands get registered. There are several way to register your Commands on the Coordinator. Once you try to execute a certain command with the use of `vendor/bin/instruct`, the Coordinator will try to following steps to *locate* and execute your command.

### `COMMANDER_REGISTER`
It will verify a optional existence of the `COMMANDER_REGISTER` enviroment variable. In case this variable exists, it will try to require the variable a file and use the response to register your commands.

### `commander.php`
Next step will be to try and locate a `commander.php` file in the root of your project. The root of your project will be defined as the directory where the composer `vendor` directory is located.
This *root* can optionally be defined rough the `APP_PATH` environment variables.
In the `commander.php` file exists, it will required to register your commands.

### `COMMANDER_COORDINATOR`
A last step when the first two *fail*, will be the creation / loading of a Coordinator. In case the `COMMANDER_COORDINATOR` environment variable exists, it will use that to construct the Coordinator, otherwise a new Coordinator will be constructed.
This last step can be used for more complicated ways to use a Coordinator, for example to register a custom Coordinator before  `instruct` gets executed.

### Custom
If none of the above steps provides a decent way to use Commander in your application, you can also start playing with the Coordinator yourself. Just construct a new Coordinator at your desired location, and register Commands to your hearts consent.

```php
$coordinator = new Coordinator;
$coordinator->register(new Command('March'));
```

The Coordinator allows to seperate commands in different sections by creating multiple Coordinators.

```php
$coordinator = new Coordinator('A');
$coordinator->register(new Command('Register to Coordinator A'));

$coordinator = new Coordinator('B');
$coordinator->register(new Command('Register to Coordinator B'));
```

Commands can be registered to Coordinators through the flow of the current context. Coordinators are stored in a static property within the Coordinator class itself, so they are *reset* every time the PHP script gets executed.

## Command

``` php
use Ruigweb\Commander\Command;

$command = new Command('name', new Argv, function(Argv $argv) {}, 'description');

// Helper
$command = command('name', new Argv, function(Argv $argv) {}, 'description');
```

A `Command` is basically a way to define how Commander should be dealing with user input. Allowing users to execute handlers based on the registered commands. A `Command` only has one required argument, its *name*. However its worth mentioning that it is not possible to execute a command in case there is not handler defined.
The optional descritpion will be use to provide additional help information on `--help` for the `Command`.

Handlers are executed with the help of the `Closure::fromCallable` function. The argument provided to the handler is a instance of `Argv` with a the user input parsed and available through this instance.

## (Positional / Optional) Argument

For every Command its possible to define which positional and optional arguments can be provided when executing the Command. Obviously it is important to provide the positional argument in the order in which they should be provided.
For each argument you can provide the name, type and default value.
The type will define how the provided value for the argument should be parsed. When a default value is provided, it will be used to that when the argument was not provided.

```php
$argument = new Argument('positional', 'int', 0);

$optional = new Option('optional', 'string', 'Awesome');

```

Optional arguments can be provided by prefixing the name with `--`. For boolean type optional arguments, it will also automatically create a abbreviation variant which can be used.

```php
$command = new Command('be', new Argv(
    new Option('awesome', 'bool', true)
));

// Register the command to a Coordinator
```

```bash
$ vendor/bin/instruct be --awesome
// or
$ vendor/bin/instruct be -a
```

You can prevent usage of abbreviation on a Command by using the `short` method.

```php
$command = new Command('be', new Argv(
    (new Option('awesome', 'bool', true))->short(false)
));

```


## Argv
The `Argv` class is used to define and provide which (optional) arguments can be used for a Command. This class is immutable after initition. It will be used to parse the provide input for a Command and provided to the handler of the Command.
It is important to define the positional arguments in the order they should be provided.
To read the parsed values provided when executed a Command, the (positional) arguments in the provided`Argv` instance will all contain a (default) value for each argument.

```php
$command = new Command('be', new Argv(
    (new Option('awesome', 'bool', true))
), function(Argv $argv) {
    return 'You are '.$argv['awesome'] === true ? 'awesome' : 'a bit boring';
});
```

When a user provides non existing optional arguments, or to many positional arguments, Commander will not / incorrectly parse those.
The current implementation does not provide any feedback on incorrectly used / provided arguments.

## Subcommands

Sometimes its really nice to structure your CLI commands in a way where it makes sense to use subcommands. For example having different command to manage a *User* model.

```bash
$ vendor/bin/instruct user create user51212@internet.com
```

```bash
$ vendor/bin/instruct user update user51212@internet.com user51212_udpated@internet.com
```

In the two example aboven, we call two commands to first create a user and the second one updates a user. For simplicity the only data we *manage* here for the user is the emailaddress

To create and register subcommands like these on the Coordinator, all you need to do is providing the (sub)command as the first argument(s) for the provided `Argv` instance.
Say what? 🤔

```php
$userCommands = new Command('user', new Argv(
    new Command('create', 
        new Argv(
            new Argument('email')
        ),
        function(Argv $argv) {
            // Create a new user based on provided email address
        }
    ),
    new Command('update', 
        new Argv(
            new Argument('email'),
            new Argument('new_email')
        ),
        function(Argv $argv) {
            // Update a existing user based on provided email and new email address
        }
    ),
));
```

Subcommands can be created recursively, the sky is the limit ✨ 

## Single command

Wait, what? We already discussed how commands work right? Yes, but what if your application is just one single CLI command?
Well, there's an idea!
In that case you simply provide your `Command` (or any callable) as a *resolvible* to your `Coordinator`.

```php
$coordinator = (new Coordinator)->resolve(
    function(Argv $argv) {
        echo 'This is awesome!';
    }, 
    new Argv(
        new Option('reboot', 'bool')
    )
);
```

This *single* `Command` can now be used as follows

```bash
$ vendor/bin/instruct --reboot
```

Just be aware its one or the other, or a single command / resolver to run or registering Commands 🤷‍♂️

## Helpers

To make your live a bit easier, some helpers are present to quickly create your Commands for example. All available helpers are simple *shortcuts* to their corresponding classes. The following helpers are available: `coordinator`, `command`, `argument`, `option`.
