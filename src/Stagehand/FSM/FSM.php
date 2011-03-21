<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Copyright (c) 2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://en.wikipedia.org/wiki/Finite_state_machine
 * @link       http://www.isd.mel.nist.gov/projects/omacapi/Software/FiniteStateMachine/doc/FSMExample.html
 * @link       http://www.isd.mel.nist.gov/projects/omacapi/Software/FiniteStateMachine/doc/
 * @link       http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @link       http://pear.php.net/package/FSM
 * @link       http://www.microsoft.com/japan/msdn/net/aspnet/aspnet-finitestatemachines.asp
 * @link       http://www.generation5.org/content/2003/FSM_Tutorial.asp
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\FSM;

/**
 * A Finite State Machine.
 *
 * Stagehand_FSM provides a self configuring Finite State Machine(FSM).
 * The following is a list of features of Stagehand_FSM.
 * o Transition action
 * o Entry and Exit state actions
 * o Initial and Final pseudo states
 * o Nested FSM
 * o History Marker
 * o Activity
 * o User defined payload
 *
 * @package    Stagehand_FSM
 * @copyright  2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://en.wikipedia.org/wiki/Finite_state_machine
 * @link       http://www.isd.mel.nist.gov/projects/omacapi/Software/FiniteStateMachine/doc/FSMExample.html
 * @link       http://www.isd.mel.nist.gov/projects/omacapi/Software/FiniteStateMachine/doc/
 * @link       http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @link       http://pear.php.net/package/FSM
 * @link       http://www.microsoft.com/japan/msdn/net/aspnet/aspnet-finitestatemachines.asp
 * @link       http://www.generation5.org/content/2003/FSM_Tutorial.asp
 * @since      Class available since Release 0.1.0
 */
class FSM
{
    /**
     * @var \Stagehand\FSM\State
     */
    protected $currentState;

    /**
     * @var \Stagehand\FSM\State
     */
    protected $previousState;

    /**
     * @var array
     */
    protected $states = array();

    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $payload;

    /**
     * @var array
     */
    protected $eventQueue = array();

    /**
     * Constructor
     *
     * @param string $state
     */
    public function __construct($state = null)
    {
        if (!is_null($state)) {
            $this->setFirstState($state);
        }
    }

    /**
     * Sets the given state as the first state.
     *
     * @param string $state
     */
    public function setFirstState($state)
    {
        $this->addTransition(State::STATE_INITIAL, Event::EVENT_START, $state);
    }

    /**
     * Starts the Finite State Machine.
     */
    public function start()
    {
        $this->initialize();
        $this->triggerEvent(Event::EVENT_START);
    }

    /**
     * Gets the current state.
     *
     * @return \Stagehand\FSM\State
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * Gets the previous state.
     *
     * @return \Stagehand\FSM\State
     */
    public function getPreviousState()
    {
        return $this->previousState;
    }

    /**
     * Gets the payload.
     *
     * @return mixed $payload
     */
    public function &getPayload()
    {
        return $this->payload;
    }

    /**
     * Triggers the given event in the current state.
     * <i>Note: Do not call this method directly from actions.</i>
     *
     * @param string  $eventName
     * @param boolean $transitionToHistoryMarker
     * @return \Stagehand\FSM\State
     */
    public function triggerEvent($eventName, $transitionToHistoryMarker = false)
    {
        $this->queueEvent($eventName, $transitionToHistoryMarker);
        while (true) {
            if (!count($this->eventQueue)) {
                return $this->currentState;
            }

            $event = array_shift($this->eventQueue);
            $this->processEvent($event['event'], $event['transitionToHistoryMarker']);
        }
    }

    /**
     * Queues an event to the event queue.
     *
     * @param string  $eventName
     * @param boolean $transitionToHistoryMarker
     * @since Method available since Release 1.7.0
     */
    public function queueEvent($eventName, $transitionToHistoryMarker = false)
    {
        $this->eventQueue[] = array('event' => $eventName, 'transitionToHistoryMarker' => $transitionToHistoryMarker);
    }

    /**
     * Adds the state transition.
     *
     * @param string   $stateName
     * @param string   $eventName
     * @param string   $nextStateName
     * @param callback $action
     * @param callback $guard
     * @param boolean  $transitionToHistoryMarker
     */
    public function addTransition(
        $stateName,
        $eventName,
        $nextStateName,
        $action = null,
        $guard = null,
        $transitionToHistoryMarker = false)
    {
        $state = $this->findState($stateName);
        if (is_null($state)) {
            $state = $this->addState($stateName);
        }

        $event = $state->getEvent($eventName);
        if (is_null($event)) {
            $event = $state->addEvent($eventName);
        }

        $event->setNextState($nextStateName);
        $event->setAction($action);
        $event->setGuard($guard);
        $event->setTransitionToHistoryMarker($transitionToHistoryMarker);
    }

    /**
     * Sets the exit action to the state.
     *
     * @param string   $state
     * @param callback $action
     */
    public function setExitAction($state, $action)
    {
        $this->addTransition($state, Event::EVENT_EXIT, null, $action);
    }

    /**
     * Sets the entry action to the state.
     *
     * @param string   $state
     * @param callback $action
     */
    public function setEntryAction($state, $action)
    {
        $this->addTransition($state, Event::EVENT_ENTRY, null, $action);
    }

    /**
     * Finds and returns the state with the given name. This method finds the
     * state recursively if child FSMs exists.
     *
     * @param string $stateName
     * @return mixed
     */
    public function getState($stateName)
    {
        $state = $this->findState($stateName);
        if (is_null($state)) {
            foreach ($this->states as $value) {
                if ($value instanceof FSM) {
                    if (!is_null($value->getState($stateName))) {
                        return $value;
                    }
                }
            }
        }

        return $state;
    }

