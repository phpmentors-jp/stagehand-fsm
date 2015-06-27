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
use Stagehand\FSM\Event\TransitionEvent;

/**
 * @since Class available since Release 2.1.0
 */
class InitialStateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function isNotAnEndStateIfTheTransitionEventIsNotSet()
    {
        $state = new InitialState();
        $result = $state->isEndState();

        $this->assertThat($result, $this->isFalse());
    }

    /**
     * @test
     */
    public function isNotAnEndStateIfTheTransitionEventIsNotAnEndEvent()
    {
        $event = \Phake::mock('Stagehand\FSM\Event\TransitionEventInterface');
        \Phake::when($event)->getEventId()->thenReturn(EventInterface::EVENT_START);
        \Phake::when($event)->isEndEvent()->thenReturn(false);
        $state = new InitialState();
        $state->addTransitionEvent($event);
        $result = $state->isEndState();

        $this->assertThat($result, $this->isFalse());
        \Phake::verify($event)->isEndEvent();
    }

    /**
     * @test
     *
     * @since Method available since Release 2.1.0
     */
    public function isAnEndStateIfTheTransitionEventIsAnEndEvent()
    {
        $event = \Phake::mock('Stagehand\FSM\Event\TransitionEventInterface');
        \Phake::when($event)->getEventId()->thenReturn(EventInterface::EVENT_START);
        \Phake::when($event)->isEndEvent()->thenReturn(true);
        $state = new InitialState();
        $state->addTransitionEvent($event);
        $result = $state->isEndState();

        \Phake::verify($event)->isEndEvent();
    }

    /**
     * @test
     *
     * @since Method available since Release 2.2.0
     */
    public function raisesAnExceptionIfTheEventIdIsInvalidWhenSettingTheTransitionEvent()
    {
        $state = new InitialState();

        try {
            $state->addTransitionEvent(new TransitionEvent('foo'));
        } catch (InvalidEventException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }
}
