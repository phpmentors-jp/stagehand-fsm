<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
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
 * @copyright  2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
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
     * @param string $stateMachineID
     */
    public function __construct($stateMachineID = null)
    {
        $this->stateMachine = new StateMachine($stateMachineID);
    }

    /**
     * @return \Stagehand\FSM\StateMachine\StateMachine
     */
    public function getStateMachine()
    {
        return $this->stateMachine;
    }

    /**
     * Sets the given state as the first state.
     *
     * @param string $stateID
     * @param callback $action
     * @param callback $guard
     */
    public function setStartState($stateID, $action = null, $guard = null)
    {
        if (is_null($this->stateMachine->getState(StateInterface::STATE_INITIAL))) {
            $transitionEvent = new TransitionEvent(EventInterface::EVENT_START);
            $this->stateMachine->addState(new InitialState($transitionEvent));
        }

        $this->addTransition(StateInterface::STATE_INITIAL, EventInterface::EVENT_START, $stateID, $action, $guard);
    }

    /**
     * Sets the given state as an end state.
     *
     * @param string $stateID
     * @param string $eventID
     * @param callback $action
     * @param callback $guard
     */
    public function setEndState($stateID, $eventID, $action = null, $guard = null)
    {
        if (is_null($this->stateMachine->getState(StateInterface::STATE_FINAL))) {
            $this->stateMachine->addState(new FinalState());
        }

        $this->addTransition($stateID, $eventID, StateInterface::STATE_FINAL, $action, $guard);
    }

    /**
     * Sets the activity to the state.
     *
     * @param string   $stateID
     * @param callback $activity
     * @throws Stagehand\FSM\StateMachine\ActionNotCallableException
     * @throws Stagehand\FSM\StateMachine\EventNotFoundException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function setActivity($stateID, $activity)
    {
        $state = $this->stateMachine->getState($stateID);
        if (is_null($state)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found in the state machine "%s".', $stateID, $this->stateMachine->getStateMachineID()));
        }

        $event = $state->getEvent(EventInterface::EVENT_DO);
        if (is_null($event)) {
            throw new EventNotFoundException(sprintf('The event "%s" is not found in the state "%s".', EventInterface::EVENT_DO, $stateID));
        }

        if (!is_null($activity)) {
            if (is_callable($activity)) {
                $event->setAction($activity);
            } else {
                throw new ActionNotCallableException(sprintf('The activity for the event "%s" in the state "%s" is not callable.', EventInterface::EVENT_DO, $stateID));
            }
        }
    }

    /**
     * Adds the state with the given ID.
     *
     * @param string $stateID
     */
    public function addState($stateID)
    {
        $state = new State($stateID);
        $state->addEvent(new EntryEvent());
        $state->addEvent(new ExitEvent());
        $state->addEvent(new DoEvent());
        $this->stateMachine->addState($state);
    }

    /**
     * Adds the state transition.
     *
     * @param string   $stateID
     * @param string   $eventID
     * @param string   $nextStateID
     * @param callback $action
     * @param callback $guard
     * @param boolean  $historyMarker
     * @throws Stagehand\FSM\StateMachine\ActionNotCallableException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function addTransition(
        $stateID,
        $eventID,
        $nextStateID,
        $action = null,
        $guard = null)
    {
        $state = $this->stateMachine->getState($stateID);
        if (is_null($state)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found.', $stateID));
        }

        $event = $state->getEvent($eventID);
        if (is_null($event)) {
            $event = new TransitionEvent($eventID);
            $state->addEvent($event);
        }

        $nextState = $this->stateMachine->getState($nextStateID);
        if (is_null($nextState)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found.', $nextStateID));
        }

        $event->setNextState($nextState);

        if (!is_null($action)) {
            if (is_callable($action)) {
                $event->setAction($action);
            } else {
                throw new ActionNotCallableException(sprintf('The action for the event "%s" in the state "%s" is not callable.', $eventID, $stateID));
            }
        }

        if (!is_null($guard)) {
            if (is_callable($guard)) {
                $event->setGuard($guard);
            } else {
                throw new ActionNotCallableException(sprintf('The guard for the event "%s" in the state "%s" is not callable.', $eventID, $stateID));
            }
        }
    }

    /**
     * Sets the entry action to the state.
     *
     * @param string   $stateID
     * @param callback $action
     * @throws Stagehand\FSM\StateMachine\ActionNotCallableException
     * @throws Stagehand\FSM\StateMachine\EventNotFoundException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function setEntryAction($stateID, $action)
    {
        $state = $this->stateMachine->getState($stateID);
        if (is_null($state)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found in the state machine "%s".', $stateID, $this->stateMachine->getStateMachineID()));
        }

        $event = $state->getEvent(EventInterface::EVENT_ENTRY);
        if (is_null($event)) {
            throw new EventNotFoundException(sprintf('The event "%s" is not found in the state "%s".', EventInterface::EVENT_ENTRY, $stateID));
        }

        if (!is_null($action)) {
            if (is_callable($action)) {
                $event->setAction($action);
            } else {
                throw new ActionNotCallableException(sprintf('The action for the event "%s" in the state "%s" is not callable.', EventInterface::EVENT_ENTRY, $stateID));
            }
        }
    }

    /**
     * Sets the exit action to the state.
     *
     * @param string   $stateID
     * @param callback $action
     * @throws Stagehand\FSM\StateMachine\ActionNotCallableException
     * @throws Stagehand\FSM\StateMachine\EventNotFoundException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function setExitAction($stateID, $action)
    {
        $state = $this->stateMachine->getState($stateID);
        if (is_null($state)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found in the state machine "%s".', $stateID, $this->stateMachine->getStateMachineID()));
        }

        $event = $state->getEvent(EventInterface::EVENT_EXIT);
        if (is_null($event)) {
            throw new EventNotFoundException(sprintf('The event "%s" is not found in the state "%s".', EventInterface::EVENT_EXIT, $stateID));
        }

        if (!is_null($action)) {
            if (is_callable($action)) {
                $event->setAction($action);
            } else {
                throw new ActionNotCallableException(sprintf('The action for the event "%s" in the state "%s" is not callable.', EventInterface::EVENT_EXIT, $stateID));
            }
        }
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
