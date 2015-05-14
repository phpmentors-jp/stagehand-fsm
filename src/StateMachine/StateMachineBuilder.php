<?php
/*
 * Copyright (c) 2011-2015 KUBO Atsuhiro <kubo@iteman.jp>,
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
     * @param string   $stateId
     * @param callback $action
     * @param callback $guard
     */
    public function setStartState($stateId, $action = null, $guard = null)
    {
        $state = $this->stateMachine->getState(StateInterface::STATE_INITIAL);
        if ($state === null) {
            $state = new InitialState();
            $this->stateMachine->addState($state);
        }

        $this->addTransition(StateInterface::STATE_INITIAL, EventInterface::EVENT_START, $stateId, $action, $guard);
    }

    /**
     * Sets the given state as an end state of the state machine.
     *
     * @param string   $stateId
     * @param string   $eventId
     * @param callback $action
     * @param callback $guard
     */
    public function setEndState($stateId, $eventId, $action = null, $guard = null)
    {
        if ($this->stateMachine->getState(StateInterface::STATE_FINAL) === null) {
            $this->stateMachine->addState(new FinalState());
        }

        $this->addTransition($stateId, $eventId, StateInterface::STATE_FINAL, $action, $guard);
    }

    /**
     * Sets the activity to the state.
     *
     * @param string   $stateId
     * @param callback $activity
     *
     * @throws ActionNotCallableException
     * @throws StateNotFoundException
     */
    public function setActivity($stateId, $activity)
    {
        $state = $this->stateMachine->getState($stateId);
        if ($state === null) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found in the state machine "%s".', $stateId, $this->stateMachine->getStateMachineId()));
        }

        if (!is_callable($activity)) {
            throw new ActionNotCallableException(sprintf('The activity for the state "%s" is not callable.', EventInterface::EVENT_DO, $stateId));
        }

        $state->getEvent(EventInterface::EVENT_DO)->setAction($activity);
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
     * @param string   $stateId
     * @param string   $eventId
     * @param string   $nextStateId
     * @param callback $action
     * @param callback $guard
     *
     * @throws ActionNotCallableException
     * @throws StateNotFoundException
     */
    public function addTransition(
        $stateId,
        $eventId,
        $nextStateId,
        $action = null,
        $guard = null)
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

        if ($action !== null) {
            if (!is_callable($action)) {
                throw new ActionNotCallableException(sprintf('The action for the event "%s" in the state "%s" is not callable.', $eventId, $stateId));
            }
        }

        if ($guard !== null) {
            if (!is_callable($guard)) {
                throw new ActionNotCallableException(sprintf('The guard for the event "%s" in the state "%s" is not callable.', $eventId, $stateId));
            }
        }

        $this->stateMachine->addTransition($state, $event, $nextState, $action, $guard);
    }

    /**
     * Sets the entry action to the state.
     *
     * @param string   $stateId
     * @param callback $action
     *
     * @throws ActionNotCallableException
     * @throws StateNotFoundException
     */
    public function setEntryAction($stateId, $action)
    {
        $state = $this->stateMachine->getState($stateId);
        if ($state === null) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found in the state machine "%s".', $stateId, $this->stateMachine->getStateMachineId()));
        }

        if (!is_callable($action)) {
            throw new ActionNotCallableException(sprintf('The action for the event "%s" in the state "%s" is not callable.', EventInterface::EVENT_ENTRY, $stateId));
        }

        $state->getEvent(EventInterface::EVENT_ENTRY)->setAction($action);
    }

    /**
     * Sets the exit action to the state.
     *
     * @param string   $stateId
     * @param callback $action
     *
     * @throws ActionNotCallableException
     * @throws StateNotFoundException
     */
    public function setExitAction($stateId, $action)
    {
        $state = $this->stateMachine->getState($stateId);
        if ($state === null) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found in the state machine "%s".', $stateId, $this->stateMachine->getStateMachineId()));
        }

        if (!is_callable($action)) {
            throw new ActionNotCallableException(sprintf('The action for the event "%s" in the state "%s" is not callable.', EventInterface::EVENT_EXIT, $stateId));
        }

        $state->getEvent(EventInterface::EVENT_EXIT)->setAction($action);
    }
}
