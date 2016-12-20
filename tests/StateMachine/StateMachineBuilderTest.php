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

/**
 * @since Class available since Release 2.0.0
 */
class StateMachineBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function raisesAnExceptionIfTheSourceStateIsNotFoundWhenAddingATransition()
    {
        $stateMachineBuilder = new StateMachineBuilder();
        $stateMachineBuilder->addState('baz');

        try {
            $stateMachineBuilder->addTransition('foo', 'bar', 'baz');
        } catch (StateNotFoundException $e) {
            $this->assertThat($e->getMessage(), $this->stringContains('"foo"'));

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @test
     */
    public function raisesAnExceptionIfTheDestinationStateIsNotFoundWhenAddingATransition()
    {
        $stateMachineBuilder = new StateMachineBuilder();
        $stateMachineBuilder->addState('foo');

        try {
            $stateMachineBuilder->addTransition('foo', 'bar', 'baz');
        } catch (StateNotFoundException $e) {
            $this->assertThat($e->getMessage(), $this->stringContains('"baz"'));

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @test
     *
     * @since Method available since Release 2.1.0
     */
    public function setsTheStateMachineObject()
    {
        $stateMachine1 = \Phake::mock('Stagehand\FSM\StateMachine\StateMachineInterface');
        $stateMachineBuilder = new StateMachineBuilder($stateMachine1);
        $stateMachine2 = $stateMachineBuilder->getStateMachine();

        $this->assertThat($stateMachine2, $this->identicalTo($stateMachine1));
    }
}
