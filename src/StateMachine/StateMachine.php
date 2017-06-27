<?php
/*
 * Copyright (c) KUBO Atsuhiro <kubo@iteman.jp>,
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
use Stagehand\FSM\State\StateCollection;
use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\State\TransitionalStateInterface;
use Stagehand\FSM\Token\Token;
use Stagehand\FSM\Transition\ActionRunnerInterface;
use Stagehand\FSM\Transition\GuardEvaluatorInterface;
use Stagehand\FSM\Transition\TransitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @see  http://en.wikipedia.org/wiki/Finite_state_machine
 * @see  http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @see  http://pear.php.net/package/FSM
 * @see  http://www.generation5.org/content/2003/FSM_Tutorial.asp
 * @since Class available since Release 0.1.0
 */
class StateMachine implements StateMachineInterface
{
    /**
     * @var StateCollection
     *
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
    private $eventQueue = [];

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
    private $transitionLog = [];

    /**
     * @var array
     *
     * @since Property available since Release 2.3.0
     */
    private $transitionMap = [];

    /**
     * @var ActionRunnerInterface[]
     *
     * @since Property available since Release 3.0.0
     */
    private $actionRunners;

    /**
     * @var GuardEvaluatorInterface[]
     *
     * @since Property available since Release 3.0.0
     */
    private $guardEvaluators;

    /**
     * @var StateInterface
     *
     * @since Property available since Release 3.0.0
     */
    private $currentState;

    /**
     * @var TransitionalStateInterface
     *
     * @since Property available since Release 3.0.0
     */
    private $previousState;

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
     * {@inheritdoc}
     */
    public function start()
    {
        if ($this->currentState !== null) {
            throw new StateMachineAlreadyStartedException('The state machine is already started.');
        }

        $initialState = $this->getState(StateInterface::STATE_INITIAL);
        assert($initialState !== null);

        $initialState->setToken(new Token());
        $this->currentState = $this->stateCollection->getCurrentState();
        $this->triggerEvent(EventInterface::EVENT_START);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousState()
    {
        return $this->previousState;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * {@inheritdoc}
     */
    public function triggerEvent($eventId)
    {
        $this->queueEvent($eventId);

        do {
            if ($this->isEnded()) {
                throw new StateMachineAlreadyShutdownException('The state machine was already shutdown.');
            }

            $event = $this->currentState->getEvent(array_shift($this->eventQueue));
            if ($this->eventDispatcher !== null) {
                $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_PROCESS, new StateMachineEvent($this, $this->currentState, $event));
            }
            if ($event instanceof TransitionEventInterface && $this->evaluateGuard($event)) {
                $fromState = $this->currentState; /* @var $fromState TransitionalStateInterface */
                $this->transition($fromState, $event);
            }

            $doEvent = $this->currentState->getEvent(EventInterface::EVENT_DO);
            if ($this->eventDispatcher !== null) {
                $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_DO, new StateMachineEvent($this, $this->currentState, $doEvent));
            }
            if ($doEvent !== null) {
                $this->runAction($doEvent);
            }
        } while (count($this->eventQueue) > 0);
    }

    /**
     * {@inheritdoc}
     *
     * @since Method available since Release 1.7.0
     */
    public function queueEvent($eventId)
    {
        if ($this->currentState === null) {
            throw $this->createStateMachineNotStartedException();
        }

        if ($this->currentState->getStateId() == StateInterface::STATE_FINAL) {
            throw new StateMachineAlreadyShutdownException('The state machine was already shutdown.');
        }

        $this->eventQueue[] = $eventId;
    }

    /**
     * {@inheritdoc}
     */
    public function getState($stateId)
    {
        return $this->stateCollection->get($stateId);
    }

    /**
     * {@inheritdoc}
     */
    public function addState(StateInterface $state)
    {
        $this->stateCollection->add($state);
    }

