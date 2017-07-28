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
use Stagehand\FSM\Token\TokenAwareTrait;

/**
 * @since Class available since Release 2.0.0
 */
class InitialState implements TransitionalStateInterface
{
    use TokenAwareTrait;

    /**
     * @var EventInterface
     *
     * @since Property available since Release 3.0.0
     */
    private $transitionEvent;

    /**
     * @var string
     *
     * @since Property available since Release 3.0.0
     */
    private $stateId;

    /**
     * @param string $stateId
     */
    public function __construct(string $stateId)
    {
        $this->stateId = $stateId;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransitionEvent($eventId)
    {
        if ($this->transitionEvent === null) {
            return null;
        } else {
            return $this->transitionEvent->getEventId() == $eventId ? $this->transitionEvent : null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStateId()
    {
        return $this->stateId;
    }

    /**
     * {@inheritdoc}
     */
    public function addTransitionEvent(EventInterface $event)
    {
        $this->transitionEvent = $event;
    }
}
