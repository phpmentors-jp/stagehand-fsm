<?php
/*
 * Copyright (c) 2013, 2015 KUBO Atsuhiro <kubo@iteman.jp>,
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

/**
 * @since Class available since Release 2.0.0
 */
class StateMachineBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function provideActionSetters()
    {
        return array(
            array('setEntryAction'),
            array('setExitAction'),
            array('setActivity'),
        );
    }

    /**
     * @param string $actionSetter
     *
     * @test
     * @dataProvider provideActionSetters
     */
    public function raisesAnExceptionIfTheStateIsNotFoundWhenSettingAStateAction($actionSetter)
    {
        $stateMachineBuilder = new StateMachineBuilder();

        try {
            $stateMachineBuilder->{ $actionSetter }('foo', function (EventInterface $event, $payload, StateMachine $stateMachine) {});
        } catch (StateNotFoundException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @param string $actionSetter
     *
     * @test
     * @dataProvider provideActionSetters
     */
    public function raisesAnExceptionIfTheActionIsNotCallableWhenSettingAStateAction($actionSetter)
    {
        $stateMachineBuilder = new StateMachineBuilder();
        $stateMachineBuilder->addState('foo');

        try {
            $stateMachineBuilder->{ $actionSetter }('foo', 'bar', array($this, 'nonExistingMethod'));
        } catch (ActionNotCallableException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

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
     */
    public function raisesAnExceptionIfTheActionIsNotCallableWhenAddingATransition()
    {
        $stateMachineBuilder = new StateMachineBuilder();
        $stateMachineBuilder->addState('foo');
        $stateMachineBuilder->addState('baz');

        try {
            $stateMachineBuilder->addTransition('foo', 'bar', 'baz', array($this, 'nonExistingMethod'));
        } catch (ActionNotCallableException $e) {
            $this->assertThat($e->getMessage(), $this->stringStartsWith('The action'));

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @test
     */
    public function raisesAnExceptionIfTheGuardIsNotCallableWhenAddingATransition()
    {
        $stateMachineBuilder = new StateMachineBuilder();
        $stateMachineBuilder->addState('foo');
        $stateMachineBuilder->addState('baz');

        try {
            $stateMachineBuilder->addTransition('foo', 'bar', 'baz', null, array($this, 'nonExistingMethod'));
        } catch (ActionNotCallableException $e) {
            $this->assertThat($e->getMessage(), $this->stringStartsWith('The guard'));

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
