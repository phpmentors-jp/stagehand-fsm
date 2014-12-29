<?php
/*
 * Copyright (c) 2006-2007, 2011-2014 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @since Class available since Release 0.1.0
 */
class State implements StateInterface
{
    /**
     * @var string
     */
     protected $stateId;

    /**
     * @var array
     */
    protected $events = array();

    /**
     * @param string $stateId
     */
    public function __construct($stateId)
    {
        $this->stateId = $stateId;
    }

    /**
     * @param  EventInterface        $event
     * @throws InvalidEventException
     * @since Method available since Release 2.0.0
     */
    public function setEntryEvent(EventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_ENTRY) {
            throw new InvalidEventException(sprintf('The event "%s" is not an entry event. "%s" must be set as the ID for an entry event ', $event->getEventId(), EventInterface::EVENT_ENTRY));
        }

        $this->events[ $event->getEventId() ] = $event;
    }

    /**
     * @param  EventInterface        $event
     * @throws InvalidEventException
     * @since Method available since Release 2.0.0
     */
    public function setExitEvent(EventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_EXIT) {
            throw new InvalidEventException(sprintf('The event "%s" is not an exit event. "%s" must be set as the ID for an exit event ', $event->getEventId(), EventInterface::EVENT_EXIT));
        }

        $this->events[ $event->getEventId() ] = $event;
    }

    /**
     * @param  EventInterface        $event
     * @throws InvalidEventException
     * @since Method available since Release 2.0.0
     */
    public function setDoEvent(EventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_DO) {
            throw new InvalidEventException(sprintf('The event "%s" is not a do event. "%s" must be set as the ID for an do event ', $event->getEventId(), EventInterface::EVENT_DO));
        }

        $this->events[ $event->getEventId() ] = $event;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvent($eventId)
    {
        if (array_key_exists($eventId, $this->events)) {
            return $this->events[$eventId];
        } else {
            return null;
        }
    }

    /**
     * @param  TransitionEventInterface $event
     * @throws DuplicateEventException
     */
    public function addTransitionEvent(TransitionEventInterface $event)
    {
        if (array_key_exists($event->getEventId(), $this->events)) {
            throw new DuplicateEventException(sprintf('The event "%s" already exists in the state "%s".', $event->getEventId(), $this->getStateId()));
        }

        $this->events[ $event->getEventId() ] = $event;
    }

    /**
     * {@inheritDoc}
     */
    public function getStateId()
    {
        return $this->stateId;
    }

    /**
     * {@inheritDoc}
     *
     * @since Method available since Release 2.1.0
     */
    public function isEndState()
    {
        foreach (array_values($this->events) as $event) {
            if ($event instanceof TransitionEventInterface && $event->isEndEvent()) {
                return true;
            }
        }

        return false;
    }
}
