# Stagehand_FSM

A finite state machine

[![Total Downloads](https://poser.pugx.org/piece/stagehand-fsm/downloads.png)](https://packagist.org/packages/piece/stagehand-fsm)
[![Latest Stable Version](https://poser.pugx.org/piece/stagehand-fsm/v/stable.png)](https://packagist.org/packages/piece/stagehand-fsm)
[![Latest Unstable Version](https://poser.pugx.org/piece/stagehand-fsm/v/unstable.png)](https://packagist.org/packages/piece/stagehand-fsm)
[![Build Status](https://travis-ci.org/piece/stagehand-fsm.svg?branch=2.4)](https://travis-ci.org/piece/stagehand-fsm)

`Stagehand_FSM` is a [finite state machine](https://en.wikipedia.org/wiki/Finite-state_machine).

Manual state management makes code complex, decreases intentionality. By using `Stagehand_FSM`, state management code can be declaratively represented in the form of FSM. This makes code simpler, increases intentionality.

`Stagehand_FSM` can be used as an infrastructure for [domain-specific languages](http://en.wikipedia.org/wiki/Domain-specific_language) (DSLs). Examples are workflow engines such as [Workflower](https://github.com/phpmentors-jp/workflower), pageflow engines such as [PHPMentorsPageflowerBundle](https://github.com/phpmentors-jp/pageflower-bundle).

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

* Activities (do actions)
* Entry actions
* Exit actions
* Transition actions
* Transition logging
* Guards
* Initial pseudo state
* Final state
* User-defined payload
* User-defined event dispatcher for the state machine events

## Installation

`Stagehand_FSM` can be installed using [Composer](http://getcomposer.org/).

Add the dependency to `piece/stagehand-fsm` into your `composer.json` file as the following:

```
composer require piece/stagehand-fsm "2.4.*"
```

## Support

If you find a bug or have a question, or want to request a feature, create an issue or pull request for it on [Issues](https://github.com/piece/stagehand-fsm/issues).

## Copyright

Copyright (c) 2006-2008, 2011-2015 KUBO Atsuhiro, All rights reserved.

## License

[The BSD 2-Clause License](http://opensource.org/licenses/BSD-2-Clause)