    /**
     * {@inheritdoc}
     */
    public function getStateMachineId()
    {
        return $this->stateMachineId;
    }

    /**
     * {@inheritdoc}
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function addTransition(TransitionInterface $transition)
    {
        $this->transitionMap[$transition->getFromState()->getStateId()][$transition->getEvent()->getEventId()] = $transition;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransitionLog()
    {
        return $this->transitionLog;
    }

    /**
     * {@inheritdoc}
     *
     * @since Method available since Release 2.3.0
     */
    public function isActive()
    {
        if ($this->currentState === null) {
            return false;
        }

        return $this->currentState != StateInterface::STATE_FINAL;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnded()
    {
        if ($this->currentState === null) {
            return false;
        }

        return $this->currentState == StateInterface::STATE_FINAL;
    }

    /**
     * {@inheritdoc}
     */
    public function addActionRunner(ActionRunnerInterface $actionRunner)
    {
        $this->actionRunners[] = $actionRunner;
    }

    /**
     * {@inheritdoc}
     */
    public function addGuardEvaluator(GuardEvaluatorInterface $guardEvaluator)
    {
        $this->guardEvaluators[] = $guardEvaluator;
    }

    /**
     * Transitions to the next state.
     *
     * @param TransitionalStateInterface $fromState
     * @param TransitionEventInterface   $event
     */
    private function transition(TransitionalStateInterface $fromState, TransitionEventInterface $event)
    {
        $exitEvent = $fromState->getEvent(EventInterface::EVENT_EXIT);
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_EXIT, new StateMachineEvent($this, $fromState, $exitEvent));
        }
        if ($exitEvent !== null) {
            $this->runAction($exitEvent);
        }

        $transition = $this->getTransition($fromState, $event);
        $transition->setToken($fromState->getToken());
        $this->previousState = $fromState;
        $this->currentState = null;
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_TRANSITION, new StateMachineEvent($this, null, $event, $transition));
        }
        $this->runAction($event, $transition);
        $transition->getToState()->setToken($transition->getToken());
        $toState = $this->stateCollection->getCurrentState();
        $this->currentState = $toState;
        $this->transitionLog[] = $this->createTransitionLogEntry($transition);

        $entryEvent = $toState->getEvent(EventInterface::EVENT_ENTRY);
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(StateMachineEvents::EVENT_ENTRY, new StateMachineEvent($this, $toState, $entryEvent));
        }
        if ($entryEvent !== null) {
            $this->runAction($entryEvent);
        }
    }

    /**
     * Evaluates the guard for the given event.
     *
     * @param TransitionEventInterface $event
     *
     * @return bool
     *
     * @since Method available since Release 2.0.0
     */
    private function evaluateGuard(TransitionEventInterface $event)
    {
        foreach ((array) $this->guardEvaluators as $guardEvaluator) {
            $result = call_user_func([$guardEvaluator, 'evaluate'], $event, $this->getPayload(), $this);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Runs the action for the given event.
     *
     * @param EventInterface           $event
     * @param TransitionInterface|null $transition
     *
     * @since Method available since Release 2.0.0
     */
    private function runAction(EventInterface $event, TransitionInterface $transition = null)
    {
        foreach ((array) $this->actionRunners as $actionRunner) {
            call_user_func([$actionRunner, 'run'], $event, $this->getPayload(), $this, $transition);
        }
    }

    /**
     * @param TransitionInterface $transition
     *
     * @return TransitionLog
     */
    private function createTransitionLogEntry(TransitionInterface $transition)
    {
        return new TransitionLog($transition, new \DateTime());
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
     * @param TransitionalStateInterface $state
     * @param TransitionEventInterface   $event
     *
     * @return TransitionInterface
     *
     * @since Method available since Release 3.0.0
     */
    private function getTransition(TransitionalStateInterface $state, TransitionEventInterface $event): TransitionInterface
    {
        return $this->transitionMap[$state->getStateId()][$event->getEventId()];
    }
}
