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
        $fsm = new FSM('locked');
        $foo = $fsm->addState('foo');
        $this->assertInstanceOf('\Stagehand\FSM\State', $foo);
        $this->assertEquals('foo', $foo->getName());

        $bar = $fsm->addState('bar');
        $this->assertInstanceOf('\Stagehand\FSM\State', $bar);
        $this->assertEquals('bar', $bar->getName());
    }

    /**
     * @test
     */
    public function setsTheFirstState()
    {
        $stateName = 'locked';
        $fsm = new FSM();
        $fsm->setFirstState($stateName);
        $fsm->start();
        $this->assertEquals($stateName, $fsm->getCurrentState()->getName());

        $fsm = new FSM($stateName);
        $fsm->start();
        $this->assertEquals($stateName, $fsm->getCurrentState()->getName());
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
        $fsm = new FSM('locked');
        $fsm->addTransition('locked', 'insertCoin', 'unlocked', function (FSM $fsm, Event $event, &$payload) use (&$unlockCalled)
        {
            $unlockCalled = true;
        });
        $fsm->addTransition('unlocked', 'pass', 'locked', function (FSM $fsm, Event $event, &$payload) use (&$lockCalled)
        {
            $lockCalled = true;
        });
        $fsm->addTransition('locked', 'pass', 'locked', function (FSM $fsm, Event $event, &$payload) use (&$alarmCalled)
        {
            $alarmCalled = true;
        });
        $fsm->addTransition('unlocked', 'insertCoin', 'unlocked', function (FSM $fsm, Event $event, &$payload) use (&$thankCalled)
        {
            $thankCalled = true;
        });
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
        $fsm = new FSM('locked');
        $fsm->addTransition('locked', 'insertCoin', 'unlocked', null, function (FSM $fsm, Event $event, &$payload) use ($maxNumberOfCoins, $numberOfCoins)
        {
            return $numberOfCoins <= $maxNumberOfCoins;
        });
        $fsm->addTransition('unlocked', 'pass', 'locked');
        $fsm->addTransition('locked', 'pass', 'locked');
        $fsm->addTransition('unlocked', 'insertCoin', 'unlocked');
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
        $fsm = new FSM('locked');
        $fsm->setExitAction(State::STATE_INITIAL, function (FSM $fsm, Event $event, &$payload) use (&$entryActionForInitialCalled)
        {
            $entryActionForInitialCalled = true;
        });
        $fsm->setEntryAction('locked', function (FSM $fsm, Event $event, &$payload) use (&$entryActionForLockedCalled)
        {
            $entryActionForLockedCalled = true;
        });
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
        $fsm = new FSM();
        $fsm->setName('foo');
        $this->assertEquals('foo', $fsm->getName());
    }

    /**
     * @test
     */
    public function supportsNestedFsms()
    {
        $child = new FSM();
        $child->setName('play');
        $child->setFirstState('playing');
        $child->addTransition('playing', 'pause', 'paused');
        $child->addTransition('paused', 'play', 'playing');

        $parent = new FSM();
        $parent->setFirstState('stopped');
        $parent->addFSM($child);
        $parent->addTransition('stopped', 'start', 'play');
        $parent->addTransition('play', 'stop', 'stopped');
        $parent->start();

        $this->assertEquals('stopped', $parent->getCurrentState()->getName());

        $child = $parent->triggerEvent('start');
        $this->assertEquals('play', $child->getName());
        $this->assertEquals('playing', $child->getCurrentState()->getName());

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

        $child = new FSM();
        $child->setName('S');
        $child->setFirstState('C');
        $child->addTransition('C', 'w', 'B');
        $child->addTransition('B', 'x', 'C');
        $child->addTransition('B', 'q', State::STATE_FINAL);
        $child->setEntryAction('C', function (FSM $fsm, Event $event, &$payload) use (&$entryActionForCCalled)
        {
            $entryActionForCCalled = true;
        });
        $parent = new FSM();
        $parent->setFirstState('A');
        $parent->addFSM($child);
        $parent->addTransition('A', 'r', 'D');
        $parent->addTransition('A', 'y', 'S');
        $parent->addTransition('A', 'v', 'S');
        $parent->addTransition('S', 'z', 'A');
        $parent->setEntryAction('S', function (FSM $fsm, Event $event, &$payload) use (&$entryActionForSCalled)
        {
            $entryActionForSCalled = true;
        });
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
        $parent = $this->prepareWashingMachine();
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
        $fsm = new FSM();
        $fsm->setFirstState('Washing');
        $fsm->addTransition('Washing', 'w', 'Rinsing');
        $fsm->addTransition('Rinsing', 'r', 'Spinning');
        $fsm->start();
        $state = $fsm->getPreviousState();
        $this->assertInstanceOf('\Stagehand\FSM\State', $state);
        $this->assertEquals(State::STATE_INITIAL, $state->getName());

        $fsm->triggerEvent('w');
        $state = $fsm->getPreviousState();

        $this->assertInstanceOf('\Stagehand\FSM\State', $state);
        $this->assertEquals('Washing', $state->getName());
    }

    /**
     * @test
     */
    public function invokesTheEntryActionOfTheParentStateBeforeTheEntryActionOfTheChildState()
    {
        $lastMarker = null;
        $parent = $this->prepareWashingMachine();
        $parent->setEntryAction('Running', function (FSM $fsm, Event $event, &$payload) use (&$lastMarker)
        {
            $lastMarker = 'Running';
        });
        $child = $parent->getState('Running');
        $child->setEntryAction('Washing', function (FSM $fsm, Event $event, &$payload) use (&$lastMarker)
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
        $parent = $this->prepareWashingMachine();
        $child = $parent->getState('Running');
        $child->setActivity('Washing', function (FSM $fsm, Event $event, &$payload) use (&$washingCount)
        {
            ++$washingCount;
        });
        $parent->start();

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
        $parent = $this->prepareWashingMachine();
        $child = $parent->getState('Running');
        $child->setPayload($payload);
        $child->setActivity('Washing', function (FSM $fsm, $event, &$payload)
        {
            ++$payload->washingCount;
        });
        $parent->start();

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
        $fsm = new FSM();
        $fsm->setFirstState('Washing');
        $test = $this;
        $fsm->setEntryAction('Washing', function (FSM $fsm, $event, &$payload) use ($test)
        {
            $test->assertEquals('Washing', $fsm->getCurrentState()->getName());
            $test->assertEquals(State::STATE_INITIAL, $fsm->getPreviousState()->getName());
            $fsm->triggerEvent('w');
        });
        $fsm->addTransition('Washing', 'w', 'Rinsing', function (FSM $fsm, $event, &$payload) {});
        $fsm->addTransition('Rinsing', 'r', 'Spinning');
        $fsm->start();
        $this->assertEquals('Rinsing', $fsm->getCurrentState()->getName());
        $this->assertEquals('Washing', $fsm->getPreviousState()->getName());
    }

    /**
     * @test
     * @expectedException \Stagehand\FSM\AlreadyShutdownException
     */
    public function shutdownsTheFsmWhenTheStateReachesTheFinalState()
    {
        $finalizeCalled = false;
        $fsm = new FSM();
        $fsm->setFirstState('ending');
        $fsm->addTransition('ending', Event::EVENT_END, State::STATE_FINAL);
        $fsm->setEntryAction(State::STATE_FINAL, function (FSM $fsm, Event $event, &$payload) use (&$finalizeCalled)
        {
            $finalizeCalled = true;
        });
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
        $this->assertTrue($fsm->isProtectedState(State::STATE_INITIAL));
        $this->assertTrue($fsm->isProtectedState(State::STATE_FINAL));
        $this->assertFalse($fsm->isProtectedState('foo'));
    }

    /**
     * @test
     * @since Method available since Release 1.6.0
     */
    public function checksWhetherTheCurrentStateHasTheGivenEvent()
    {
        $fsm = new FSM();
        $fsm->setFirstState('Stop');
        $fsm->addTransition('Stop', 'play', 'Playing');
        $fsm->addTransition('Playing', 'stop', 'Stop');
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

        $fsm = new FSM();
        $fsm->setFirstState('DisplayForm');
        $fsm->setActivity('DisplayForm', function (FSM $fsm, $event, &$payload) use (&$activityForDisplayFormCallCount)
        {
            ++$activityForDisplayFormCallCount;
        });
        $fsm->addTransition('DisplayForm', 'confirmForm', 'processConfirmForm', function (FSM $fsm, $event, &$payload) use (&$transitionActionForDisplayFormCallCount)
        {
            ++$transitionActionForDisplayFormCallCount;
            $fsm->queueEvent('goDisplayConfirmation');
        });
        $fsm->addTransition('processConfirmForm', 'goDisplayConfirmation', 'DisplayConfirmation');
        $fsm->setActivity('DisplayConfirmation', function (FSM $fsm, $event, &$payload) use (&$activityForDisplayConfirmationCallCount)
        {
            ++$activityForDisplayConfirmationCallCount;
        });
        $fsm->start();

        $this->assertEquals(1, $activityForDisplayFormCallCount);

        $fsm->triggerEvent('confirmForm');

        $this->assertEquals(1, $activityForDisplayFormCallCount);
        $this->assertEquals(1, $transitionActionForDisplayFormCallCount);
        $this->assertEquals(1, $activityForDisplayConfirmationCallCount);
    }

    protected function prepareWashingMachine()
    {
        $child = new FSM();
        $child->setName('Running');
        $child->setFirstState('Washing');
        $child->addTransition('Washing', 'w', 'Rinsing');
        $child->addTransition('Rinsing', 'r', 'Spinning');

        $parent = new FSM();
        $parent->setFirstState('Running');
        $parent->addFSM($child);
        $parent->addTransition('PowerOff', 'restorePower', 'Running', null, null, true);
        $parent->addTransition('Running', 'powerCut', 'PowerOff');
        $parent->addTransition('PowerOff', 'reset', 'Running');

        return $parent;
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
