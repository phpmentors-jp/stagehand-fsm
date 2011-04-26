<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Copyright (c) 2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\FSM;

/**
 * @package    Stagehand_FSM
 * @copyright  2006-2008, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
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
        $builder->setFirstState('locked');
        $builder->addState('foo');
        $builder->addState('bar');
        $fsm = $builder->getFSM();
        $this->assertInstanceOf('\Stagehand\FSM\IState', $fsm->getState('foo'));
        $this->assertEquals('foo', $fsm->getState('foo')->getName());
        $this->assertInstanceOf('\Stagehand\FSM\IState', $fsm->getState('bar'));
        $this->assertEquals('bar', $fsm->getState('bar')->getName());
    }

    /**
     * @test
     */
    public function setsTheFirstState()
    {
        $firstStateName = 'locked';
        $builder = new FSMBuilder();
        $builder->setFirstState($firstStateName);
        $fsm = $builder->getFSM();
        $fsm->start();
        $this->assertEquals($firstStateName, $fsm->getCurrentState()->getName());

        $builder = new FSMBuilder();
        $builder->setFirstState($firstStateName);
        $fsm = $builder->getFSM();
        $fsm->start();
        $this->assertEquals($firstStateName, $fsm->getCurrentState()->getName());
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
        $builder->setFirstState('locked');
        $builder->addTransition('locked', 'insertCoin', 'unlocked', function (FSM $fsm, Event $event, &$payload) use (&$unlockCalled)
        {
            $unlockCalled = true;
        });
        $builder->addTransition('unlocked', 'pass', 'locked', function (FSM $fsm, Event $event, &$payload) use (&$lockCalled)
        {
            $lockCalled = true;
        });
        $builder->addTransition('locked', 'pass', 'locked', function (FSM $fsm, Event $event, &$payload) use (&$alarmCalled)
        {
            $alarmCalled = true;
        });
        $builder->addTransition('unlocked', 'insertCoin', 'unlocked', function (FSM $fsm, Event $event, &$payload) use (&$thankCalled)
        {
            $thankCalled = true;
        });
        $fsm = $builder->getFSM();
        $fsm->start();

        $currentState = $fsm->triggerEvent('pass');
        $this->assertEquals('locked', $currentState->getName());
        $this->assertTrue($alarmCalled);

        $currentState = $fsm->triggerEvent('insertCoin');
        $this->assertEquals('unlocked', $currentState->getName());
        $this->assertTrue($unlockCalled);

        $currentState = $fsm->triggerEvent('insertCoin');
        $this->assertEquals('unlocked', $currentState->getName());
        $this->assertTrue($thankCalled);

        $currentState = $fsm->triggerEvent('pass');
        $this->assertEquals('locked', $currentState->getName());
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
        $builder->setFirstState('locked');
        $builder->addTransition('locked', 'insertCoin', 'unlocked', null, function (FSM $fsm, Event $event, &$payload) use ($maxNumberOfCoins, $numberOfCoins)
        {
            return $numberOfCoins <= $maxNumberOfCoins;
        });
        $builder->addTransition('unlocked', 'pass', 'locked');
        $builder->addTransition('locked', 'pass', 'locked');
        $builder->addTransition('unlocked', 'insertCoin', 'unlocked');
        $fsm = $builder->getFSM();
        $fsm->start();
        $currentState = $fsm->triggerEvent('insertCoin');
        $this->assertEquals('locked', $currentState->getName());
    }

    /**
     * @test
     */
    public function supportsExitAndEntryActions()
    {
        $entryActionForInitialCalled = false;
        $entryActionForLockedCalled = false;
        $builder = new FSMBuilder();
        $builder->setFirstState('locked');
        $builder->setExitAction(IState::STATE_INITIAL, function (FSM $fsm, Event $event, &$payload) use (&$entryActionForInitialCalled)
        {
            $entryActionForInitialCalled = true;
        });
        $builder->setEntryAction('locked', function (FSM $fsm, Event $event, &$payload) use (&$entryActionForLockedCalled)
        {
            $entryActionForLockedCalled = true;
        });
        $fsm = $builder->getFSM();
        $fsm->start();

        $this->assertTrue($entryActionForInitialCalled);
        $this->assertTrue($entryActionForLockedCalled);
        $this->assertEquals('locked', $fsm->getCurrentState()->getName());
    }

    /**
     * @test
     */
    public function setsTheName()
    {
        $fsm = new FSM('foo');
        $this->assertEquals('foo', $fsm->getName());
    }

    /**
     * @test
     */
    public function supportsNestedFsms()
    {
        $childBuilder = new FSMBuilder('play');
        $childBuilder->setFirstState('playing');
        $childBuilder->setActivity('playing', function (FSM $fsm, Event $event, &$payload)
        {
            ++$payload;
        });
        $childBuilder->addTransition('playing', 'pause', 'paused');
        $childBuilder->addTransition('paused', 'play', 'playing');
        $child = $childBuilder->getFSM();

        $payload = 0;
        $parentBuilder = new FSMBuilder();
        $parentBuilder->setPayload($payload);
        $parentBuilder->addFSM($child);
        $parentBuilder->setFirstState('stopped');
        $parentBuilder->setActivity('stopped', function (FSM $fsm, Event $event, &$payload)
        {
            ++$payload;
        });
        $parentBuilder->addTransition('stopped', 'start', 'play');
        $parentBuilder->addTransition('play', 'stop', 'stopped');
        $parent = $parentBuilder->getFSM();
        $parent->start();

        $this->assertEquals('stopped', $parent->getCurrentState()->getName());

        $child = $parent->triggerEvent('start');
        $this->assertEquals('play', $child->getName());
        $this->assertEquals('playing', $child->getCurrentState()->getName());
        $this->assertEquals(2, $payload);

        $currentStateOfChild = $child->triggerEvent('pause');
        $this->assertEquals('paused', $currentStateOfChild->getName());

        $currentStateOfChild = $child->triggerEvent('play');
        $this->assertEquals('playing', $currentStateOfChild->getName());
        $this->assertEquals('play', $parent->getCurrentState('play')->getName());

        $currentState = $parent->triggerEvent('stop');
        $this->assertEquals('stopped', $currentState->getName());
    }

    /**
     * @test
     */
    public function supportsNestedStates()
    {
        $entryActionForCCalled = false;
        $entryActionForSCalled = false;

        $childBuilder = new FSMBuilder('S');
        $childBuilder->setFirstState('C');
        $childBuilder->addTransition('C', 'w', 'B');
        $childBuilder->addTransition('B', 'x', 'C');
        $childBuilder->addTransition('B', 'q', IState::STATE_FINAL);
        $childBuilder->setEntryAction('C', function (FSM $fsm, Event $event, &$payload) use (&$entryActionForCCalled)
        {
            $entryActionForCCalled = true;
        });

        $parentBuilder = new FSMBuilder();
        $parentBuilder->setFirstState('A');
        $parentBuilder->addFSM($childBuilder->getFSM());
        $parentBuilder->addTransition('A', 'r', 'D');
        $parentBuilder->addTransition('A', 'y', 'S');
        $parentBuilder->addTransition('A', 'v', 'S');
        $parentBuilder->addTransition('S', 'z', 'A');
        $parentBuilder->setEntryAction('S', function (FSM $fsm, Event $event, &$payload) use (&$entryActionForSCalled)
        {
            $entryActionForSCalled = true;
        });
        $parent = $parentBuilder->getFSM();
        $parent->start();
        $this->assertEquals('A', $parent->getCurrentState()->getName());

        $parent->triggerEvent('v');
        $this->assertEquals('S', $parent->getCurrentState()->getName());
        $this->assertTrue($entryActionForSCalled);

        $child = $parent->getState('S');
        $this->assertEquals('C', $child->getCurrentState()->getName());
        $this->assertTrue($entryActionForCCalled);
    }

    /**
     * @test
     */
    public function supportsHistoryMarker()
    {
        $parentBuilder = $this->prepareWashingMachine();
        $parent = $parentBuilder->getFSM();
        $parent->start();
        $this->assertEquals('Running', $parent->getCurrentState()->getName());
        $child = $parent->getState('Running');

        $currentStateOfChild = $child->triggerEvent('w');
        $this->assertEquals('Rinsing', $currentStateOfChild->getName());

        $currentState = $parent->triggerEvent('powerCut');
        $this->assertEquals('PowerOff', $currentState->getName());

        $currentState = $parent->triggerEvent('restorePower');
        $this->assertEquals('Running', $currentState->getName());
        $this->assertEquals('Rinsing', $child->getCurrentState()->getName());

        $parent->triggerEvent('powerCut');
        $currentState = $parent->triggerEvent('reset');
        $this->assertEquals('Running', $currentState->getName());
        $this->assertEquals('Washing', $child->getCurrentState()->getName());
    }

    /**
     * @test
     */
    public function getsThePreviousState()
    {
        $builder = new FSMBuilder();
        $builder->setFirstState('Washing');
        $builder->addTransition('Washing', 'w', 'Rinsing');
        $builder->addTransition('Rinsing', 'r', 'Spinning');
        $fsm = $builder->getFSM();
        $fsm->start();
        $state = $fsm->getPreviousState();
        $this->assertInstanceOf('\Stagehand\FSM\IState', $state);
        $this->assertEquals(IState::STATE_INITIAL, $state->getName());

        $fsm->triggerEvent('w');
        $state = $fsm->getPreviousState();

        $this->assertInstanceOf('\Stagehand\FSM\IState', $state);
        $this->assertEquals('Washing', $state->getName());
    }

    /**
     * @test
     */
    public function invokesTheEntryActionOfTheParentStateBeforeTheEntryActionOfTheChildState()
    {
        $lastMarker = null;
        $parentBuilder = $this->prepareWashingMachine();
        $parentBuilder->setEntryAction('Running', function (FSM $fsm, Event $event, &$payload) use (&$lastMarker)
        {
            $lastMarker = 'Running';
        });
        $parent = $parentBuilder->getFSM();
        $childBuilder = new FSMBuilder($parent->getState('Running'));
        $childBuilder->setEntryAction('Washing', function (FSM $fsm, Event $event, &$payload) use (&$lastMarker)
        {
            $lastMarker = 'Washing';
        });
        $parent->start();
        $this->assertEquals('Washing', $lastMarker);
    }

    /**
     * @test
     */
    public function supportsActivity()
    {
        $washingCount = 0;
        $parent = $this->prepareWashingMachine()->getFSM();
        $childBuilder = new FSMBuilder($parent->getState('Running'));
        $childBuilder->setActivity('Washing', function (FSM $fsm, Event $event, &$payload) use (&$washingCount)
        {
            ++$washingCount;
        });
        $parent->start();

        $child = $childBuilder->getFSM();
        $state = $child->triggerEvent('put');
        $this->assertEquals(2, $washingCount);
        $this->assertEquals('Washing', $state->getName());

        $state = $child->triggerEvent('hit');
        $this->assertEquals(3, $washingCount);
        $this->assertEquals('Washing', $state->getName());
    }

    /**
     * @test
     */
    public function supportsPayloads()
    {
        $payload = new \stdClass();
        $payload->washingCount = 0;
        $parent = $this->prepareWashingMachine()->getFSM();
        $childBuilder = new FSMBuilder($parent->getState('Running'));
        $childBuilder->setPayload($payload);
        $childBuilder->setActivity('Washing', function (FSM $fsm, $event, &$payload)
        {
            ++$payload->washingCount;
        });
        $parent->start();

        $child = $childBuilder->getFSM();
        $state = $child->triggerEvent('put');
        $this->assertEquals(2, $payload->washingCount);
        $this->assertEquals('Washing', $state->getName());

        $state = $child->triggerEvent('hit');
        $this->assertEquals(3, $payload->washingCount);
        $this->assertEquals('Washing', $state->getName());
    }

    /**
     * @test
     */
    public function transitionsWhenAnEventIsTriggeredInAnAction()
    {
        $builder = new FSMBuilder();
        $builder->setFirstState('Washing');
        $test = $this;
        $builder->setEntryAction('Washing', function (FSM $fsm, $event, &$payload) use ($test)
        {
            $test->assertEquals('Washing', $fsm->getCurrentState()->getName());
            $test->assertEquals(IState::STATE_INITIAL, $fsm->getPreviousState()->getName());
            $fsm->triggerEvent('w');
        });
        $builder->addTransition('Washing', 'w', 'Rinsing', function (FSM $fsm, $event, &$payload) {});
        $builder->addTransition('Rinsing', 'r', 'Spinning');
        $fsm = $builder->getFSM();
        $fsm->start();
        $this->assertEquals('Rinsing', $fsm->getCurrentState()->getName());
        $this->assertEquals('Washing', $fsm->getPreviousState()->getName());
    }

    /**
     * @test
     * @expectedException \Stagehand\FSM\FSMAlreadyShutdownException
     */
    public function shutdownsTheFsmWhenTheStateReachesTheFinalState()
    {
        $finalizeCalled = false;
        $builder = new FSMBuilder();
        $builder->setFirstState('ending');
        $builder->addTransition('ending', Event::EVENT_END, IState::STATE_FINAL);
        $builder->setEntryAction(IState::STATE_FINAL, function (FSM $fsm, Event $event, &$payload) use (&$finalizeCalled)
        {
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
     * @since Method available since Release 1.5.0
     */
    public function checksWhetherAnEventIsProtectedOrNot()
    {
        $fsm = new FSM();
        $this->assertTrue($fsm->isProtectedEvent(Event::EVENT_ENTRY));
        $this->assertTrue($fsm->isProtectedEvent(Event::EVENT_EXIT));
        $this->assertTrue($fsm->isProtectedEvent(Event::EVENT_START));
        $this->assertTrue($fsm->isProtectedEvent(Event::EVENT_END));
        $this->assertTrue($fsm->isProtectedEvent(Event::EVENT_DO));
        $this->assertFalse($fsm->isProtectedEvent('foo'));
    }

    /**
     * @test
     * @since Method available since Release 1.5.0
     */
    public function checksWhetherAnStateIsProtectedOrNot()
    {
        $fsm = new FSM();
        $this->assertTrue($fsm->isProtectedState(IState::STATE_INITIAL));
        $this->assertTrue($fsm->isProtectedState(IState::STATE_FINAL));
        $this->assertFalse($fsm->isProtectedState('foo'));
    }

    /**
     * @test
     * @since Method available since Release 1.6.0
     */
    public function checksWhetherTheCurrentStateHasTheGivenEvent()
    {
        $builder = new FSMBuilder();
        $builder->setFirstState('Stop');
        $builder->addTransition('Stop', 'play', 'Playing');
        $builder->addTransition('Playing', 'stop', 'Stop');
        $fsm = $builder->getFSM();
        $fsm->start();

        $this->assertEquals('Stop', $fsm->getCurrentState()->getName());
        $this->assertTrue($fsm->hasEvent('play'));
        $this->assertFalse($fsm->hasEvent('stop'));

        $currentState = $fsm->triggerEvent('play');
        $this->assertEquals('Playing', $currentState->getName());
        $this->assertTrue($fsm->hasEvent('stop'));
        $this->assertFalse($fsm->hasEvent('play'));
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
        $builder->setFirstState('DisplayForm');
        $builder->setActivity('DisplayForm', function (FSM $fsm, $event, &$payload) use (&$activityForDisplayFormCallCount)
        {
            ++$activityForDisplayFormCallCount;
        });
        $builder->addTransition('DisplayForm', 'confirmForm', 'processConfirmForm', function (FSM $fsm, $event, &$payload) use (&$transitionActionForDisplayFormCallCount)
        {
            ++$transitionActionForDisplayFormCallCount;
            $fsm->queueEvent('goDisplayConfirmation');
        });
        $builder->addTransition('processConfirmForm', 'goDisplayConfirmation', 'DisplayConfirmation');
        $builder->setActivity('DisplayConfirmation', function (FSM $fsm, $event, &$payload) use (&$activityForDisplayConfirmationCallCount)
        {
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

    /**
     * @return \Stagehand\FSM\FSMBuilder
     */
    protected function prepareWashingMachine()
    {
        $childBuilder = new FSMBuilder('Running');
        $childBuilder->setFirstState('Washing');
        $childBuilder->addTransition('Washing', 'w', 'Rinsing');
        $childBuilder->addTransition('Rinsing', 'r', 'Spinning');

        $parentBuilder = new FSMBuilder();
        $parentBuilder->setFirstState('Running');
        $parentBuilder->addFSM($childBuilder->getFSM());
        $parentBuilder->addTransition('PowerOff', 'restorePower', 'Running', null, null, true);
        $parentBuilder->addTransition('Running', 'powerCut', 'PowerOff');
        $parentBuilder->addTransition('PowerOff', 'reset', 'Running');

        return $parentBuilder;
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
