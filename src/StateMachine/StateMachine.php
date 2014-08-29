<?php
/*
 * Copyright (c) 2006-2008, 2011-2014 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\StateMachine;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\Event\TransitionEventInterface;
use Stagehand\FSM\State\StateInterface;

/**
 * @link  http://en.wikipedia.org/wiki/Finite_state_machine
 * @link  http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @link  http://pear.php.net/package/FSM
 * @link  http://www.generation5.org/content/2003/FSM_Tutorial.asp
 * @since Class available since Release 0.1.0
 */
class StateMachine
{
    /**
     * @var string
     */
    protected $currentStateId;

    /**
     * @var string
     */
    protected $previousStateId;

    /**
     * @var array
     */
    protected $states = array();

    /**
     * @var string
     */
    protected $stateMachineId;

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
     * @param string $stateMachineId
     */
    public function __construct($stateMachineId = null)
    {
        $this->stateMachineId = $stateMachineId;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array(
            'currentStateId',
            'previousStateId',
            'states',
            'stateMachineId',
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
     *
     * @throws \Stagehand\FSM\StateMachine\StateMachineAlreadyStartedException
     */
    public function start()
    {
        if (!is_null($this->getCurrentState())) {
            throw new StateMachineAlreadyStartedException('The state machine is already started.');
        }

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
        if (is_null($this->currentStateId)) {
            return null;
        }

        return $this->getState($this->currentStateId);
    }

    /**
     * Gets the previous state of the state machine.
     *
     * @return \Stagehand\FSM\State\StateInterface
     */
    public function getPreviousState()
    {
        if (is_null($this->previousStateId)) {
            return null;
        }

        return $this->getState($this->previousStateId);
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
     * @param  string                                                           $eventId
     * @throws \Stagehand\FSM\StateMachine\StateMachineAlreadyShutdownException
     * @throws \Stagehand\FSM\StateMachine\StateMachineNotStartedException
     */
    public function triggerEvent($eventId)
    {
        $this->queueEvent($eventId);

        do {
            if (is_null($this->getCurrentState())) {
                throw new StateMachineNotStartedException('The state machine is not started yet.');
            }

            if ($this->currentStateId == StateInterface::STATE_FINAL) {
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
     * @param string $eventId
     * @since Method available since Release 1.7.0
     */
    public function queueEvent($eventId)
    {
        $this->eventQueue[] = $eventId;
    }

    /**
     * Gets the state according to the given ID.
     *
     * @param  string                              $stateId
     * @return \Stagehand\FSM\State\StateInterface
     */
    public function getState($stateId)
    {
        if (array_key_exists($stateId, $this->states)) {
            return $this->states[$stateId];
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
        if (array_key_exists($state->getStateId(), $this->states)) {
            throw new DuplicateStateException(sprintf('The state "%s" already exists.', $state->getStateId()));
        }

        $this->states[ $state->getStateId() ] = $state;
    }

    /**
     * Gets the ID of the state machine.
     *
     * @return string
     */
    public function getStateMachineId()
    {
        return $this->stateMachineId;
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

        $this->previousStateId = $this->currentStateId;
        $this->currentStateId = $event->getNextState()->getStateId();

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
        $this->currentStateId = StateInterface::STATE_INITIAL;
        $this->previousStateId = null;
        $this->eventQueue = array();
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
