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

use Stagehand\FSM\State\State;
use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\Event\Event;

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
     */
    public function setStartState($stateID)
    {
        $this->addTransition(StateInterface::STATE_INITIAL, Event::EVENT_START, $stateID);
    }

    /**
     * Sets the activity to the state.
     *
     * @param string   $stateID
     * @param callback $activity
     * @throws Stagehand\FSM\StateMachine\EventNotFoundException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function setActivity($stateID, $activity)
    {
        $state = $this->stateMachine->getState($stateID);
        if (is_null($state)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found in the state machine "%s".', $stateID, $this->stateMachine->getStateMachineID()));
        }

        $event = $state->getEvent(Event::EVENT_DO);
        if (is_null($event)) {
            throw new EventNotFoundException(sprintf('The event "%s" is not found in the state "%s".', Event::EVENT_DO, $stateID));
        }

        $event->setAction($activity);
    }

    /**
     * Adds the state with the given ID.
     *
     * @param string $stateID
     */
    public function addState($stateID)
    {
        $this->stateMachine->addState(new State($stateID));
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
            $event = new Event($eventID);
            $state->addEvent($event);
        }

        $nextState = $this->stateMachine->getState($nextStateID);
        if (is_null($nextState)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found.', $nextStateID));
        }

        $event->setNextState($nextState);
        $event->setAction($action);
        $event->setGuard($guard);
    }

    /**
     * Sets the entry action to the state.
     *
     * @param string   $stateID
     * @param callback $action
     * @throws Stagehand\FSM\StateMachine\EventNotFoundException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function setEntryAction($stateID, $action)
    {
        $state = $this->stateMachine->getState($stateID);
        if (is_null($state)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found in the state machine "%s".', $stateID, $this->stateMachine->getStateMachineID()));
        }

        $event = $state->getEvent(Event::EVENT_ENTRY);
        if (is_null($event)) {
            throw new EventNotFoundException(sprintf('The event "%s" is not found in the state "%s".', Event::EVENT_ENTRY, $stateID));
        }

        $event->setAction($action);
    }

    /**
     * Sets the exit action to the state.
     *
     * @param string   $stateID
     * @param callback $action
     * @throws Stagehand\FSM\StateMachine\EventNotFoundException
     * @throws Stagehand\FSM\StateMachine\StateNotFoundException
     */
    public function setExitAction($stateID, $action)
    {
        $state = $this->stateMachine->getState($stateID);
        if (is_null($state)) {
            throw new StateNotFoundException(sprintf('The state "%s" is not found in the state machine "%s".', $stateID, $this->stateMachine->getStateMachineID()));
        }

        $event = $state->getEvent(Event::EVENT_EXIT);
        if (is_null($event)) {
            throw new EventNotFoundException(sprintf('The event "%s" is not found in the state "%s".', Event::EVENT_EXIT, $stateID));
        }

        $event->setAction($action);
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
