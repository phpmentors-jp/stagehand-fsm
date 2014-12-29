<?php
/*
 * Copyright (c) 2013-2014 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\State;

use Stagehand\FSM\Event\TransitionEventInterface;

/**
 * @since Class available since Release 2.0.0
 */
class InitialState implements StateInterface
{
    /**
     * @var TransitionEventInterface
     */
    protected $transitionEvent;

    /**
     * @since Method available since Release 2.1.0
     */
    public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getEvent($eventId)
    {
        if (!is_null($this->transitionEvent) && $eventId == $this->transitionEvent->getEventId()) {
            return $this->transitionEvent;
        } else {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getStateId()
    {
        return StateInterface::STATE_INITIAL;
    }

    /**
     * @param TransitionEventInterface $transitionEvent
     */
    public function setTransitionEvent(TransitionEventInterface $transitionEvent)
    {
        $this->transitionEvent = $transitionEvent;
    }

    /**
     * {@inheritDoc}
     */
    public function isEndState()
    {
        return !is_null($this->transitionEvent) && $this->transitionEvent->isEndEvent();
    }
}
