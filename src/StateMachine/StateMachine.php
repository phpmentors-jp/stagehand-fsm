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
use Stagehand\FSM\State\AutomaticTransitionInterface;
use Stagehand\FSM\State\ParentStateInterface;
use Stagehand\FSM\State\StateActionInterface;
use Stagehand\FSM\State\StateCollection;
use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\State\TransitionalStateInterface;
use Stagehand\FSM\Transition\ActionRunnerInterface;
use Stagehand\FSM\Transition\GuardEvaluatorInterface;
use Stagehand\FSM\Transition\TransitionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @see  http://en.wikipedia.org/wiki/Finite_state_machine
 * @see  http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @see  http://pear.php.net/package/FSM
 * @see  http://www.generation5.org/content/2003/FSM_Tutorial.asp
 * @see  https://sparxsystems.com/enterprise_architect_user_guide/14.0/model_simulation/example__fork_and_join.html
 * @see  https://online.visual-paradigm.com/diagrams/tutorials/state-machine-diagram-tutorial/
 * @see  https://www.uml-diagrams.org/state-machine-diagrams.html
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
     * @var StateMachine
     *
     * @since Property available since Release 3.0.0
     */
    private $parent;

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
    public function start(StateMachineInterface $parent = null)
    {
        if ($this->currentState !== null) {
            throw new StateMachineAlreadyStartedException('The state machine is already started.');
        }

        $initialState = $this->getState(self::STATE_INITIAL);
        assert($initialState !== null);

        if ($parent !== null) {
            $this->parent = $parent;
        }
        $this->currentState = $initialState;
        $this->triggerEvent(self::EVENT_START);
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
        if ($this->parent !== null) {
            return $this->parent->getPayload();
        }

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

            $event = $this->currentState->getTransitionEvent(array_shift($this->eventQueue));
            if ($event !== null) {
                if ($this->eventDispatcher !== null) {
                    $this->eventDispatcher->dispatch(new StateMachineEvent($this, $this->currentState, $event), StateMachineEvents::EVENT_PROCESS);
                }

                $fromState = $this->currentState;
                if ($fromState instanceof TransitionalStateInterface) {
                    $transition = $this->getTransition($fromState, $event);
                    if ($this->evaluateGuard($this, $event, $transition)) {
                        $toState = $this->transition($transition);
                    }
                }
            }

            if ($this->currentState instanceof StateActionInterface) {
                $doEvent = $this->currentState->getDoEvent();
                if ($doEvent !== null) {
                    if ($this->eventDispatcher !== null) {
                        $this->eventDispatcher->dispatch(new StateMachineEvent($this, $this->currentState, $doEvent), StateMachineEvents::EVENT_DO);
                    }

                    $this->runAction($this, $doEvent);
                }
            }

            if (isset($toState)) {
                if ($toState instanceof ParentStateInterface) {
                    $this->fork($toState);
                }

                if ($toState->getStateId() == self::STATE_FINAL) {
                    if ($this->parent != null) {
                        $parentCurrentState = $this->parent->getCurrentState();
                        if ($parentCurrentState instanceof ParentStateInterface) {
                            $this->parent->join($parentCurrentState);
                        }
                    }
                }
            }

            if ($this->currentState instanceof AutomaticTransitionInterface) {
                $this->queueEvent($this->currentState->getAutomaticTransitionEvent()->getEventId());
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

        if ($this->currentState->getStateId() == self::STATE_FINAL) {
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
        if ($this->parent !== null) {
            return $this->parent->setPayload($payload);
        }

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

        return $this->currentState->getStateId() != self::STATE_FINAL;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnded()
    {
        if ($this->currentState === null) {
            return false;
        }

        return $this->currentState->getStateId() == self::STATE_FINAL;
    }

    /**
     * {@inheritdoc}
     */
    public function addActionRunner(ActionRunnerInterface $actionRunner)
    {
        if ($this->parent !== null) {
            return $this->parent->addActionRunner($actionRunner);
        }

        $this->actionRunners[] = $actionRunner;
    }

    /**
     * {@inheritdoc}
     */
    public function addGuardEvaluator(GuardEvaluatorInterface $guardEvaluator)
    {
        if ($this->parent !== null) {
            return $this->parent->addGuardEvaluator($guardEvaluator);
        }

        $this->guardEvaluators[] = $guardEvaluator;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransitionMap()
    {
        return $this->transitionMap;
    }

    /**
     * Transitions to the next state.
     *
     * @param TransitionInterface $transition
     *
     * @return StateInterface
     */
    private function transition(TransitionInterface $transition)
    {
        if ($transition->getFromState() instanceof StateActionInterface) {
            $exitEvent = $transition->getFromState()->getExitEvent();
            if ($exitEvent !== null) {
                if ($this->eventDispatcher !== null) {
                    $this->eventDispatcher->dispatch(new StateMachineEvent($this, $transition->getFromState(), $exitEvent), StateMachineEvents::EVENT_EXIT);
                }

                $this->runAction($this, $exitEvent);
            }
        }

        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(new StateMachineEvent($this, null, $transition->getEvent(), $transition), StateMachineEvents::EVENT_TRANSITION);
        }
        $this->runAction($this, $transition->getEvent(), $transition);
        $this->previousState = $transition->getFromState();
        $this->currentState = $toState = $transition->getToState();
        $this->transitionLog[] = $this->createTransitionLogEntry($transition);

        if ($toState instanceof StateActionInterface) {
            $entryEvent = $toState->getEntryEvent();
            if ($entryEvent !== null) {
                if ($this->eventDispatcher !== null) {
                    $this->eventDispatcher->dispatch(new StateMachineEvent($this, $toState, $entryEvent), StateMachineEvents::EVENT_ENTRY);
                }

                $this->runAction($this, $entryEvent);
            }
        }

        return $toState;
    }

    /**
     * Evaluates the guard for the given event.
     *
     * @param StateMachineInterface $stateMachine
     * @param EventInterface        $event
     * @param TransitionInterface   $transition
     *
     * @return bool
     *
     * @since Method available since Release 2.0.0
     */
    private function evaluateGuard(StateMachineInterface $stateMachine, EventInterface $event, TransitionInterface $transition)
    {
        if ($this->parent !== null) {
            return $this->parent->evaluateGuard($stateMachine, $event, $transition);
        }

        foreach ((array) $this->guardEvaluators as $guardEvaluator) {
            $result = call_user_func([$guardEvaluator, 'evaluate'], $event, $this->getPayload(), $stateMachine, $transition);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Runs the action for the given event.
     *
     * @param StateMachineInterface    $stateMachine
     * @param EventInterface           $event
     * @param TransitionInterface|null $transition
     *
     * @return bool
     *
     * @since Method available since Release 2.0.0
     */
    private function runAction(StateMachineInterface $stateMachine, EventInterface $event, TransitionInterface $transition = null)
    {
        if ($this->parent !== null) {
            return $this->parent->runAction($stateMachine, $event, $transition);
        }

        foreach ((array) $this->actionRunners as $actionRunner) {
            call_user_func([$actionRunner, 'run'], $event, $this->getPayload(), $stateMachine, $transition);
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
     * @param EventInterface             $event
     *
     * @return TransitionInterface
     *
     * @since Method available since Release 3.0.0
     */
    private function getTransition(TransitionalStateInterface $state, EventInterface $event): TransitionInterface
    {
        return $this->transitionMap[$state->getStateId()][$event->getEventId()];
    }

    /**
     * @param ParentStateInterface $parent
     */
    private function fork(ParentStateInterface $parent)
    {
        foreach ($parent->getChildren() as $child) {
            $child->start($this);
        }
    }

    /**
     * @param ParentStateInterface $parent
     */
    private function join(ParentStateInterface $parent)
    {
        foreach ($parent->getChildren() as $child) {
            if (!$child->isEnded()) {
                return;
            }
        }

        $this->triggerEvent(self::EVENT_JOIN);
    }
}
