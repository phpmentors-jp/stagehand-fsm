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
use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\Transition\TransitionInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @since Class available since Release 2.1.0
 */
class StateMachineEvent extends Event
{
    /**
     * @var StateMachine
     */
    private $stateMachine;

    /**
     * @var StateInterface
     */
    private $state;

    /**
     * @var EventInterface|null
     */
    private $event;

    /**
     * @var TransitionInterface|null
     *
     * @since Property available since Release 3.0.0
     */
    private $transition;

    /**
     * @param StateMachine             $stateMachine
     * @param StateInterface|null      $state
     * @param EventInterface|null      $event
     * @param TransitionInterface|null $transition
     */
    public function __construct(StateMachine $stateMachine, StateInterface $state = null, EventInterface $event = null, TransitionInterface $transition = null)
    {
        $this->stateMachine = $stateMachine;
        $this->state = $state;
        $this->event = $event;
        $this->transition = $transition;
    }

    /**
     * @return StateMachine
     */
    public function getStateMachine()
    {
        return $this->stateMachine;
    }

    /**
     * @return StateInterface
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return EventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return TransitionInterface
     *
     * @since Method available since Release 3.0.0
     */
    public function getTransition()
    {
        return $this->transition;
    }
}
