<?php
/*
 * Copyright (c) 2013-2015 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @since Class available since Release 2.0.0
 */
class InitialState extends State
{
    /**
     * @var string
     *
     * @deprecated Deprecated since version 2.1.0, to be removed in 3.0.0.
     */
    protected $stateID = StateInterface::STATE_INITIAL;

    /**
     * @var \Stagehand\FSM\Event\TransitionEventInterface
     *
     * @deprecated Deprecated since version 2.2.0, to be removed in 3.0.0.
     */
    protected $transitionEvent;

    /**
     * {@inheritDoc}
     *
     * @since Method available since Release 2.2.0
     */
    public function __wakeup()
    {
        parent::__wakeup();

        if ($this->transitionEvent !== null) {
            $this->addTransitionEvent($this->transitionEvent);
        }
    }

    /**
     * @since Method available since Release 2.1.0
     */
    public function __construct()
    {
        parent::__construct(StateInterface::STATE_INITIAL);
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidEventException
     *
     * @since Method available since Release 2.2.0
     */
    public function addTransitionEvent(TransitionEventInterface $event)
    {
        if ($event->getEventId() != EventInterface::EVENT_START) {
            throw new InvalidEventException(sprintf('The transition event for the state "%s" should be "%s", "%s" is specified.', $this->getStateId(), EventInterface::EVENT_START, $event->getEventId()));
        }

        parent::addTransitionEvent($event);
    }
}
