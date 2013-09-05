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
 * @license    http://opensource.org/licenses/BSD-2-Clause  The BSD 2-Clause License
 * @version    Release: @package_version@
 * @link       http://en.wikipedia.org/wiki/Finite_state_machine
 * @link       http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @link       http://pear.php.net/package/FSM
 * @link       http://www.generation5.org/content/2003/FSM_Tutorial.asp
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\FSM\StateMachine;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\Event\TransitionEventInterface;
use Stagehand\FSM\State\StateInterface;

/**
 * @package    Stagehand_FSM
 * @copyright  2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://opensource.org/licenses/BSD-2-Clause  The BSD 2-Clause License
 * @version    Release: @package_version@
 * @link       http://en.wikipedia.org/wiki/Finite_state_machine
 * @link       http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @link       http://pear.php.net/package/FSM
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
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     * @since Property available since Release 2.1.0
     */
    protected $eventDispatcher;

    /**
     * @param string $stateMachineID
     */
    public function __construct($stateMachineID = null)
    {
        $this->stateMachineID = $stateMachineID;
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
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @since Method available since Release 2.1.0
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Starts the state machine.
     */
    public function start()
    {
        $this->initialize();
        $this->triggerEvent(EventInterface::EVENT_START);
    }

    /**
     * Gets the current state of the state machine.
     *
     * @return \Stagehand\FSM\State\StateInterface
     */
    public function getCurrentState()
    {
        return $this->getState($this->currentStateID);
    }

    /**
     * Gets the previous state of the state machine.
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
     * Triggers an event in the current state.
     * <i>Note: Do not call this method directly from actions.</i>
     *
     * @param  string                                                           $eventID
     * @throws \Stagehand\FSM\StateMachine\StateMachineAlreadyShutdownException
     */
    public function triggerEvent($eventID)
    {
        $this->queueEvent($eventID);

        do {
            if ($this->currentStateID == StateInterface::STATE_FINAL) {
                throw new StateMachineAlreadyShutdownException('The state machine was already shutdown.');
            }

            $event = $this->getCurrentState()->getEvent(array_shift($this->eventQueue));
            if (!is_null($this->eventDispatcher)) {
                $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_PROCESS, new StateMachineEvent($this, $this->getCurrentState(), $event));
            }
            if ($event instanceof TransitionEventInterface && (is_null($event->getGuard()) || $this->evaluateGuard($event))) {
                $this->transition($event);
            }

            $doEvent = $this->getCurrentState()->getEvent(EventInterface::EVENT_DO);
            if (!is_null($this->eventDispatcher)) {
                $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_DO, new StateMachineEvent($this, $this->getCurrentState(), $doEvent));
            }
            if (!is_null($doEvent) && !is_null($doEvent->getAction())) {
                $this->invokeAction($doEvent);
            }
        } while (count($this->eventQueue) > 0);
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
     * Gets the state according to the given ID.
     *
     * @param  string                              $stateID
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
     * Adds a state to the state machine.
     *
     * @param  \Stagehand\FSM\State\StateInterface                 $state
     * @throws \Stagehand\FSM\StateMachine\DuplicateStateException
     */
    public function addState(StateInterface $state)
    {
        if (array_key_exists($state->getStateID(), $this->states)) {
            throw new DuplicateStateException(sprintf('The state "%s" already exists.', $state->getStateID()));
        }

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
     * Sets the payload to the state machine.
     *
     * @param mixed $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Transitions to the next state.
     *
     * @param  \Stagehand\FSM\Event\TransitionEventInterface $event
     * @throws \Stagehand\FSM\StateNotFoundException
     */
    protected function transition(TransitionEventInterface $event)
    {
        $exitEvent = $this->getCurrentState()->getEvent(EventInterface::EVENT_EXIT);
        if (!is_null($this->eventDispatcher)) {
            $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_EXIT, new StateMachineEvent($this, $this->getCurrentState(), $exitEvent));
        }
        if (!is_null($exitEvent) && !is_null($exitEvent->getAction())) {
            $this->invokeAction($exitEvent);
        }

        if (!is_null($this->eventDispatcher)) {
            $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_TRANSITION, new StateMachineEvent($this, $this->getCurrentState(), $event));
        }
        if (!is_null($event->getAction())) {
            $this->invokeAction($event);
        }

        $this->previousStateID = $this->currentStateID;
        $this->currentStateID = $event->getNextState()->getStateID();

        $entryEvent = $this->getCurrentState()->getEvent(EventInterface::EVENT_ENTRY);
        if (!is_null($this->eventDispatcher)) {
            $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_ENTRY, new StateMachineEvent($this, $this->getCurrentState(), $entryEvent));
        }
        if (!is_null($entryEvent) && !is_null($entryEvent->getAction())) {
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
     * Evaluates the guard for the given event.
     *
     * @param  \Stagehand\FSM\Event\EventInterface $event
     * @return boolean
     * @since Method available since Release 2.0.0
     */
    protected function evaluateGuard(EventInterface $event)
    {
        return call_user_func($event->getGuard(), $event, $this->getPayload(), $this);
    }

    /**
     * Invokes the action for the given event.
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
