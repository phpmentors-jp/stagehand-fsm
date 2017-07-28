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
     * @param string $eventId
     *
     * @return EventInterface|null
     */
    public function getTransitionEvent($eventId)
    {
        return $this->events[$eventId] ?? null;
    }

    /**
     * @param EventInterface $event
     */
    public function addTransitionEvent(EventInterface $event)
    {
        $this->events[$event->getEventId()] = $event;
    }
}
