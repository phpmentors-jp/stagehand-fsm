<?php
/*
 * Copyright (c) 2006-2008, 2011-2015 KUBO Atsuhiro <kubo@iteman.jp>,
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
use Stagehand\FSM\State\StateCollection;
use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\State\TransitionalStateInterface;

/**
 * @link  http://en.wikipedia.org/wiki/Finite_state_machine
 * @link  http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @link  http://pear.php.net/package/FSM
 * @link  http://www.generation5.org/content/2003/FSM_Tutorial.asp
 * @since Class available since Release 0.1.0
 */
class StateMachine implements StateMachineInterface, \Serializable
{
    /**
     * @var string
     */
    private $currentStateId;

    /**
     * @var string
     */
    private $previousStateId;

    /**
     * @var array
     * @deprecated Deprecated since version 2.2.0, to be removed in 3.0.0.
     */
    private $states = array();

    /**
     * @var StateCollection
     * @since Property available since Release 2.2.0
     */
    private $stateCollection;

    /**
     * @var string
     */
    private $stateMachineId;

    /**
     * @var mixed
     */
    private $payload;

    /**
     * @var array
     */
    private $eventQueue = array();

    /**
     * @var EventDispatcherInterface
     * @since Property available since Release 2.1.0
     */
    private $eventDispatcher;

    /**
     * {@inheritDoc}
     *
     * @since Method available since Release 2.2.0
     */
    public function serialize()
    {
        return serialize(array(
            'currentStateId' => $this->currentStateId,
            'previousStateId' => $this->previousStateId,
            'stateCollection' => $this->stateCollection,
            'stateMachineId' => $this->stateMachineId,
            'eventQueue' => $this->eventQueue,
        ));
    }

    /**
     * {@inheritDoc}
     *
     * @since Method available since Release 2.2.0
     */
    public function unserialize($serialized)
    {
        foreach (unserialize($serialized) as $name => $value) {
            if ($name == 'states') {
                $this->stateCollection = new StateCollection($value);
            } else {
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * @param string $stateMachineId
     */
    public function __construct($stateMachineId = null)
    {
        $this->stateCollection = new StateCollection();
        $this->stateMachineId = $stateMachineId;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @since Method available since Release 2.1.0
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function start()
    {
        if ($this->getCurrentState() !== null) {
            throw new StateMachineAlreadyStartedException('The state machine is already started.');
        }

        $this->initialize();
        $this->triggerEvent(EventInterface::EVENT_START);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentState()
    {
        if ($this->currentStateId === null) {
            return null;
        }

        return $this->getState($this->currentStateId);
    }

    /**
     * {@inheritDoc}
     */
    public function getPreviousState()
    {
        if ($this->previousStateId === null) {
            return null;
        }

        return $this->getState($this->previousStateId);
    }

    /**
     * {@inheritDoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * {@inheritDoc}
     */
    public function triggerEvent($eventId)
    {
        $this->queueEvent($eventId);

        do {
            if ($this->getCurrentState() === null) {
                throw new StateMachineNotStartedException('The state machine is not started yet.');
            }

            if ($this->currentStateId == StateInterface::STATE_FINAL) {
                throw new StateMachineAlreadyShutdownException('The state machine was already shutdown.');
            }

            $event = $this->getCurrentState()->getEvent(array_shift($this->eventQueue));
            if ($this->eventDispatcher !== null) {
                $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_PROCESS, new StateMachineEvent($this, $this->getCurrentState(), $event));
            }
            if ($event instanceof TransitionEventInterface && $this->evaluateGuard($event)) {
                $this->transition($event);
            }

            $doEvent = $this->getCurrentState()->getEvent(EventInterface::EVENT_DO);
            if ($this->eventDispatcher !== null) {
                $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_DO, new StateMachineEvent($this, $this->getCurrentState(), $doEvent));
            }
            $this->invokeAction($doEvent);
        } while (count($this->eventQueue) > 0);
    }

    /**
     * {@inheritDoc}
     *
     * @since Method available since Release 1.7.0
     */
    public function queueEvent($eventId)
    {
        $this->eventQueue[] = $eventId;
    }

    /**
     * {@inheritDoc}
     */
    public function getState($stateId)
    {
        return $this->stateCollection->get($stateId);
    }

    /**
     * {@inheritDoc}
     */
    public function addState(StateInterface $state)
    {
        $this->stateCollection->add($state);
    }

    /**
     * {@inheritDoc}
     */
    public function getStateMachineId()
    {
        return $this->stateMachineId;
    }

    /**
     * {@inheritDoc}
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * {@inheritDoc}
     */
    public function addTransition(TransitionalStateInterface $state, TransitionEventInterface $event, StateInterface $nextState, $action, $guard)
    {
        $event->setNextState($nextState);
        $event->setAction($action);
        $event->setGuard($guard);
        $state->addTransitionEvent($event);
    }

    /**
     * Transitions to the next state.
     *
     * @param  TransitionEventInterface $event
     * @throws StateNotFoundException
     */
    private function transition(TransitionEventInterface $event)
    {
        $exitEvent = $this->getCurrentState()->getEvent(EventInterface::EVENT_EXIT);
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_EXIT, new StateMachineEvent($this, $this->getCurrentState(), $exitEvent));
        }
        $this->invokeAction($exitEvent);

        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_TRANSITION, new StateMachineEvent($this, $this->getCurrentState(), $event));
        }
        $this->invokeAction($event);

        $this->previousStateId = $this->currentStateId;
        $this->currentStateId = $event->getNextState()->getStateId();

        $entryEvent = $this->getCurrentState()->getEvent(EventInterface::EVENT_ENTRY);
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_ENTRY, new StateMachineEvent($this, $this->getCurrentState(), $entryEvent));
        }
        $this->invokeAction($entryEvent);
    }

    /**
     * Initializes the state machine.
     */
    private function initialize()
    {
        $this->currentStateId = StateInterface::STATE_INITIAL;
        $this->previousStateId = null;
        $this->eventQueue = array();
    }

    /**
     * Evaluates the guard for the given event.
     *
     * @param  EventInterface $event
     * @return boolean
     * @since Method available since Release 2.0.0
     */
    private function evaluateGuard(TransitionEventInterface $event)
    {
        if ($event->getGuard() === null) {
            return true;
        }

        return call_user_func($event->getGuard(), $event, $this->getPayload(), $this);
    }

    /**
     * Invokes the action for the given event.
     *
     * @param EventInterface $event
     * @since Method available since Release 2.0.0
     */
    private function invokeAction(EventInterface $event = null)
    {
        if ($event !== null && $event->getAction() !== null) {
            call_user_func($event->getAction(), $event, $this->getPayload(), $this);
        }
    }
}
