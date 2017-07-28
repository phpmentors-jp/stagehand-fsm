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
use Stagehand\FSM\State\TransitionalStateInterface;
use Stagehand\FSM\Transition\TransitionInterface;

/**
 * @since Class available since Release 2.3.0
 */
class TransitionLog
{
    /**
     * @var \DateTime
     */
    private $transitionDate;

    /**
     * @var TransitionInterface
     *
     * @since Property available since Release 3.0.0
     */
    private $transition;

    /**
     * @param TransitionInterface $transition
     * @param \DateTime           $transitionDate
     */
    public function __construct(TransitionInterface $transition, \DateTime $transitionDate)
    {
        $this->transition = $transition;
        $this->transitionDate = $transitionDate;
    }

    /**
     * @return EventInterface
     */
    public function getEvent(): EventInterface
    {
        return $this->transition->getEvent();
    }

    /**
     * @return TransitionalStateInterface
     */
    public function getFromState(): TransitionalStateInterface
    {
        return $this->transition->getFromState();
    }

    /**
     * @return StateInterface
     */
    public function getToState(): StateInterface
    {
        return $this->transition->getToState();
    }

    /**
     * @return \DateTime
     */
    public function getTransitionDate()
    {
        return $this->transitionDate;
    }
}
