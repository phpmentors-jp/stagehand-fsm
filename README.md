# Stagehand_FSM - A finite state machine

Stagehand_FSM is a [finite state machine](https://en.wikipedia.org/wiki/Finite-state_machine).

Manual state management makes code complex, decreases intentionality. By using Stagehand_FSM, state management code can be declaratively represented in the form of FSM. This makes code simpler, increases intentionality.

Stagehand_FSM can be used as an infrastructure for [domain-specific languages](http://en.wikipedia.org/wiki/Domain-specific_language) (DSLs). Examples are workflow engines, page flow engines such as [Piece_Flow](https://github.com/piece/piece-flow).

```php
<?php
use Stagehand\FSM\StateMachine\StateMachineBuilder;

$stateMachineBuilder = new StateMachineBuilder();
$stateMachineBuilder->addState('locked');
$stateMachineBuilder->addState('unlocked');
$stateMachineBuilder->setStartState('locked');
$stateMachineBuilder->addTransition('locked', 'insertCoin', 'unlocked');
$stateMachineBuilder->addTransition('unlocked', 'pass', 'locked');
$stateMachine = $stateMachineBuilder->getStateMachine();

$stateMachine->start();
echo $stateMachine->getCurrentState()->getStateID() . PHP_EOL; // "locked"
$stateMachine->triggerEvent('insertCoin');
echo $stateMachine->getCurrentState()->getStateID() . PHP_EOL; // "unlocked"
$stateMachine->triggerEvent('pass');
echo $stateMachine->getCurrentState()->getStateID() . PHP_EOL; // "locked"
```

## Features

* Activities (Do Actions)
* Entry Actions
* Exit Actions
* Transition Actions
* Guards
* Initial Pseudo State
* Final State
* User-Defined Payload

## Installation

Stagehand_FSM can be installed using [Composer](http://getcomposer.org/) or [PEAR](http://pear.php.net/). The following sections explain how to install Stagehand_FSM.

### Composer

First, add the dependency to **piece/stagehand-fsm** into your **composer.json** file as the following:

```json
{
    "require": {
        "piece/stagehand-fsm": ">=2.0.0"
    }
}
```

Second, update your dependencies as the following:

```console
composer update piece/stagehand-fsm
```

### PEAR

```console
pear config-set auto_discover 1
pear install pear.piece-framework.com/Stagehand_FSM
```

## Support

If you find a bug or have a question, or want to request a feature, create an issue or pull request for it on GitHub.

## Copyright

Copyright (c) 2006-2008, 2011-2013 KUBO Atsuhiro &lt;kubo@iteman.jp&gt;, All rights reserved.

## License

[The BSD 2-Clause License](http://opensource.org/licenses/BSD-2-Clause)
