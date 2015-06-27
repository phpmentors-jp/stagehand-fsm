<?php
namespace Stagehand\FSM\StateMachine\StateMachineTest;

use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\StateMachine\StateMachineBuilder;
use Stagehand\FSM\StateMachine\StateMachineInterface;

class RegistrationStateMachineBuilder
{
    /**
     * @return StateMachineInterface
     */
    public function build()
    {
        $stateMachineBuilder = new StateMachineBuilder('registration');
        $stateMachineBuilder->addState('input');
        $stateMachineBuilder->addState('confirmation');
        $stateMachineBuilder->addState('success');
        $stateMachineBuilder->setStartState('input');
        $stateMachineBuilder->addTransition('input', 'confirmation', 'confirmation');
        $stateMachineBuilder->addTransition('confirmation', 'success', 'success');
        $stateMachineBuilder->addTransition('confirmation', 'input', 'input');
        $stateMachineBuilder->setEndState('success', StateInterface::STATE_FINAL);

        return $stateMachineBuilder->getStateMachine();
    }
}
