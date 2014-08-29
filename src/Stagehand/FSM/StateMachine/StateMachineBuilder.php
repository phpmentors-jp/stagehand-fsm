<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011-2014 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_FSM
 * @copyright  2011-2014 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://opensource.org/licenses/BSD-2-Clause  The BSD 2-Clause License
 * @version    Release: @package_version@
 * @since      File available since Release 2.0.0
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
 * @package    Stagehand_FSM
 * @copyright  2011-2014 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://opensource.org/licenses/BSD-2-Clause  The BSD 2-Clause License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.0.0
 */
class StateMachineBuilder
{
    /**
     * @var \Stagehand\FSM\StateMachine\StateMachine
     */
    protected $stateMachine;

    /**
     * @param string|\Stagehand\FSM\StateMachine\StateMachine $stateMachineId
     */
    public function __construct($stateMachineId = null)
    {
        if ($stateMachineId instanceof StateMachine) {
            $this->stateMachine = $stateMachineId;
        } else {
            $this->stateMachine = new StateMachine($stateMachineId);
        }
    }

    /**
     * @return \Stagehand\FSM\StateMachine\StateMachine
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
        if (is_null($state)) {
            $state = new InitialState();
            $this->stateMachine->addState($state);
        }

        $event = $state->getEvent(EventInterface::EVENT_START);
        if (is_null($event)) {
            $state->setTransitionEvent(new TransitionEvent(EventInterface::EVENT_START));
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
        if (is_null($this->stateMachine->getState(StateInterface::STATE_FINAL))) {
            $this->stateMachine->addState(new FinalState());
        }

        $this->addTransition($stateId, $eventId, StateInterface::STATE_FINAL, $action, $guard);
    }

    /**
     * Sets the activity to the state.
     *
     * @param  string                                                $stateId
     * @param  callback                                              $activity
     * @throws Stagehand\FSM\StateMachine\ActionNotCallableException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function setActivity($stateId, $activity)
    {
        $state = $this->stateMachine->getState($stateId);
        if (is_null($state)) {
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
     * @param  string                                                $stateId
     * @param  string                                                $eventId
     * @param  string                                                $nextStateId
     * @param  callback                                              $action
     * @param  callback                                              $guard
     * @throws Stagehand\FSM\StateMachine\ActionNotCallableException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function addTransition(
        $stateId,
        $eventId,
        $nextStateId,
        $action = null,
        $guard = null)
    {
        $state = $this->stateMachine->getState($stateId);
        if (is_null($state)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found.', $stateId));
        }

        $event = $state->getEvent($eventId);
        if (is_null($event)) {
            $event = new TransitionEvent($eventId);
            $state->addTransitionEvent($event);
        }

        $nextState = $this->stateMachine->getState($nextStateId);
        if (is_null($nextState)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found.', $nextStateId));
        }

        $event->setNextState($nextState);

        if (!is_null($action)) {
            if (is_callable($action)) {
                $event->setAction($action);
            } else {
                throw new ActionNotCallableException(sprintf('The action for the event "%s" in the state "%s" is not callable.', $eventId, $stateId));
            }
        }

        if (!is_null($guard)) {
            if (is_callable($guard)) {
                $event->setGuard($guard);
            } else {
                throw new ActionNotCallableException(sprintf('The guard for the event "%s" in the state "%s" is not callable.', $eventId, $stateId));
            }
        }
    }

    /**
     * Sets the entry action to the state.
     *
     * @param  string                                                $stateId
     * @param  callback                                              $action
     * @throws Stagehand\FSM\StateMachine\ActionNotCallableException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function setEntryAction($stateId, $action)
    {
        $state = $this->stateMachine->getState($stateId);
        if (is_null($state)) {
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
     * @param  string                                                $stateId
     * @param  callback                                              $action
     * @throws Stagehand\FSM\StateMachine\ActionNotCallableException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function setExitAction($stateId, $action)
    {
        $state = $this->stateMachine->getState($stateId);
        if (is_null($state)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found in the state machine "%s".', $stateId, $this->stateMachine->getStateMachineId()));
        }

        if (!is_callable($action)) {
            throw new ActionNotCallableException(sprintf('The action for the event "%s" in the state "%s" is not callable.', EventInterface::EVENT_EXIT, $stateId));
        }

        $state->getEvent(EventInterface::EVENT_EXIT)->setAction($action);
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
