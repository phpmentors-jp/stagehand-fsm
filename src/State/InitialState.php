<?php
/*
 * Copyright (c) 2013-2015 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\State;

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\Event\TransitionEventInterface;

/**
 * @since Class available since Release 2.0.0
 */
class InitialState implements StateInterface
{
    /**
     * @var TransitionEventInterface
     */
    private $transitionEvent;

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
        if ($this->transitionEvent !== null && $eventId == $this->transitionEvent->getEventId()) {
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
    private function setTransitionEvent(TransitionEventInterface $transitionEvent)
    {
        $this->transitionEvent = $transitionEvent;
    }

    /**
     * @param  TransitionEventInterface $event
     * @throws InvalidEventException
     * @since Method available since Release 2.2.0
     */
    public function addTransitionEvent(TransitionEventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_START) {
            throw new InvalidEventException(sprintf('The transition event for the state "%s" should be "%s", "%s" is specified.', $this->getStateId(), EventInterface::EVENT_START, $event->getEventId()));
        }

        $this->setTransitionEvent($event);
    }

    /**
     * {@inheritDoc}
     */
    public function isEndState()
    {
        return $this->transitionEvent !== null && $this->transitionEvent->isEndEvent();
    }
}
