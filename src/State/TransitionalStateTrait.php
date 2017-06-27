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

namespace Stagehand\FSM\State;

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\Event\TransitionEventInterface;

/**
 * @since Trait available since Release 3.0.0
 */
trait TransitionalStateTrait
{
    /**
     * @var array
     */
    protected $events = [];

    /**
     * @param EventInterface $event
     *
     * @throws InvalidEventException
     */
    public function setEntryEvent(EventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_ENTRY) {
            throw new InvalidEventException(sprintf('The event "%s" is not an entry event. "%s" must be set as the ID for an entry event ', $event->getEventId(), EventInterface::EVENT_ENTRY));
        }

        $this->events[$event->getEventId()] = $event;
    }

    /**
     * @param EventInterface $event
     *
     * @throws InvalidEventException
     */
    public function setExitEvent(EventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_EXIT) {
            throw new InvalidEventException(sprintf('The event "%s" is not an exit event. "%s" must be set as the ID for an exit event ', $event->getEventId(), EventInterface::EVENT_EXIT));
        }

        $this->events[$event->getEventId()] = $event;
    }

    /**
     * @param EventInterface $event
     *
     * @throws InvalidEventException
     */
    public function setDoEvent(EventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_DO) {
            throw new InvalidEventException(sprintf('The event "%s" is not a do event. "%s" must be set as the ID for an do event ', $event->getEventId(), EventInterface::EVENT_DO));
        }

        $this->events[$event->getEventId()] = $event;
    }

    /**
     * Gets the event according to the given ID.
     *
     * @param string $eventId
     *
     * @return EventInterface|null
     */
    public function getEvent($eventId)
    {
        return $this->events[$eventId] ?? null;
    }

    /**
     * @param TransitionEventInterface $event
     */
    public function addTransitionEvent(TransitionEventInterface $event)
    {
        $this->events[$event->getEventId()] = $event;
    }
}