    /**
     * Adds the state with the given name.
     *
     * @param string $state
     * @return \Stagehand\FSM\State
     */
    public function addState($state)
    {
        $this->states[$state] = new State($state);
        return $this->states[$state];
    }

    /**
     * Sets the name of the FSM.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the name of the FSM.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Adds a Stagehand_FSM object to the FSM.
     *
     * @param \Stagehand\FSM\FSM $fsm
     * @return \Stagehand\FSM\FSMState
     */
    public function addFSM(FSM $fsm)
    {
        if (is_null($fsm->getPayload())) {
            $fsm->setPayload($this->payload);
        }
        $name = $fsm->getName();
        $this->states[$name] = FSMState::wrap($fsm);
        return $this->states[$name];
    }

    /**
     * Sets the activity to the state.
     *
     * @param string   $state
     * @param callback $activity
     */
    public function setActivity($state, $activity)
    {
        $this->addTransition($state, EVENT::EVENT_DO, null, $activity);
    }

    /**
     * Sets the given payload.
     *
     * @param mixed &$payload
     */
    public function setPayload(&$payload)
    {
        $this->payload = &$payload;
    }

    /**
     * Returns whether an event is a protected event such as the special
     * events and so on.
     *
     * @param string $event
     * @return boolean
     * @since Method available since Release 1.5.0
     */
    public function isProtectedEvent($event)
    {
        return $this->isSpecialEvent($event) || $event == Event::EVENT_START || $event == Event::EVENT_END;
    }

    /**
     * Returns whether a state is a protected event such as the pseudo states
     * and so on.
     *
     * @param string $state
     * @return boolean
     * @since Method available since Release 1.5.0
     */
    public function isProtectedState($state)
    {
        return $state == State::STATE_INITIAL || $state == State::STATE_FINAL;
    }

    /**
     * Returns whether the current state has an event with a given name.
     *
     * @param string $name
     * @return boolean
     * @since Method available since Release 1.6.0
     */
    public function hasEvent($name)
    {
        return $this->currentState->hasEvent($name);
    }

    /**
     * Removes the payload from the property.
     *
     * @since Method available since Release 1.9.0
     */
    public function clearPayload()
    {
        unset($this->payload);
    }

    /**
     * Returns whether the event is special event or not.
     *
     * @param string $event
     * @return boolean
     */
    protected function isSpecialEvent($event)
    {
        return $event == Event::EVENT_ENTRY || $event == Event::EVENT_EXIT || $event == Event::EVENT_DO;
    }

    /**
     * Transitions to the next state.
     *
     * @param string $stateName
     */
    protected function transition($stateName)
    {
        $this->previousState = $this->currentState;
        $state = $this->getState($stateName);
        if (is_null($state)) {
            $state = $this->addState($stateName);
        }
        $this->currentState = $state;
    }

    /**
     * Finds and returns the state with the given name in the FSM.
     *
     * @param string $name
     * @return mixed
     */
    protected function findState($name)
    {
        if (!array_key_exists($name, $this->states)) return;
        return $this->states[$name];
    }

    /**
     * Returns whether the event is entry event or not.
     *
     * @param string $event
     * @return boolean
     */
    protected function isEntryEvent($event)
    {
        return $event == Event::EVENT_ENTRY;
    }

    /**
     * Initializes the FSM.
     */
    protected function initialize()
    {
        $this->currentState = $this->findState(State::STATE_INITIAL);
        if (is_null($this->currentState)) {
            $this->currentState = $this->addState(State::STATE_INITIAL);
        }
    }

    /**
     * Processes an event.
     *
     * @param string  $eventName
     * @param boolean $transitionToHistoryMarker
     * @return \Stagehand\FSM\State
     * @throws \Stagehand\FSM\AlreadyShutdownException
     * @since Method available since Release 1.7.0
     */
    protected function processEvent($eventName, $transitionToHistoryMarker = false)
    {
        if ($this->currentState->getName() == State::STATE_FINAL && !$this->isSpecialEvent($eventName)) {
            throw new AlreadyShutdownException('The FSM was already shutdown.');
        }

        $event = $this->currentState->getEvent($eventName);
        if (!is_null($event)) {
            if (!$this->isSpecialEvent($eventName)) {
                $result = $event->evaluateGuard($this);
                if (!$result) {
                    $eventName = Event::EVENT_DO;
                    $event = $this->currentState->getEvent(Event::EVENT_DO);
                }
            }
        } else {
            $eventName = Event::EVENT_DO;
            $event = $this->currentState->getEvent(Event::EVENT_DO);
        }

        if (!$this->isSpecialEvent($eventName)) {
            $this->processEvent(Event::EVENT_EXIT, $transitionToHistoryMarker);
        }

        if (!$this->isSpecialEvent($eventName)) {
            $nextStateName = $event->getNextState();
            $this->transition($nextStateName);
        }

        $event->invokeAction($this);

        if ($this->isEntryEvent($eventName) && $this->currentState instanceof FSM && !$transitionToHistoryMarker) {
            $this->currentState->start();
        }

        if (!$this->isSpecialEvent($eventName)) {
            $this->processEvent(Event::EVENT_ENTRY, $event->getTransitionToHistoryMarker());
        }

        if (!$this->isSpecialEvent($eventName)) {
            $this->processEvent(Event::EVENT_DO, $event->getTransitionToHistoryMarker());
        }

        return $this->currentState;
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
