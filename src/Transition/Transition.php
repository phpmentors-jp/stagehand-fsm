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

use Stagehand\FSM\Event\TransitionEventInterface;
use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\State\TransitionalStateInterface;
use Stagehand\FSM\Token\Token;

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
     * @var TransitionEventInterface
     */
    private $event;

    /**
     * @var Token|null
     */
    private $token;

    /**
     * @param StateInterface             $toState
     * @param TransitionalStateInterface $fromState
     * @param TransitionEventInterface   $event
     */
    public function __construct(StateInterface $toState, TransitionalStateInterface $fromState, TransitionEventInterface $event)
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
    public function getEvent(): TransitionEventInterface
    {
        return $this->event;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(Token $token)
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        $token = $this->token;
        $this->token = null;

        return $token;
    }
}
