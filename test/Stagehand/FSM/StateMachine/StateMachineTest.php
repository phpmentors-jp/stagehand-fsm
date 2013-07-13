<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\FSM\StateMachine;

use Stagehand\FSM\Event\Event;
use Stagehand\FSM\State\StateInterface;

/**
 * @package    Stagehand_FSM
 * @copyright  2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class StateMachineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function addsAState()
    {
        $builder = new StateMachineBuilder();
        $builder->addState('locked');
        $builder->addState('foo');
        $builder->addState('bar');
        $builder->setStartState('locked');
        $stateMachine = $builder->getStateMachine();
        $this->assertInstanceOf('\Stagehand\FSM\State\StateInterface', $stateMachine->getState('foo'));
        $this->assertEquals('foo', $stateMachine->getState('foo')->getStateID());
        $this->assertInstanceOf('\Stagehand\FSM\State\StateInterface', $stateMachine->getState('bar'));
        $this->assertEquals('bar', $stateMachine->getState('bar')->getStateID());
    }

    /**
     * @test
     */
    public function setsTheFirstState()
    {
        $firstStateID = 'locked';
        $builder = new StateMachineBuilder();
        $builder->addState($firstStateID);
        $builder->setStartState($firstStateID);
        $stateMachine = $builder->getStateMachine();
        $stateMachine->start();
        $this->assertEquals($firstStateID, $stateMachine->getCurrentState()->getStateID());

        $builder = new StateMachineBuilder();
        $builder->addState($firstStateID);
        $builder->setStartState($firstStateID);
        $stateMachine = $builder->getStateMachine();
        $stateMachine->start();
        $this->assertEquals($firstStateID, $stateMachine->getCurrentState()->getStateID());
    }

    /**
     * @test
     */
    public function triggersAnEvent()
    {
        $unlockCalled = false;
        $lockCalled = false;
        $alarmCalled = false;
        $thankCalled = false;
        $builder = new StateMachineBuilder();
        $builder->addState('locked');
        $builder->addState('unlocked');
        $builder->setStartState('locked');
        $builder->addTransition('locked', 'insertCoin', 'unlocked', function (Event $event, $payload, StateMachine $stateMachine) use (&$unlockCalled) {
            $unlockCalled = true;
        });
        $builder->addTransition('unlocked', 'pass', 'locked', function (Event $event, $payload, StateMachine $stateMachine) use (&$lockCalled) {
            $lockCalled = true;
        });
        $builder->addTransition('locked', 'pass', 'locked', function (Event $event, $payload, StateMachine $stateMachine) use (&$alarmCalled) {
            $alarmCalled = true;
        });
        $builder->addTransition('unlocked', 'insertCoin', 'unlocked', function (Event $event, $payload, StateMachine $stateMachine) use (&$thankCalled) {
            $thankCalled = true;
        });
        $stateMachine = $builder->getStateMachine();
        $stateMachine->start();

        $currentState = $stateMachine->triggerEvent('pass');
        $this->assertEquals('locked', $currentState->getStateID());
        $this->assertTrue($alarmCalled);

        $currentState = $stateMachine->triggerEvent('insertCoin');
        $this->assertEquals('unlocked', $currentState->getStateID());
        $this->assertTrue($unlockCalled);

        $currentState = $stateMachine->triggerEvent('insertCoin');
        $this->assertEquals('unlocked', $currentState->getStateID());
        $this->assertTrue($thankCalled);

        $currentState = $stateMachine->triggerEvent('pass');
        $this->assertEquals('locked', $currentState->getStateID());
        $this->assertTrue($lockCalled);
    }

    /**
     * @test
     */
    public function supportsGuards()
    {
        $maxNumberOfCoins = 10;
        $numberOfCoins = 11;
        $builder = new StateMachineBuilder();
        $builder->addState('locked');
        $builder->addState('unlocked');
        $builder->setStartState('locked');
        $builder->addTransition('locked', 'insertCoin', 'unlocked', null, function (Event $event, $payload, StateMachine $stateMachine) use ($maxNumberOfCoins, $numberOfCoins) {
            return $numberOfCoins <= $maxNumberOfCoins;
        });
        $builder->addTransition('unlocked', 'pass', 'locked');
        $builder->addTransition('locked', 'pass', 'locked');
        $builder->addTransition('unlocked', 'insertCoin', 'unlocked');
        $stateMachine = $builder->getStateMachine();
        $stateMachine->start();
        $currentState = $stateMachine->triggerEvent('insertCoin');
        $this->assertEquals('locked', $currentState->getStateID());
    }

    /**
     * @test
     */
    public function supportsExitAndEntryActions()
    {
        $entryActionForInitialCalled = false;
        $entryActionForLockedCalled = false;
        $builder = new StateMachineBuilder();
        $builder->addState('locked');
        $builder->setStartState('locked');
        $builder->setExitAction(StateInterface::STATE_INITIAL, function (Event $event, $payload, StateMachine $stateMachine) use (&$entryActionForInitialCalled) {
            $entryActionForInitialCalled = true;
        });
        $builder->setEntryAction('locked', function (Event $event, $payload, StateMachine $stateMachine) use (&$entryActionForLockedCalled) {
            $entryActionForLockedCalled = true;
        });
        $stateMachine = $builder->getStateMachine();
        $stateMachine->start();

        $this->assertTrue($entryActionForInitialCalled);
        $this->assertTrue($entryActionForLockedCalled);
        $this->assertEquals('locked', $stateMachine->getCurrentState()->getStateID());
    }

    /**
     * @test
     */
    public function setsTheId()
    {
        $stateMachine = new StateMachine('foo');
        $this->assertEquals('foo', $stateMachine->getStateMachineID());
    }

    /**
     * @test
     */
    public function getsThePreviousState()
    {
        $builder = new StateMachineBuilder();
        $builder->addState('Washing');
        $builder->addState('Rinsing');
        $builder->addState('Spinning');
        $builder->setStartState('Washing');
        $builder->addTransition('Washing', 'w', 'Rinsing');
        $builder->addTransition('Rinsing', 'r', 'Spinning');
        $stateMachine = $builder->getStateMachine();
        $stateMachine->start();
        $state = $stateMachine->getPreviousState();
        $this->assertInstanceOf('\Stagehand\FSM\State\StateInterface', $state);
        $this->assertEquals(StateInterface::STATE_INITIAL, $state->getStateID());

        $stateMachine->triggerEvent('w');
        $state = $stateMachine->getPreviousState();

        $this->assertInstanceOf('\Stagehand\FSM\State\StateInterface', $state);
        $this->assertEquals('Washing', $state->getStateID());
    }

    /**
     * @test
     */
    public function transitionsWhenAnEventIsTriggeredInAnAction()
    {
        $builder = new StateMachineBuilder();
        $builder->addState('Washing');
        $builder->addState('Rinsing');
        $builder->addState('Spinning');
        $builder->setStartState('Washing');
        $test = $this;
        $builder->setEntryAction('Washing', function ($event, $payload, StateMachine $stateMachine) use ($test) {
            $test->assertEquals('Washing', $stateMachine->getCurrentState()->getStateID());
            $test->assertEquals(StateInterface::STATE_INITIAL, $stateMachine->getPreviousState()->getStateID());
            $stateMachine->triggerEvent('w');
        });
        $builder->addTransition('Washing', 'w', 'Rinsing', function ($event, $payload, StateMachine $stateMachine) {});
        $builder->addTransition('Rinsing', 'r', 'Spinning');
        $stateMachine = $builder->getStateMachine();
        $stateMachine->start();
        $this->assertEquals('Rinsing', $stateMachine->getCurrentState()->getStateID());
        $this->assertEquals('Washing', $stateMachine->getPreviousState()->getStateID());
    }

    /**
     * @test
     * @expectedException \Stagehand\FSM\StateMachine\StateMachineAlreadyShutdownException
     */
    public function shutdownsTheStateMachineWhenTheStateReachesTheFinalState()
    {
        $finalizeCalled = false;
        $builder = new StateMachineBuilder();
        $builder->addState('ending');
        $builder->addState(StateInterface::STATE_FINAL);
        $builder->setStartState('ending');
        $builder->addTransition('ending', Event::EVENT_END, StateInterface::STATE_FINAL);
        $builder->setEntryAction(StateInterface::STATE_FINAL, function (Event $event, $payload, StateMachine $stateMachine) use (&$finalizeCalled) {
            $finalizeCalled = true;
        });
        $stateMachine = $builder->getStateMachine();
        $stateMachine->start();
        $stateMachine->triggerEvent(Event::EVENT_END);
        $this->assertTrue($finalizeCalled);
        $stateMachine->triggerEvent('foo');
    }

    /**
     * @test
     * @since Method available since Release 1.6.0
     */
    public function checksWhetherTheCurrentStateHasTheGivenEvent()
    {
        $builder = new StateMachineBuilder();
        $builder->addState('Stop');
        $builder->addState('Playing');
        $builder->setStartState('Stop');
        $builder->addTransition('Stop', 'play', 'Playing');
        $builder->addTransition('Playing', 'stop', 'Stop');
        $stateMachine = $builder->getStateMachine();
        $stateMachine->start();

        $this->assertEquals('Stop', $stateMachine->getCurrentState()->getStateID());
        $this->assertTrue($stateMachine->getCurrentState()->hasEvent('play'));
        $this->assertFalse($stateMachine->getCurrentState()->hasEvent('stop'));

        $currentState = $stateMachine->triggerEvent('play');
        $this->assertEquals('Playing', $currentState->getStateID());
        $this->assertTrue($stateMachine->getCurrentState()->hasEvent('stop'));
        $this->assertFalse($stateMachine->getCurrentState()->hasEvent('play'));
    }

    /**
     * @test
     * @since Method available since Release 1.7.0
     */
    public function invokesTheActivityOnlyOnceWhenAnStateIsUpdated()
    {
        $activityForDisplayFormCallCount = 0;
        $transitionActionForDisplayFormCallCount = 0;
        $activityForDisplayConfirmationCallCount = 0;

        $builder = new StateMachineBuilder();
        $builder->addState('DisplayForm');
        $builder->addState('processConfirmForm');
        $builder->addState('DisplayConfirmation');
        $builder->setStartState('DisplayForm');
        $builder->setActivity('DisplayForm', function ($event, $payload, StateMachine $stateMachine) use (&$activityForDisplayFormCallCount) {
            ++$activityForDisplayFormCallCount;
        });
        $builder->addTransition('DisplayForm', 'confirmForm', 'processConfirmForm', function ($event, $payload, StateMachine $stateMachine) use (&$transitionActionForDisplayFormCallCount) {
            ++$transitionActionForDisplayFormCallCount;
            $stateMachine->queueEvent('goDisplayConfirmation');
        });
        $builder->addTransition('processConfirmForm', 'goDisplayConfirmation', 'DisplayConfirmation');
        $builder->setActivity('DisplayConfirmation', function ($event, $payload, StateMachine $stateMachine) use (&$activityForDisplayConfirmationCallCount) {
            ++$activityForDisplayConfirmationCallCount;
        });
        $stateMachine = $builder->getStateMachine();
        $stateMachine->start();

        $this->assertEquals(1, $activityForDisplayFormCallCount);

        $stateMachine->triggerEvent('confirmForm');

        $this->assertEquals(1, $activityForDisplayFormCallCount);
        $this->assertEquals(1, $transitionActionForDisplayFormCallCount);
        $this->assertEquals(1, $activityForDisplayConfirmationCallCount);
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
