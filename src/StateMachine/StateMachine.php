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

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\Event\TransitionEventInterface;
use Stagehand\FSM\State\FinalState;
use Stagehand\FSM\State\State;
use Stagehand\FSM\State\StateCollection;
use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\State\TransitionalStateInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     *
     * @deprecated Deprecated since version 2.1.0, to be removed in 3.0.0.
     */
    protected $currentStateID;

    /**
     * @var string
     *
     * @deprecated Deprecated since version 2.3.0, to be removed in 3.0.0.
     */
    protected $currentStateId;

    /**
     * @var string
     *
     * @deprecated Deprecated since version 2.1.0, to be removed in 3.0.0.
     */
    protected $previousStateID;

    /**
     * @var string
     *
     * @deprecated Deprecated since version 2.3.0, to be removed in 3.0.0.
     */
    protected $previousStateId;

    /**
     * @var array
     *
     * @deprecated Deprecated since version 2.2.0, to be removed in 3.0.0.
     */
    protected $states = array();

    /**
     * @var StateCollection
     *
     * @since Property available since Release 2.2.0
     */
    private $stateCollection;

    /**
     * @var string
     */
    protected $stateMachineId;

    /**
     * @var string
     *
     * @deprecated Deprecated since version 2.1.0, to be removed in 3.0.0.
     */
    protected $stateMachineID;

    /**
     * @var mixed
     */
    private $payload;

    /**
     * @var array
     */
    protected $eventQueue = array();

    /**
     * @var EventDispatcherInterface
     *
     * @since Property available since Release 2.1.0
     */
    private $eventDispatcher;

    /**
     * @var bool
     *
     * @since Property available since Release 2.3.0
     */
    private $active = false;

    /**
     * @var TransitionLog[]
     *
     * @since Property available since Release 2.4.0
     */
    private $transitionLog = array();

    /**
     * @var TransitionLog[]
     *
     * @since Property available since Release 2.3.0
     * @deprecated Deprecated since version 2.4.0, to be removed in 3.0.0.
     */
    private $transitionLogs = array();

    /**
     * @var array
     *
     * @since Property available since Release 2.3.0
     */
    private $transitionMap = array();

    /**
     * {@inheritDoc}
     *
     * @since Method available since Release 2.2.0
     */
    public function serialize()
    {
        return serialize(array(
            'stateCollection' => $this->stateCollection,
            'stateMachineId' => $this->stateMachineId,
            'eventQueue' => $this->eventQueue,
            'transitionLog' => $this->transitionLog,
            'active' => $this->active,
            'transitionMap' => $this->transitionMap,
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
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }

        if ($this->currentStateId !== null) {
            $currentState = $this->getState($this->currentStateId);
            $this->buildTransitionMapFromStates($this->stateCollection);
        } else {
            $currentState = null;
        }

        $this->rebuildTransitionEvents($this->stateCollection);

        if (count($this->transitionLogs) > 0) {
            $this->transitionLog = $this->transitionLogs;
        }

        if ($currentState !== null) {
            $this->active = true;

            if ($this->previousStateId !== null) {
                $previousState = $this->getState($this->previousStateId);
            } else {
                $previousState = null;
            }

            if ($previousState !== null) {
                $this->transitionLog[] = $this->createTransitionLogEntry($currentState, $previousState);
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @since Method available since Release 2.2.0
     */
    public function __wakeup()
    {
        if (count($this->states) > 0) {
            $this->stateCollection = new StateCollection($this->states);
        }

        $this->buildTransitionMapFromStates($this->stateCollection);

        if ($this->currentStateID !== null) {
            $currentState = $this->getState($this->currentStateID);
        } elseif ($this->currentStateId !== null) {
            $currentState = $this->getState($this->currentStateId);
        } else {
            $currentState = null;
        }

        if ($currentState !== null) {
            $this->active = true;

            if ($this->previousStateID !== null) {
                $previousState = $this->getState($this->previousStateID);
            } elseif ($this->previousStateId !== null) {
                $previousState = $this->getState($this->previousStateId);
            } else {
                $previousState = null;
            }

            if ($previousState !== null) {
                $this->transitionLog[] = $this->createTransitionLogEntry($currentState, $previousState);
            }
        }

        if ($this->stateMachineID !== null) {
            $this->stateMachineId = $this->stateMachineID;
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
     *
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
        if ($this->active) {
            throw new StateMachineAlreadyStartedException('The state machine is already started.');
        }

        $this->active = true;
        $this->triggerEvent(EventInterface::EVENT_START);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentState()
    {
        if ($this->active) {
            if (count($this->transitionLog) == 0) {
                return $this->getState(StateInterface::STATE_INITIAL);
            }
        } else {
            if (!$this->isEnded()) {
                return null;
            }
        }

        return $this->transitionLog[count($this->transitionLog) - 1]->getToState();
    }

    /**
     * {@inheritDoc}
     */
    public function getPreviousState()
    {
        if ($this->active) {
            if (count($this->transitionLog) == 0) {
                return null;
            }
        } else {
            if (!$this->isEnded()) {
                return null;
            }
        }

        return $this->transitionLog[count($this->transitionLog) - 1]->getFromState();
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
            if ($this->isEnded()) {
                throw new StateMachineAlreadyShutdownException('The state machine was already shutdown.');
            }

            $event = $this->getCurrentState()->getEvent(array_shift($this->eventQueue));
            if ($this->eventDispatcher !== null) {
                $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_PROCESS, new StateMachineEvent($this, $this->getCurrentState(), $event));
            }
            if ($event instanceof TransitionEventInterface && $this->evaluateGuard($event)) {
                $this->transition($event);
                if ($this->isEnded()) {
                    $this->active = false;
                }
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
        if (!$this->active) {
            if ($this->isEnded()) {
                throw new StateMachineAlreadyShutdownException('The state machine was already shutdown.');
            } else {
                throw $this->createStateMachineNotStartedException();
            }
        }

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

        $this->transitionMap[$state->getStateId()][$event->getEventId()] = $nextState;
    }

    /**
     * {@inheritDoc}
     */
    public function getTransitionLog()
    {
        return $this->transitionLog;
    }

    /**
     * @return TransitionLog[]
     *
     * @since Method available since Release 2.3.0
     * @deprecated Deprecated since version 2.4.0, to be removed in 3.0.0.
     */
    public function getTransitionLogs()
    {
        return $this->getTransitionLog();
    }

    /**
     * {@inheritDoc}
     *
     * @since Method available since Release 2.3.0
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnded()
    {
        return count($this->transitionLog) > 0 && $this->transitionLog[count($this->transitionLog) - 1]->getToState() instanceof FinalState;
    }

    /**
     * Transitions to the next state.
     *
     * @param TransitionEventInterface $event
     *
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

        $this->transitionLog[] = $this->createTransitionLogEntry($this->transitionMap[$this->getCurrentState()->getStateId()][$event->getEventId()], $this->getCurrentState(), $event);

        $entryEvent = $this->getCurrentState()->getEvent(EventInterface::EVENT_ENTRY);
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_ENTRY, new StateMachineEvent($this, $this->getCurrentState(), $entryEvent));
        }
        $this->invokeAction($entryEvent);
    }

    /**
     * Evaluates the guard for the given event.
     *
     * @param EventInterface $event
     *
     * @return bool
     *
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
     *
     * @since Method available since Release 2.0.0
     */
    private function invokeAction(EventInterface $event = null)
    {
        if ($event !== null && $event->getAction() !== null) {
            call_user_func($event->getAction(), $event, $this->getPayload(), $this);
        }
    }

    /**
     * @param StateInterface           $toState
     * @param StateInterface           $fromState
     * @param TransitionEventInterface $event
     *
     * @return TransitionLog
     */
    private function createTransitionLogEntry(StateInterface $toState, StateInterface $fromState = null, TransitionEventInterface $event = null)
    {
        return new TransitionLog($toState, $fromState, $event, new \DateTime());
    }

    /**
     * @return StateMachineNotStartedException
     *
     * @since Method available since Release 2.3.0
     */
    private function createStateMachineNotStartedException()
    {
        return new StateMachineNotStartedException('The state machine is not started yet.');
    }

    /**
     * @param StateCollection $stateCollection
     *
     * @since Method available since Release 2.3.0
     */
    private function buildTransitionMapFromStates(StateCollection $stateCollection)
    {
        foreach ($stateCollection as $state) {
            if ($state instanceof State) {
                $stateClass = new \ReflectionClass($state);
                $eventCollectionProperty = $stateClass->getProperty('eventCollection');
                $eventCollectionProperty->setAccessible(true);
                $eventCollection = $eventCollectionProperty->getValue($state);
                $eventCollectionProperty->setAccessible(false);
                foreach ($eventCollection as $event) {
                    if ($event instanceof TransitionEventInterface) {
                        $this->transitionMap[$state->getStateId()][$event->getEventId()] = $event->getNextState();
                    }
                }
            }
        }
    }

    /**
     * @param StateCollection $stateCollection
     *
     * @since Method available since Release 2.3.0
     */
    private function rebuildTransitionEvents(StateCollection $stateCollection)
    {
        foreach ($stateCollection as $state) {
            if ($state instanceof State) {
                $stateClass = new \ReflectionClass($state);
                $eventCollectionProperty = $stateClass->getProperty('eventCollection');
                $eventCollectionProperty->setAccessible(true);
                $eventCollection = $eventCollectionProperty->getValue($state);
                $eventCollectionProperty->setAccessible(false);
                foreach ($eventCollection as $event) {
                    if ($event instanceof TransitionEventInterface) {
                        if ($event->getNextState() === null) {
                            $eventClass = new \ReflectionClass($event);
                            $nextStateProperty = $eventClass->getProperty('nextState');
                            $nextStateProperty->setAccessible(true);
                            $nextStateProperty->setValue($event, $this->transitionMap[$state->getStateId()][$event->getEventId()]);
                            $nextStateProperty->setAccessible(false);
                        }
                    }
                }
            }
        }
    }
}
