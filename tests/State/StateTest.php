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
}
