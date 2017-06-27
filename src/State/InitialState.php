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

use Stagehand\FSM\Event\TransitionEventInterface;
use Stagehand\FSM\Token\TokenAwareTrait;

/**
 * @since Class available since Release 2.0.0
 */
class InitialState implements TransitionalStateInterface
{
    use TokenAwareTrait;

    /**
     * @var TransitionEventInterface
     *
     * @since Property available since Release 3.0.0
     */
    private $transitionEvent;

    /**
     * {@inheritdoc}
     */
    public function getEvent($eventId)
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
        return StateInterface::STATE_INITIAL;
    }

    /**
     * {@inheritdoc}
     */
    public function addTransitionEvent(TransitionEventInterface $event)
    {
        $this->transitionEvent = $event;
    }
}
