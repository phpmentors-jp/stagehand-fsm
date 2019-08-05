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

use Stagehand\FSM\Event\Event;
use Stagehand\FSM\State\FinalState;
use Stagehand\FSM\State\ForkState;
use Stagehand\FSM\State\InitialState;
use Stagehand\FSM\State\JoinState;
use Stagehand\FSM\State\State;
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
        $state = $this->stateMachine->getState(StateMachineInterface::STATE_INITIAL);
        if ($state === null) {
            $state = new InitialState(StateMachineInterface::STATE_INITIAL);
            $this->stateMachine->addState($state);
        }

        $this->addTransition(StateMachineInterface::STATE_INITIAL, $stateId, StateMachineInterface::EVENT_START);
    }

    /**
     * Sets the given state as an end state of the state machine.
     *
     * @param string $stateId
     * @param string $eventId
     */
    public function setEndState($stateId, $eventId)
    {
        if ($this->stateMachine->getState(StateMachineInterface::STATE_FINAL) === null) {
            $this->stateMachine->addState(new FinalState(StateMachineInterface::STATE_FINAL));
        }

        $this->addTransition($stateId, StateMachineInterface::STATE_FINAL, $eventId);
    }

    /**
     * Adds a state to the state machine.
     *
     * @param string $stateId
     */
    public function addState($stateId)
    {
        $state = new State($stateId);
        $this->stateMachine->addState($state);
    }

    /**
     * Adds an state transition to the state machine.
     *
     * @param string $stateId
     * @param string $nextStateId
     * @param string $eventId
     *
     * @throws StateNotFoundException
     */
    public function addTransition($stateId, $nextStateId, $eventId = null)
    {
        $state = $this->stateMachine->getState($stateId);
        if ($state === null) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found.', $stateId));
        }

        if ($state instanceof ForkState) {
            $eventId = StateMachineInterface::EVENT_FORK;
        }

        $nextState = $this->stateMachine->getState($nextStateId);
        if ($nextState === null) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found.', $nextStateId));
        }

        if ($nextState instanceof JoinState) {
            $eventId = StateMachineInterface::EVENT_JOIN;
        }

        $event = $state->getTransitionEvent($eventId);
        if ($event === null) {
            $event = new Event($eventId);
        }

        $this->stateMachine->addTransition(new Transition($nextState, $state, $event));
    }

    /**
     * @param string $stateId
     */
    public function addForkState($stateId)
    {
        $state = new ForkState($stateId);
        $this->stateMachine->addState($state);
    }

    /**
     * @param string $stateId
     */
    public function addJoinState($stateId)
    {
        $state = new JoinState($stateId);
        $this->stateMachine->addState($state);
    }

    /**
     * @param string $parentStateId
     * @param $stateMachineId
     * @param callable $callback
     */
    public function addChild($parentStateId, $stateMachineId, callable $callback)
    {
        $parentState = $this->stateMachine->getState($parentStateId);
        if ($parentState === null) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found.', $parentStateId));
        }

        $builder = new static($stateMachineId);
        call_user_func($callback, $builder);

        $parentState->addChild($builder->getStateMachine());
    }
}
