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

use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\Transition\ActionRunnerInterface;
use Stagehand\FSM\Transition\GuardEvaluatorInterface;
use Stagehand\FSM\Transition\TransitionInterface;

/**
 * @since Class available since Release 2.2.0
 */
interface StateMachineInterface
{
    /**
     * @since Constant available since Release 3.0.0
     */
    const EVENT_START = '__START__';

    /**
     * Sets the payload to the state machine.
     *
     * @param mixed $payload
     */
    public function setPayload($payload);

    /**
     * Gets the payload.
     *
     * @return mixed $payload
     */
    public function getPayload();

    /**
     * Adds a state to the state machine.
     *
     * @param StateInterface $state
     */
    public function addState(StateInterface $state);

    /**
     * Gets the state according to the given ID.
     *
     * @param string $stateId
     *
     * @return StateInterface
     */
    public function getState($stateId);

    /**
     * Gets the current state of the state machine.
     *
     * @return StateInterface
     */
    public function getCurrentState();

    /**
     * Gets the previous state of the state machine.
     *
     * @return StateInterface
     */
    public function getPreviousState();

    /**
     * Gets the ID of the state machine.
     *
     * @return string
     */
    public function getStateMachineId();

    /**
     * @param TransitionInterface $transition
     */
    public function addTransition(TransitionInterface $transition);

    /**
     * Starts the state machine.
     */
    public function start();

    /**
     * Triggers an event in the current state.
     * <i>Note: Do not call this method directly from actions.</i>.
     *
     * @param string $eventId
     *
     * @throws StateMachineAlreadyShutdownException
     * @throws StateMachineNotStartedException
     */
    public function triggerEvent($eventId);

    /**
     * Queues an event to the event queue.
     *
     * @param string $eventId
     *
     * @throws StateMachineAlreadyShutdownException
     * @throws StateMachineNotStartedException
     */
    public function queueEvent($eventId);

    /**
     * @return bool
     *
     * @since Method available since Release 2.4.0
     */
    public function isActive();

    /**
     * @return bool
     *
     * @since Method available since Release 2.4.0
     */
    public function isEnded();

    /**
     * @return TransitionLog[]
     *
     * @since Method available since Release 2.4.0
     */
    public function getTransitionLog();

    /**
     * @param ActionRunnerInterface $actionRunner
     *
     * @since Method available since Release 3.0.0
     */
    public function addActionRunner(ActionRunnerInterface $actionRunner);

    /**
     * @param GuardEvaluatorInterface $guardEvaluator
     *
     * @since Method available since Release 3.0.0
     */
    public function addGuardEvaluator(GuardEvaluatorInterface $guardEvaluator);
}
