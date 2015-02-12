<?php
/*
 * Copyright (c) 2015 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\StateMachine;

use Stagehand\FSM\Event\TransitionEventInterface;
use Stagehand\FSM\State\StateInterface;

/**
 * @since Class available since Release 2.3.0
 */
class TransitionLog
{
    /**
     * @var TransitionEventInterface
     */
    private $event;

    /**
     * @var StateInterface
     */
    private $fromState;

    /**
     * @var StateInterface
     */
    private $toState;

    /**
     * @var \DateTime
     */
    private $transitionDate;

    /**
     * @param StateInterface           $toState
     * @param StateInterface           $fromState
     * @param TransitionEventInterface $event
     * @param \DateTime                $transitionDate
     */
    public function __construct(StateInterface $toState, StateInterface $fromState = null, TransitionEventInterface $event = null, \DateTime $transitionDate = null)
    {
        $this->toState = $toState;
        $this->fromState = $fromState;
        $this->event = $event;
        $this->transitionDate = $transitionDate;
    }

    /**
     * @return TransitionEventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return StateInterface
     */
    public function getFromState()
    {
        return $this->fromState;
    }

    /**
     * @return StateInterface
     */
    public function getToState()
    {
        return $this->toState;
    }

    /**
     * @return \DateTime
     */
    public function getTransitionDate()
    {
        return $this->transitionDate;
    }
}
