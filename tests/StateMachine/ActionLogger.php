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

namespace Stagehand\FSM\StateMachine;

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\Transition\ActionRunnerInterface;
use Stagehand\FSM\Transition\TransitionInterface;

/**
 * @since Class available since Release 3.0.0
 */
class ActionLogger implements ActionRunnerInterface, \ArrayAccess, \Countable
{
    /**
     * @var array
     */
    private $runActions = [];

    /**
     * {@inheritdoc}
     */
    public function run(EventInterface $event, $payload, StateMachineInterface $stateMachine, TransitionInterface $transition = null)
    {
        $this->runActions[] = [
            'stateMachine' => $stateMachine->getStateMachineId(),
            'state' => $transition === null ? $stateMachine->getCurrentState()->getStateId() : $transition->getFromState()->getStateId(),
            'event' => $event->getEventId(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->runActions[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->runActions[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->runActions[] = $value;
        } else {
            $this->runActions[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->runActions[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->runActions);
    }
}
