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

namespace Stagehand\FSM\Event;

use Stagehand\FSM\State\StateInterface;

/**
 * @since Class available since Release 0.1.0
 */
class TransitionEvent implements TransitionEventInterface
{
    /**
     * @var string
     */
    private $eventId;

    /**
     * @var StateInterface
     */
    private $nextState;

    /**
     * @var callback
     */
    private $action;

    /**
     * @var callback
     */
    private $guard;

    /**
     * @param string $eventId
     */
    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * Sets the next state to the event.
     *
     * @param StateInterface $state
     */
    public function setNextState(StateInterface $state)
    {
        $this->nextState = $state;
    }

    /**
     * Sets the action for the event.
     *
     * @param callback $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Sets the guard for the event.
     *
     * @param callback $guard
     */
    public function setGuard($guard)
    {
        $this->guard = $guard;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * {@inheritDoc}
     */
    public function getNextState()
    {
        return $this->nextState;
    }

    /**
     * {@inheritDoc}
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * {@inheritDoc}
     */
    public function getGuard()
    {
        return $this->guard;
    }

    /**
     * {@inheritDoc}
     *
     * @since Method available since Release 2.1.0
     */
    public function isEndEvent()
    {
        return $this->getNextState() !== null && $this->getNextState()->getStateId() == StateInterface::STATE_FINAL;
    }
}
