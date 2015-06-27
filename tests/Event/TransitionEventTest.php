<?php
/*
 * Copyright (c) 2013-2014 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @since Class available since Release 2.1.0
 */
class TransitionEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function isNotAnEndEventIfTheNextStateIsNotSet()
    {
        $event = new TransitionEvent('foo');
        $event->setNextState(\Phake::mock('Stagehand\FSM\State\StateInterface'));
        $result = $event->isEndEvent();

        $this->assertThat($result, $this->isFalse());
    }

    /**
     * @test
     */
    public function isAnEndEventIfTheNextStateIsTheFinalState()
    {
        $state = \Phake::mock('Stagehand\FSM\State\StateInterface');
        \Phake::when($state)->getStateId()->thenReturn(StateInterface::STATE_FINAL);
        $event = new TransitionEvent('foo');
        $event->setNextState($state);
        $result = $event->isEndEvent();

        $this->assertThat($result, $this->isTrue());
    }

    /**
     * @test
     */
    public function isNotAnEndEventIfTheNextStateIsNotTheFinalState()
    {
        $state = \Phake::mock('Stagehand\FSM\State\StateInterface');
        \Phake::when($state)->getStateId()->thenReturn('bar');
        $event = new TransitionEvent('foo');
        $event->setNextState($state);
        $result = $event->isEndEvent();

        $this->assertThat($result, $this->isFalse());
    }
}
