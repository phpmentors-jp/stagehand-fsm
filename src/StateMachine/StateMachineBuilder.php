<?php
/*
 * Copyright (c) KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\StateMachine;

use Stagehand\FSM\Event\DoEvent;
use Stagehand\FSM\Event\EntryEvent;
use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\Event\ExitEvent;
use Stagehand\FSM\Event\TransitionEvent;
use Stagehand\FSM\State\FinalState;
use Stagehand\FSM\State\InitialState;
use Stagehand\FSM\State\State;
use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\Transition\Transition;

/**
 * @since Class available since Release 2.0.0
 */
class StateMachineBuilder
{
    /**
     * @var StateMachine
     */
    private $stateMachine;

    /**
     * @param string|StateMachine $stateMachineId
     */
    public function __construct($stateMachineId = null)
    {
        if ($stateMachineId instanceof StateMachineInterface) {
            $this->stateMachine = $stateMachineId;
        } else {
            $this->stateMachine = new StateMachine($stateMachineId);
        }
    }

    /**
     * @return StateMachine
     */
    public function getStateMachine()
    {
        return $this->stateMachine;
    }

    /**
     * Sets the given state as the start state of the state machine.
     *
     * @param string $stateId
     */
    public function setStartState($stateId)
    {
        $state = $this->stateMachine->getState(StateInterface::STATE_INITIAL);
        if ($state === null) {
            $state = new InitialState();
            $this->stateMachine->addState($state);
        }

        $this->addTransition(StateInterface::STATE_INITIAL, EventInterface::EVENT_START, $stateId);
    }

    /**
     * Sets the given state as an end state of the state machine.
     *
     * @param string $stateId
     * @param string $eventId
     */
    public function setEndState($stateId, $eventId)
    {
        if ($this->stateMachine->getState(StateInterface::STATE_FINAL) === null) {
            $this->stateMachine->addState(new FinalState());
        }

        $this->addTransition($stateId, $eventId, StateInterface::STATE_FINAL);
    }

    /**
     * Adds a state to the state machine.
     *
     * @param string $stateId
     */
    public function addState($stateId)
    {
        $state = new State($stateId);
        $state->setEntryEvent(new EntryEvent());
        $state->setExitEvent(new ExitEvent());
        $state->setDoEvent(new DoEvent());
        $this->stateMachine->addState($state);
    }

    /**
     * Adds an state transition to the state machine.
     *
     * @param string $stateId
     * @param string $eventId
     * @param string $nextStateId
     *
     * @throws StateNotFoundException
     */
    public function addTransition($stateId, $eventId, $nextStateId)
    {
        $state = $this->stateMachine->getState($stateId);
        if ($state === null) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found.', $stateId));
        }

        $event = $state->getEvent($eventId);
        if ($event === null) {
            $event = new TransitionEvent($eventId);
        }

        $nextState = $this->stateMachine->getState($nextStateId);
        if ($nextState === null) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found.', $nextStateId));
        }

        $this->stateMachine->addTransition(new Transition($nextState, $state, $event));
    }
}
