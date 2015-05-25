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

namespace Stagehand\FSM\Event;

use PHPMentors\DomainKata\Entity\EntityCollectionInterface;
use PHPMentors\DomainKata\Entity\EntityInterface;

/**
 * @since Class available since Release 2.2.0
 */
class EventCollection implements EntityCollectionInterface
{
    /**
     * @var array
     */
    private $events;

    /**
     * @param array $events
     */
    public function __construct(array $events = array())
    {
        $this->events = $events;
    }

    /**
     * {@inheritDoc}
     */
    public function add(EntityInterface $entity)
    {
        assert($entity instanceof EventInterface);

        /* @var $entity EventInterface */
        $this->events[$entity->getEventId()] = $entity;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->events)) {
            return $this->events[$key];
        } else {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function remove(EntityInterface $entity)
    {
        assert($entity instanceof EventInterface);

        /* @var $entity EventInterface */
        if (array_key_exists($entity->getEventId(), $this->events)) {
            unset($this->events[$entity->getEventId()]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->events);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->events);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->events;
    }
}
