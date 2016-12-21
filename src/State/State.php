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

use Stagehand\FSM\Event\EventCollection;
use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\Event\TransitionEventInterface;

/**
 * @since Class available since Release 0.1.0
 */
class State implements TransitionalStateInterface, \Serializable
{
    /**
     * @var string
     */
    protected $stateId;

    /**
     * @var EventCollection
     *
     * @since Property available since Release 2.2.0
     */
    protected $eventCollection;

    /**
     * {@inheritdoc}
     *
     * @since Method available since Release 2.2.0
     */
    public function serialize()
    {
        return serialize(get_object_vars($this));
    }

    /**
     * {@inheritdoc}
     *
     * @since Method available since Release 2.2.0
     */
    public function unserialize($serialized)
    {
        foreach (unserialize($serialized) as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * @param string $stateId
     */
    public function __construct($stateId)
    {
        $this->eventCollection = new EventCollection();
        $this->stateId = $stateId;
    }

    /**
     * @param EventInterface $event
     *
     * @throws InvalidEventException
     *
     * @since Method available since Release 2.0.0
     */
    public function setEntryEvent(EventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_ENTRY) {
            throw new InvalidEventException(sprintf('The event "%s" is not an entry event. "%s" must be set as the ID for an entry event ', $event->getEventId(), EventInterface::EVENT_ENTRY));
        }

        $this->eventCollection->add($event);
    }

    /**
     * @param EventInterface $event
     *
     * @throws InvalidEventException
     *
     * @since Method available since Release 2.0.0
     */
    public function setExitEvent(EventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_EXIT) {
            throw new InvalidEventException(sprintf('The event "%s" is not an exit event. "%s" must be set as the ID for an exit event ', $event->getEventId(), EventInterface::EVENT_EXIT));
        }

        $this->eventCollection->add($event);
    }

    /**
     * @param EventInterface $event
     *
     * @throws InvalidEventException
     *
     * @since Method available since Release 2.0.0
     */
    public function setDoEvent(EventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_DO) {
            throw new InvalidEventException(sprintf('The event "%s" is not a do event. "%s" must be set as the ID for an do event ', $event->getEventId(), EventInterface::EVENT_DO));
        }

        $this->eventCollection->add($event);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent($eventId)
    {
        return $this->eventCollection->get($eventId);
    }

    /**
     * @param TransitionEventInterface $event
     */
    public function addTransitionEvent(TransitionEventInterface $event)
    {
        $this->eventCollection->add($event);
    }

    /**
     * {@inheritdoc}
     */
    public function getStateId()
    {
        return $this->stateId;
    }
}
