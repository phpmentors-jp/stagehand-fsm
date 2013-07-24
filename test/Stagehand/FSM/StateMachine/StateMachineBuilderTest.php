<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2013 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_FSM
 * @copyright  2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.0.0
 */

namespace Stagehand\FSM\StateMachine;

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\StateMachine\ActionNotCallableException;
use Stagehand\FSM\StateMachine\StateNotFoundException;

/**
 * @package    Stagehand_FSM
 * @copyright  2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.0.0
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
            array('setActivity')
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
}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
