<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @author     MIYAI Fumihiko <fumichz@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://iteman.typepad.jp/stagehand/
 * @see        Stagehand_FSM
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Stagehand/FSM.php';
require_once 'Stagehand/FSM/Error.php';
require_once 'GateKeeper.php';

// {{{ Stagehand_FSMTestCase

/**
 * TestCase for Stagehand_FSM
 *
 * @package    Stagehand_FSM
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @author     MIYAI Fumihiko <fumichz@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/stagehand/
 * @see        Stagehand_FSM
 * @since      Class available since Release 0.1.0
 */
class Stagehand_FSMTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_keeper;
    var $_maxNumberOfCoins;
    var $_numberOfCoins;
    var $_invokeEntryOfSCalled = false;
    var $_invokeEntryOfCCalled = false;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        Stagehand_FSM_Error::pushCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
        $this->_keeper = &new GateKeeper();
    }

    function tearDown()
    {
        $this->_keeper = null;
        Stagehand_FSM_Error::clearErrors();
        Stagehand_FSM_Error::popCallback();
     }

    function testAddingState()
    {
        $fsm = &new Stagehand_FSM('locked');
        $foo = &$fsm->addState('foo');
        $bar = &$fsm->addState('bar');

        $this->assertTrue(is_a($foo, 'Stagehand_FSM_State'));
        $this->assertEquals('foo', $foo->getName());
        $this->assertTrue(is_a($bar, 'Stagehand_FSM_State'));
        $this->assertEquals('bar', $bar->getName());
    }

    function testSettingFirstState()
    {
        $stateName = 'locked';
        $fsm = &new Stagehand_FSM();
        $fsm->setFirstState($stateName);
        $fsm->start();

        $currentState = &$fsm->getCurrentState();
        $this->assertEquals($stateName, $currentState->getName());

        $fsm = &new Stagehand_FSM($stateName);
        $fsm->start();

        $currentState = &$fsm->getCurrentState();
        $this->assertEquals($stateName, $currentState->getName());
    }

    function testTriggeringEvent()
    {
        $this->_keeper->reset();
        $fsm = &new Stagehand_FSM('locked');
        $fsm->addTransition('locked', 'insertCoin', 'unlocked',
                            array(&$this->_keeper, 'unlock')
                            );
        $fsm->addTransition('unlocked', 'pass', 'locked',
                            array(&$this->_keeper, 'lock')
                            );
        $fsm->addTransition('locked', 'pass', 'locked',
                            array(&$this->_keeper, 'alarm')
                            );
        $fsm->addTransition('unlocked', 'insertCoin', 'unlocked',
                            array(&$this->_keeper, 'thank')
                            );
        $fsm->start();

        $currentState = &$fsm->triggerEvent('pass');

        $this->assertEquals('locked', $currentState->getName());
        $this->assertTrue($this->_keeper->alarmCalled);

        $currentState = &$fsm->triggerEvent('insertCoin');

        $this->assertEquals('unlocked', $currentState->getName());
        $this->assertTrue($this->_keeper->unlockCalled);

        $currentState = &$fsm->triggerEvent('insertCoin');

        $this->assertEquals('unlocked', $currentState->getName());
        $this->assertTrue($this->_keeper->thankCalled);

        $currentState = &$fsm->triggerEvent('pass');

        $this->assertEquals('locked', $currentState->getName());
        $this->assertTrue($this->_keeper->lockCalled);
    }

    function testGuard()
    {
        $this->_keeper->reset();
        $fsm = &new Stagehand_FSM('locked');
        $fsm->addTransition('locked', 'insertCoin', 'unlocked',
                            array(&$this->_keeper, 'unlock'),
                            array(&$this, 'validateNumberOfCoins')
                            );
        $fsm->addTransition('unlocked', 'pass', 'locked',
                            array(&$this->_keeper, 'lock')
                            );
        $fsm->addTransition('locked', 'pass', 'locked',
                            array(&$this->_keeper, 'alarm')
                            );
        $fsm->addTransition('unlocked', 'insertCoin', 'unlocked',
                            array(&$this->_keeper, 'thank')
                            );
        $fsm->start();

        $this->_maxNumberOfCoins = 10;
        $this->_numberOfCoins = 11;
        $currentState = &$fsm->triggerEvent('insertCoin');

        $this->assertEquals('locked', $currentState->getName());
    }

    function validateNumberOfCoins()
    {
        return $this->_numberOfCoins <= $this->_maxNumberOfCoins;
    }

    function testExitAndEntryActions()
    {
        $this->_keeper->reset();
        $fsm = &new Stagehand_FSM('locked');
        $fsm->setExitAction(STAGEHAND_FSM_STATE_INITIAL,
                            array(&$this->_keeper, 'hello')
                            );
        $fsm->setEntryAction('locked',
                             array(&$this->_keeper, 'helloLocked')
                             );
        $fsm->start();

        $this->assertTrue($this->_keeper->helloCalled);
        $this->assertTrue($this->_keeper->helloLockedCalled);

        $currentState = &$fsm->getCurrentState();

        $this->assertEquals('locked', $currentState->getName());
    }

    function testSettingName()
    {
        $fsm = &new Stagehand_FSM();
        $fsm->setName('foo');

        $this->assertEquals('foo', $fsm->getName());
    }

    function testNestedFSM()
    {
        $child = &new Stagehand_FSM();
        $child->setName('play');
        $child->setFirstState('playing');
        $child->addTransition('playing', 'pause', 'paused');
        $child->addTransition('paused', 'play', 'playing');

        $parent = &new Stagehand_FSM();
        $parent->setFirstState('stopped');
        $parent->addFSM($child);
        $parent->addTransition('stopped', 'start', 'play');
        $parent->addTransition('play', 'stop', 'stopped');
        $parent->start();

        $currentState = &$parent->getCurrentState();

        $this->assertEquals('stopped', $currentState->getName());

        $child = &$parent->triggerEvent('start');

        $this->assertEquals('play', $child->getName());

        $currentStateOfChild = &$child->getCurrentState();

        $this->assertEquals('playing', $currentStateOfChild->getName());

        $currentStateOfChild = &$child->triggerEvent('pause');

        $this->assertEquals('paused', $currentStateOfChild->getName());

        $currentStateOfChild = &$child->triggerEvent('play');

        $this->assertEquals('playing', $currentStateOfChild->getName());

        $currentState = &$parent->getCurrentState('play');

        $this->assertEquals('play', $currentState->getName());

        $currentState = &$parent->triggerEvent('stop');

        $this->assertEquals('stopped', $currentState->getName());
    }

    function testNestedState()
    {
        $child = &new Stagehand_FSM();
        $child->setName('S');
        $child->setFirstState('C');
        $child->addTransition('C', 'w', 'B');
        $child->addTransition('B', 'x', 'C');
        $child->addTransition('B', 'q', STAGEHAND_FSM_STATE_FINAL);
        $child->setEntryAction('C', array(&$this, 'invokeEntryOfC'));

        $parent = &new Stagehand_FSM();
        $parent->setFirstState('A');
        $parent->addFSM($child);
        $parent->addTransition('A', 'r', 'D');
        $parent->addTransition('A', 'y', 'S');
        $parent->addTransition('A', 'v', 'S');
        $parent->addTransition('S', 'z', 'A');
        $parent->setEntryAction('S', array(&$this, 'invokeEntryOfS'));
        $parent->start();

        $currentState = &$parent->getCurrentState();

        $this->assertEquals('A', $currentState->getName());

        $parent->triggerEvent('v');

        $currentState = &$parent->getCurrentState();

        $this->assertEquals('S', $currentState->getName());
        $this->assertTrue($this->_invokeEntryOfSCalled);

        $child = &$parent->getState('S');

        $currentStateOfChild = &$child->getCurrentState();

        $this->assertEquals('C', $currentStateOfChild->getName());
        $this->assertTrue($this->_invokeEntryOfCCalled);
    }

    function invokeEntryOfS()
    {
        $this->_invokeEntryOfSCalled = true;
    }

    function invokeEntryOfC()
    {
        $this->_invokeEntryOfCCalled = true;
    }

    function testHistoryMarker()
    {
        $parent = &$this->_prepareWashingMachine();
        $parent->start();

        $currentState = &$parent->getCurrentState();

        $this->assertEquals('Running', $currentState->getName());

        $child = &$parent->getState('Running');
        $currentStateOfChild = &$child->triggerEvent('w');

        $this->assertEquals('Rinsing', $currentStateOfChild->getName());

        $currentState = &$parent->triggerEvent('powerCut');

        $this->assertEquals('PowerOff', $currentState->getName());

        $currentState = &$parent->triggerEvent('restorePower');

        $this->assertEquals('Running', $currentState->getName());

        $currentStateOfChild = &$child->getCurrentState();

        $this->assertEquals('Rinsing', $currentStateOfChild->getName());

        $parent->triggerEvent('powerCut');
        $currentState = &$parent->triggerEvent('reset');

        $this->assertEquals('Running', $currentState->getName());

        $currentStateOfChild = &$child->getCurrentState();

        $this->assertEquals('Washing', $currentStateOfChild->getName());
    }

    function testGettingPreviousState()
    {
        $fsm = &new Stagehand_FSM();
        $fsm->setFirstState('Washing');
        $fsm->addTransition('Washing', 'w', 'Rinsing');
        $fsm->addTransition('Rinsing', 'r', 'Spinning');
        $fsm->start();

        $state = &$fsm->getPreviousState();

        $this->assertTrue(is_a($state, 'Stagehand_FSM_State'));
        $this->assertEquals(STAGEHAND_FSM_STATE_INITIAL, $state->getName());

        $fsm->triggerEvent('w');
        $state = &$fsm->getPreviousState();

        $this->assertTrue(is_a($state, 'Stagehand_FSM_State'));
        $this->assertEquals('Washing', $state->getName());
    }

    function testEntryActionOfParentStateInvokeBeforeEntryActionOfChildState()
    {
        $parent = &$this->_prepareWashingMachine();
        $parent->setEntryAction('Running', array(&$this, 'markWithRunning'));
        $child = &$parent->getState('Running');
        $child->setEntryAction('Washing', array(&$this, 'markWithWashing'));
        $parent->start();

        $this->assertEquals('Washing', $this->_lastMarker);

        unset($this->_lastMarker);
    }

    function markWithWashing()
    {
        $this->_lastMarker = 'Washing';
    }

    function markWithRunning()
    {
        $this->_lastMarker = 'Running';
    }

    function testActivity()
    {
        $this->_washingCount = 0;
        $parent = &$this->_prepareWashingMachine();
        $child = &$parent->getState('Running');
        $child->setActivity('Washing', array(&$this, 'increaseWashingCount'));
        $parent->start();

        $state = &$child->triggerEvent('put');

        $this->assertEquals(2, $this->_washingCount);
        $this->assertEquals('Washing', $state->getName());

        $state = &$child->triggerEvent('hit');

        $this->assertEquals(3, $this->_washingCount);
        $this->assertEquals('Washing', $state->getName());

        unset($this->_washingCount);
    }

    function increaseWashingCount()
    {
        ++$this->_washingCount;
    }

    function testPayload()
    {
        $parent = &$this->_prepareWashingMachine();
        $payload = &new stdClass();
        $payload->washingCount = 0;
        $child = &$parent->getState('Running');
        $child->setPayload($payload);
        $child->setActivity('Washing',
                            array(&$this, 'increaseWashingCountWithPayload')
                            );
        $parent->start();

        $state = &$child->triggerEvent('put');

        $this->assertEquals(2, $payload->washingCount);
        $this->assertEquals('Washing', $state->getName());

        $state = &$child->triggerEvent('hit');

        $this->assertEquals(3, $payload->washingCount);
        $this->assertEquals('Washing', $state->getName());
    }

    function increaseWashingCountWithPayload(&$fsm, &$event, &$payload)
    {
        ++$payload->washingCount;
    }

    function testCorrectTransitionsWhenEventsAreTriggeredInAction()
    {
        $fsm = &new Stagehand_FSM();
        $fsm->setFirstState('Washing');
        $fsm->setEntryAction('Washing',
                             array(&$this, 'triggerEventInEntryAction')
                             );
        $fsm->addTransition('Washing', 'w', 'Rinsing',
                            array(&$this, 'triggerEventInTransitionAction')
                            );
        $fsm->addTransition('Rinsing', 'r', 'Spinning');
        $fsm->start();

        $currentState = &$fsm->getCurrentState();

        $this->assertEquals('Rinsing', $currentState->getName());

        $previousState = &$fsm->getPreviousState();

        $this->assertEquals('Washing', $previousState->getName());
    }

    function triggerEventInEntryAction(&$fsm, &$event, &$payload)
    {
        $currentState = &$fsm->getCurrentState();

        $this->assertEquals('Washing', $currentState->getName());

        $previousState = &$fsm->getPreviousState();

        $this->assertEquals(STAGEHAND_FSM_STATE_INITIAL,
                            $previousState->getName()
                            );

        $fsm->triggerEvent('w');
    }

    function testShutdown()
    {
        Stagehand_FSM_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $GLOBALS['finalizeCalled'] = false;

        $fsm = &new Stagehand_FSM();
        $fsm->setFirstState('ending');
        $fsm->addTransition('ending', STAGEHAND_FSM_EVENT_END, STAGEHAND_FSM_STATE_FINAL);
        $fsm->setEntryAction(STAGEHAND_FSM_STATE_FINAL, array(&$this, 'finalize'));
        $fsm->start();

        $fsm->triggerEvent(STAGEHAND_FSM_EVENT_END);

        $this->assertTrue($GLOBALS['finalizeCalled']);
        $this->assertFalse(Stagehand_FSM_Error::hasErrors('exception'));

        $fsm->triggerEvent('foo');
        $error = Stagehand_FSM_Error::pop();

        $this->assertEquals(STAGEHAND_FSM_ERROR_ALREADY_SHUTDOWN, $error['code']);

        unset($GLOBALS['finalizeCalled']);
        Stagehand_FSM_Error::popCallback();
    }

    function finalize()
    {
        $GLOBALS['finalizeCalled'] = true;
    }

    /**
     * @since Method available since Release 1.5.0
     */
    function testProtectedEvents()
    {
        $fsm = &new Stagehand_FSM();

        $this->assertTrue($fsm->isProtectedEvent(STAGEHAND_FSM_EVENT_ENTRY));
        $this->assertTrue($fsm->isProtectedEvent(STAGEHAND_FSM_EVENT_EXIT));
        $this->assertTrue($fsm->isProtectedEvent(STAGEHAND_FSM_EVENT_START));
        $this->assertTrue($fsm->isProtectedEvent(STAGEHAND_FSM_EVENT_END));
        $this->assertTrue($fsm->isProtectedEvent(STAGEHAND_FSM_EVENT_DO));

        $this->assertFalse($fsm->isProtectedEvent('foo'));
    }

    /**
     * @since Method available since Release 1.5.0
     */
    function testProtectedStates()
    {
        $fsm = &new Stagehand_FSM();

        $this->assertTrue($fsm->isProtectedState(STAGEHAND_FSM_STATE_INITIAL));
        $this->assertTrue($fsm->isProtectedState(STAGEHAND_FSM_STATE_FINAL));

        $this->assertFalse($fsm->isProtectedState('foo'));
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function &_prepareWashingMachine()
    {
        $child = &new Stagehand_FSM();
        $child->setName('Running');
        $child->setFirstState('Washing');
        $child->addTransition('Washing', 'w', 'Rinsing');
        $child->addTransition('Rinsing', 'r', 'Spinning');

        $parent = &new Stagehand_FSM();
        $parent->setFirstState('Running');
        $parent->addFSM($child);
        $parent->addTransition('PowerOff', 'restorePower', 'Running',
                               null, null, true
                               );
        $parent->addTransition('Running', 'powerCut', 'PowerOff');
        $parent->addTransition('PowerOff', 'reset', 'Running');

        return $parent;
    }

    /**#@-*/

    // }}}
}

// }}}

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
?>
