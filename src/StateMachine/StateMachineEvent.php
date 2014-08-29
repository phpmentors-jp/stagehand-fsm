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

use Symfony\Component\EventDispatcher\Event;

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\State\StateInterface;

/**
 * @since Class available since Release 2.1.0
 */
class StateMachineEvent extends Event
{
    /**
     * @var \Stagehand\FSM\StateMachine\StateMachine
     */
    protected $stateMachine;

    /**
     * @var \Stagehand\FSM\State\StateInterface
     */
    protected $state;

    /**
     * @var \Stagehand\FSM\Event\EventInterface
     */
    protected $event;

    /**
     * @param \Stagehand\FSM\StateMachine\StateMachine $stateMachine
     * @param \Stagehand\FSM\State\StateInterface      $state
     * @param \Stagehand\FSM\Event\EventInterface      $event
     */
    public function __construct(StateMachine $stateMachine, StateInterface $state, EventInterface $event = null)
    {
        $this->stateMachine = $stateMachine;
        $this->state = $state;
        $this->event = $event;
    }

    /**
     * @return \Stagehand\FSM\StateMachine\StateMachine
     */
    public function getStateMachine()
    {
        return $this->stateMachine;
    }

    /**
     * @return \Stagehand\FSM\State\StateInterface
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return \Stagehand\FSM\Event\EventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }
}
