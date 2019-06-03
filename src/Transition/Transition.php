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

namespace Stagehand\FSM\Transition;

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\State\TransitionalStateInterface;

/**
 * @since Class available since Release 3.0.0
 */
class Transition implements TransitionInterface
{
    /**
     * @var StateInterface
     */
    private $toState;

    /**
     * @var TransitionalStateInterface
     */
    private $fromState;

    /**
     * @var EventInterface
     */
    private $event;

    /**
     * @param StateInterface             $toState
     * @param TransitionalStateInterface $fromState
     * @param EventInterface             $event
     */
    public function __construct(StateInterface $toState, TransitionalStateInterface $fromState, EventInterface $event)
    {
        $this->toState = $toState;
        $this->fromState = $fromState;
        $this->event = $event;

        $this->fromState->addTransitionEvent($event);
    }

    /**
     * {@inheritdoc}
     */
    public function getToState(): StateInterface
    {
        return $this->toState;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromState(): TransitionalStateInterface
    {
        return $this->fromState;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent(): EventInterface
    {
        return $this->event;
    }
}
