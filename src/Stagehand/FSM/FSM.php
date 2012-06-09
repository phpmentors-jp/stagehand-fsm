<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Copyright (c) 2006-2008, 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2008, 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
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
 * @copyright  2006-2008, 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
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
     * @var \Stagehand\FSM\IState
     */
    protected $currentState;

    /**
     * @var \Stagehand\FSM\IState
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
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->setName($name);
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
     * @return \Stagehand\FSM\IState
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * Gets the previous state.
     *
     * @return \Stagehand\FSM\IState
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
     * @param string $eventName
     * @return \Stagehand\FSM\IState
     */
    public function triggerEvent($eventName)
    {
        $this->queueEvent($eventName);
        while (true) {
            if (!count($this->eventQueue)) {
                return $this->currentState;
            }
            $this->processEvent(array_shift($this->eventQueue));
        }
    }

    /**
     * Queues an event to the event queue.
     *
     * @param string $eventName
     * @since Method available since Release 1.7.0
     */
    public function queueEvent($eventName)
    {
        $this->eventQueue[] = $eventName;
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
        if (array_key_exists($stateName, $this->states)) {
            return $this->states[$stateName];
        } else {
            foreach ($this->states as $state) {
                if ($state instanceof FSM) {
                    if (!is_null($state->getState($stateName))) {
                        return $state;
                    }
                }
            }
        }
    }

    /**
     * Adds the state with the given name.
     *
     * @param \Stagehand\FSM\IState $state
     */
    public function addState(IState $state)
    {
        $this->states[ $state->getName() ] = $state;
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
        return $state == IState::STATE_INITIAL || $state == IState::STATE_FINAL;
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
            $state = new State($stateName);
            $this->addState($state);
        }
        $this->currentState = $state;
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
        $this->currentState = $this->getState(IState::STATE_INITIAL);
        if (is_null($this->currentState)) {
            $state = new State(IState::STATE_INITIAL);
            $this->addState($state);
            $this->currentState = $state;
        }
    }

    /**
     * Processes an event.
     *
     * @param string  $eventName
     * @param boolean $usesHistoryMarker
     * @return \Stagehand\FSM\IState
     * @throws \Stagehand\FSM\FSMAlreadyShutdownException
     * @since Method available since Release 1.7.0
     */
    protected function processEvent($eventName, $usesHistoryMarker = false)
    {
        if ($this->currentState->getName() == IState::STATE_FINAL && !$this->isSpecialEvent($eventName)) {
            throw new FSMAlreadyShutdownException('The FSM was already shutdown.');
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
            $this->processEvent(Event::EVENT_EXIT, $usesHistoryMarker);
        }

        if (!$this->isSpecialEvent($eventName)) {
            $nextStateName = $event->getNextState();
            $this->transition($nextStateName);
        }

        $event->invokeAction($this);

        if ($this->isEntryEvent($eventName) && $this->currentState instanceof FSM && !$usesHistoryMarker) {
            $this->currentState->start();
        }

        if (!$this->isSpecialEvent($eventName)) {
            $this->processEvent(Event::EVENT_ENTRY, $event->usesHistoryMarker());
        }

        if (!$this->isSpecialEvent($eventName)) {
            $this->processEvent(Event::EVENT_DO, $event->usesHistoryMarker());
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
