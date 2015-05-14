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

use Stagehand\FSM\Event\TransitionEvent;

/**
 * @since Class available since Release 2.0.0
 */
class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function raisesAnExceptionIfTheEventIdIsInvalidWhenSettingTheEntryEvent()
    {
        $state = new State('foo');

        try {
            $state->setEntryEvent(new TransitionEvent('bar'));
        } catch (InvalidEventException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @test
     */
    public function raisesAnExceptionIfTheEventIdIsInvalidWhenSettingTheExitEvent()
    {
        $state = new State('foo');

        try {
            $state->setExitEvent(new TransitionEvent('bar'));
        } catch (InvalidEventException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @test
     */
    public function raisesAnExceptionIfTheEventIdIsInvalidWhenSettingTheDoEvent()
    {
        $state = new State('foo');

        try {
            $state->setDoEvent(new TransitionEvent('bar'));
        } catch (InvalidEventException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @test
     *
     * @since Method available since Release 2.1.0
     */
    public function isNotAnEndStateIfNoEndEventsAreFound()
    {
        $event = \Phake::mock('Stagehand\FSM\Event\TransitionEventInterface');
        \Phake::when($event)->getEventId()->thenReturn('bar');
        \Phake::when($event)->isEndEvent()->thenReturn(false);
        $state = new State('foo');
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
    public function isAnEndStateIfAtLeastATransitionEventIsAnEndEvent()
    {
        $event1 = \Phake::mock('Stagehand\FSM\Event\TransitionEventInterface');
        \Phake::when($event1)->getEventId()->thenReturn('bar');
        \Phake::when($event1)->isEndEvent()->thenReturn(false);
        $event2 = \Phake::mock('Stagehand\FSM\Event\TransitionEventInterface');
        \Phake::when($event2)->getEventId()->thenReturn('baz');
        \Phake::when($event2)->isEndEvent()->thenReturn(true);
        $state = new State('foo');
        $state->addTransitionEvent($event1);
        $state->addTransitionEvent($event2);
        $result = $state->isEndState();

        $this->assertThat($result, $this->isTrue());
        \Phake::verify($event1)->isEndEvent();
        \Phake::verify($event2)->isEndEvent();
    }
}
