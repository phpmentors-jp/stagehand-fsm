<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/
 * @see        Stagehand_FSM_FSMState
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Stagehand/FSM/FSMState.php';
require_once 'Stagehand/FSM.php';

// {{{ Stagehand_FSM_FSMStateTestCase

/**
 * TestCase for Stagehand_FSM_FSMState
 *
 * @package    Stagehand_FSM
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/
 * @see        Stagehand_FSM_FSMState
 * @since      Class available since Release 0.1.0
 */
class Stagehand_FSM_FSMStateTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_fsmState;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        $fsm = &new Stagehand_FSM();
        $fsm->setName('play');
        $fsm->setFirstState('playing');
        $fsm->addTransition('playing', 'pause', 'paused');
        $fsm->addTransition('paused', 'play', 'playing');
        $this->_fsmState = &Stagehand_FSM_FSMState::wrap($fsm);
    }

    function tearDown()
    {
        $this->_fsmState = null;
    }

    function testWrapping()
    {
        $this->assertTrue(is_a($this->_fsmState, 'Stagehand_FSM_FSMState'));
        $this->assertEquals('play', $this->_fsmState->getName());

        $currentState = &$this->_fsmState->getCurrentState();

        $this->assertNull($currentState);
    }

    function testCreatingSpecialEventsInConstructor()
    {
        $entry = &$this->_fsmState->getEvent(STAGEHAND_FSM_EVENT_ENTRY);
        $exit  = &$this->_fsmState->getEvent(STAGEHAND_FSM_EVENT_EXIT);
        $do  = &$this->_fsmState->getEvent(STAGEHAND_FSM_EVENT_DO);

        $this->assertTrue(is_a($entry, 'STAGEHAND_FSM_EVENT'));
        $this->assertEquals(STAGEHAND_FSM_EVENT_ENTRY, $entry->getName());
        $this->assertTrue(is_a($exit, 'STAGEHAND_FSM_EVENT'));
        $this->assertEquals(STAGEHAND_FSM_EVENT_EXIT, $exit->getName());
        $this->assertTrue(is_a($do, 'STAGEHAND_FSM_EVENT'));
        $this->assertEquals(STAGEHAND_FSM_EVENT_DO, $do->getName());
    }

    function testAddingEvent()
    {
        $foo = &$this->_fsmState->addEvent('foo');
        $bar = &$this->_fsmState->addEvent('bar');

        $this->assertTrue(is_a($foo, 'STAGEHAND_FSM_EVENT'));
        $this->assertEquals('foo', $foo->getName());
        $this->assertTrue(is_a($bar, 'STAGEHAND_FSM_EVENT'));
        $this->assertEquals('bar', $bar->getName());
    }

    /**
     * @since Method available since Release 1.6.0
     */
    function testCheckingWhetherStateHasEvent()
    {
        $foo = &$this->_fsmState->addEvent('foo');
        $bar = &$this->_fsmState->addEvent('bar');

        $this->assertTrue($this->_fsmState->hasEvent('foo'));
        $this->assertTrue($this->_fsmState->hasEvent('bar'));
        $this->assertFalse($this->_fsmState->hasEvent('baz'));
    }

    /**#@-*/

    /**#@+
     * @access private
     */

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
