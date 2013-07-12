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

namespace Stagehand\FSM;

/**
 * @package    Stagehand_FSM
 * @copyright  2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class FSMTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function addsAState()
    {
        $builder = new FSMBuilder();
        $builder->setStartState('locked');
        $builder->addState('foo');
        $builder->addState('bar');
        $fsm = $builder->getFSM();
        $this->assertInstanceOf('\Stagehand\FSM\StateInterface', $fsm->getState('foo'));
        $this->assertEquals('foo', $fsm->getState('foo')->getStateID());
        $this->assertInstanceOf('\Stagehand\FSM\StateInterface', $fsm->getState('bar'));
        $this->assertEquals('bar', $fsm->getState('bar')->getStateID());
    }

    /**
     * @test
     */
    public function setsTheFirstState()
    {
        $firstStateID = 'locked';
        $builder = new FSMBuilder();
        $builder->setStartState($firstStateID);
        $fsm = $builder->getFSM();
        $fsm->start();
        $this->assertEquals($firstStateID, $fsm->getCurrentState()->getStateID());

        $builder = new FSMBuilder();
        $builder->setStartState($firstStateID);
        $fsm = $builder->getFSM();
        $fsm->start();
        $this->assertEquals($firstStateID, $fsm->getCurrentState()->getStateID());
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
        $builder = new FSMBuilder();
        $builder->setStartState('locked');
        $builder->addTransition('locked', 'insertCoin', 'unlocked', function (Event $event, $payload, FSM $fsm) use (&$unlockCalled) {
            $unlockCalled = true;
        });
        $builder->addTransition('unlocked', 'pass', 'locked', function (Event $event, $payload, FSM $fsm) use (&$lockCalled) {
            $lockCalled = true;
        });
        $builder->addTransition('locked', 'pass', 'locked', function (Event $event, $payload, FSM $fsm) use (&$alarmCalled) {
            $alarmCalled = true;
        });
        $builder->addTransition('unlocked', 'insertCoin', 'unlocked', function (Event $event, $payload, FSM $fsm) use (&$thankCalled) {
            $thankCalled = true;
        });
        $fsm = $builder->getFSM();
        $fsm->start();

        $currentState = $fsm->triggerEvent('pass');
        $this->assertEquals('locked', $currentState->getStateID());
        $this->assertTrue($alarmCalled);

        $currentState = $fsm->triggerEvent('insertCoin');
        $this->assertEquals('unlocked', $currentState->getStateID());
        $this->assertTrue($unlockCalled);

        $currentState = $fsm->triggerEvent('insertCoin');
        $this->assertEquals('unlocked', $currentState->getStateID());
        $this->assertTrue($thankCalled);

        $currentState = $fsm->triggerEvent('pass');
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
        $builder = new FSMBuilder();
        $builder->setStartState('locked');
        $builder->addTransition('locked', 'insertCoin', 'unlocked', null, function (Event $event, $payload, FSM $fsm) use ($maxNumberOfCoins, $numberOfCoins) {
            return $numberOfCoins <= $maxNumberOfCoins;
        });
        $builder->addTransition('unlocked', 'pass', 'locked');
        $builder->addTransition('locked', 'pass', 'locked');
        $builder->addTransition('unlocked', 'insertCoin', 'unlocked');
        $fsm = $builder->getFSM();
        $fsm->start();
        $currentState = $fsm->triggerEvent('insertCoin');
        $this->assertEquals('locked', $currentState->getStateID());
    }

    /**
     * @test
     */
    public function supportsExitAndEntryActions()
    {
        $entryActionForInitialCalled = false;
        $entryActionForLockedCalled = false;
        $builder = new FSMBuilder();
        $builder->setStartState('locked');
        $builder->setExitAction(StateInterface::STATE_INITIAL, function (Event $event, $payload, FSM $fsm) use (&$entryActionForInitialCalled) {
            $entryActionForInitialCalled = true;
        });
        $builder->setEntryAction('locked', function (Event $event, $payload, FSM $fsm) use (&$entryActionForLockedCalled) {
            $entryActionForLockedCalled = true;
        });
        $fsm = $builder->getFSM();
        $fsm->start();

        $this->assertTrue($entryActionForInitialCalled);
        $this->assertTrue($entryActionForLockedCalled);
        $this->assertEquals('locked', $fsm->getCurrentState()->getStateID());
    }

    /**
     * @test
     */
    public function setsTheId()
    {
        $fsm = new FSM('foo');
        $this->assertEquals('foo', $fsm->getFSMID());
    }

    /**
     * @test
     */
    public function getsThePreviousState()
    {
        $builder = new FSMBuilder();
        $builder->setStartState('Washing');
        $builder->addTransition('Washing', 'w', 'Rinsing');
        $builder->addTransition('Rinsing', 'r', 'Spinning');
        $fsm = $builder->getFSM();
        $fsm->start();
        $state = $fsm->getPreviousState();
        $this->assertInstanceOf('\Stagehand\FSM\StateInterface', $state);
        $this->assertEquals(StateInterface::STATE_INITIAL, $state->getStateID());

        $fsm->triggerEvent('w');
        $state = $fsm->getPreviousState();

        $this->assertInstanceOf('\Stagehand\FSM\StateInterface', $state);
        $this->assertEquals('Washing', $state->getStateID());
    }

    /**
     * @test
     */
    public function transitionsWhenAnEventIsTriggeredInAnAction()
    {
        $builder = new FSMBuilder();
        $builder->setStartState('Washing');
        $test = $this;
        $builder->setEntryAction('Washing', function ($event, $payload, FSM $fsm) use ($test) {
            $test->assertEquals('Washing', $fsm->getCurrentState()->getStateID());
            $test->assertEquals(StateInterface::STATE_INITIAL, $fsm->getPreviousState()->getStateID());
            $fsm->triggerEvent('w');
        });
        $builder->addTransition('Washing', 'w', 'Rinsing', function ($event, $payload, FSM $fsm) {});
        $builder->addTransition('Rinsing', 'r', 'Spinning');
        $fsm = $builder->getFSM();
        $fsm->start();
        $this->assertEquals('Rinsing', $fsm->getCurrentState()->getStateID());
        $this->assertEquals('Washing', $fsm->getPreviousState()->getStateID());
    }

    /**
     * @test
     * @expectedException \Stagehand\FSM\FSMAlreadyShutdownException
     */
    public function shutdownsTheFsmWhenTheStateReachesTheFinalState()
    {
        $finalizeCalled = false;
        $builder = new FSMBuilder();
        $builder->setStartState('ending');
        $builder->addTransition('ending', Event::EVENT_END, StateInterface::STATE_FINAL);
        $builder->setEntryAction(StateInterface::STATE_FINAL, function (Event $event, $payload, FSM $fsm) use (&$finalizeCalled) {
            $finalizeCalled = true;
        });
        $fsm = $builder->getFSM();
        $fsm->start();
        $fsm->triggerEvent(Event::EVENT_END);
        $this->assertTrue($finalizeCalled);
        $fsm->triggerEvent('foo');
    }

    /**
     * @test
     * @since Method available since Release 1.6.0
     */
    public function checksWhetherTheCurrentStateHasTheGivenEvent()
    {
        $builder = new FSMBuilder();
        $builder->setStartState('Stop');
        $builder->addTransition('Stop', 'play', 'Playing');
        $builder->addTransition('Playing', 'stop', 'Stop');
        $fsm = $builder->getFSM();
        $fsm->start();

        $this->assertEquals('Stop', $fsm->getCurrentState()->getStateID());
        $this->assertTrue($fsm->getCurrentState()->hasEvent('play'));
        $this->assertFalse($fsm->getCurrentState()->hasEvent('stop'));

        $currentState = $fsm->triggerEvent('play');
        $this->assertEquals('Playing', $currentState->getStateID());
        $this->assertTrue($fsm->getCurrentState()->hasEvent('stop'));
        $this->assertFalse($fsm->getCurrentState()->hasEvent('play'));
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

        $builder = new FSMBuilder();
        $builder->setStartState('DisplayForm');
        $builder->setActivity('DisplayForm', function ($event, $payload, FSM $fsm) use (&$activityForDisplayFormCallCount) {
            ++$activityForDisplayFormCallCount;
        });
        $builder->addTransition('DisplayForm', 'confirmForm', 'processConfirmForm', function ($event, $payload, FSM $fsm) use (&$transitionActionForDisplayFormCallCount) {
            ++$transitionActionForDisplayFormCallCount;
            $fsm->queueEvent('goDisplayConfirmation');
        });
        $builder->addTransition('processConfirmForm', 'goDisplayConfirmation', 'DisplayConfirmation');
        $builder->setActivity('DisplayConfirmation', function ($event, $payload, FSM $fsm) use (&$activityForDisplayConfirmationCallCount) {
            ++$activityForDisplayConfirmationCallCount;
        });
        $fsm = $builder->getFSM();
        $fsm->start();

        $this->assertEquals(1, $activityForDisplayFormCallCount);

        $fsm->triggerEvent('confirmForm');

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
