<?php
/*
 * Copyright (c) 2013 KUBO Atsuhiro <kubo@iteman.jp>,
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
     * @var EventInterface
     */
    private $event;

    /**
     * @param StateMachine   $stateMachine
     * @param StateInterface $state
     * @param EventInterface $event
     */
    public function __construct(StateMachine $stateMachine, StateInterface $state, EventInterface $event = null)
    {
        $this->stateMachine = $stateMachine;
        $this->state = $state;
        $this->event = $event;
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
}
