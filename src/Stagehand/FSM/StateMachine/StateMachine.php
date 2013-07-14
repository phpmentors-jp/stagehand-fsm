<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
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

namespace Stagehand\FSM\StateMachine;

use Stagehand\FSM\Event\Event;
use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\State\State;
use Stagehand\FSM\State\StateInterface;

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
 * @copyright  2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
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
class StateMachine
{
    /**
     * @var string
     */
    protected $currentStateID;

    /**
     * @var string
     */
    protected $previousStateID;

    /**
     * @var array
     */
    protected $states = array();

    /**
     * @var string
     */
    protected $stateMachineID;

    /**
     * @var mixed
     */
    protected $payload;

    /**
     * @var array
     */
    protected $eventQueue = array();

    /**
     * @param string $stateMachineID
     */
    public function __construct($stateMachineID = null)
    {
        $this->stateMachineID = $stateMachineID;
        $this->addState(new State(StateInterface::STATE_INITIAL));
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array(
            'currentStateID',
            'previousStateID',
            'states',
            'stateMachineID',
            'eventQueue',
        );
    }

    /**
     * Starts the Finite State Machine.
     */
    public function start()
    {
        $this->initialize();
        $this->triggerEvent(EventInterface::EVENT_START);
    }

    /**
     * Gets the current state.
     *
     * @return \Stagehand\FSM\State\StateInterface
     */
    public function getCurrentState()
    {
        return $this->getState($this->currentStateID);
    }

    /**
     * Gets the previous state.
     *
     * @return \Stagehand\FSM\State\StateInterface
     */
    public function getPreviousState()
    {
        return $this->getState($this->previousStateID);
    }

    /**
     * Gets the payload.
     *
     * @return mixed $payload
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Triggers the given event in the current state.
     * <i>Note: Do not call this method directly from actions.</i>
     *
     * @param  string                                     $eventID
     * @return \Stagehand\FSM\State\StateInterface
     * @throws \Stagehand\FSM\StateMachine\StateMachineAlreadyShutdownException
     */
    public function triggerEvent($eventID)
    {
        $this->queueEvent($eventID);

        while (true) {
            if (count($this->eventQueue) == 0) {
                return $this->getCurrentState();
            } else {
                if ($this->currentStateID == StateInterface::STATE_FINAL && !Event::isSpecialEvent($eventID)) {
                    throw new StateMachineAlreadyShutdownException('The state machine was already shutdown.');
                }

                $event = $this->getCurrentState()->getEvent(array_shift($this->eventQueue));
                if (!is_null($event) && (is_null($event->getGuard()) || $this->evaluateGuard($event))) {
                    $this->transition($event);
                }

                $doEvent = $this->getCurrentState()->getEvent(EventInterface::EVENT_DO);
                if (!is_null($doEvent) && !is_null($doEvent->getAction())) {
                    $this->invokeAction($doEvent);
                }

                continue;
            }
        }
    }

    /**
     * Queues an event to the event queue.
     *
     * @param string $eventID
     * @since Method available since Release 1.7.0
     */
    public function queueEvent($eventID)
    {
        $this->eventQueue[] = $eventID;
    }

    /**
     * Finds and returns the state with the given ID. This method finds the
     * state recursively if child FSMs exists.
     *
     * @param  string                        $stateID
     * @return \Stagehand\FSM\State\StateInterface
     */
    public function getState($stateID)
    {
        if (array_key_exists($stateID, $this->states)) {
            return $this->states[$stateID];
        } else {
            return null;
        }
    }

    /**
     * Adds the state with the given ID.
     *
     * @param \Stagehand\FSM\State\StateInterface $state
     */
    public function addState(StateInterface $state)
    {
        $this->states[ $state->getStateID() ] = $state;
    }

    /**
     * Gets the ID of the state machine.
     *
     * @return string
     */
    public function getStateMachineID()
    {
        return $this->stateMachineID;
    }

    /**
     * Sets the given payload.
     *
     * @param mixed $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
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
     * Transitions to the next state.
     *
     * @param  \Stagehand\FSM\Event\EventInterface                  $event
     * @throws \Stagehand\FSM\StateNotFoundException
     */
    protected function transition(EventInterface $event)
    {
        $exitEvent = $this->getCurrentState()->getEvent(EventInterface::EVENT_EXIT);
        if (!is_null($exitEvent->getAction())) {
            $this->invokeAction($exitEvent);
        }

        if (!is_null($event->getAction())) {
            $this->invokeAction($event);
        }

        $this->previousStateID = $this->currentStateID;
        $this->currentStateID = $event->getNextState()->getStateID();

        $entryEvent = $this->getCurrentState()->getEvent(EventInterface::EVENT_ENTRY);
        if (!is_null($entryEvent->getAction())) {
            $this->invokeAction($entryEvent);
        }
    }

    /**
     * Initializes the state machine.
     */
    protected function initialize()
    {
        $this->currentStateID = StateInterface::STATE_INITIAL;
    }

    /**
     * Evaluates the guard for an event.
     *
     * @param \Stagehand\FSM\Event\EventInterface $event
     * @return boolean
     * @since Method available since Release 2.0.0
     */
    protected function evaluateGuard(EventInterface $event)
    {
        return call_user_func($event->getGuard(), $event, $this->getPayload(), $this);
    }

    /**
     * Invokes the action for an event.
     *
     * @param \Stagehand\FSM\Event\EventInterface $event
     * @since Method available since Release 2.0.0
     */
    protected function invokeAction(EventInterface $event)
    {
        call_user_func($event->getAction(), $event, $this->getPayload(), $this);
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
