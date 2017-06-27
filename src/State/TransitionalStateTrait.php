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
    use StateActionTrait;

    /**
     * @var array
     */
    protected $events = [];

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
