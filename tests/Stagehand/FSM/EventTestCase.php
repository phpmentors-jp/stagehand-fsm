<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Stagehand/FSM/Event.php';

// {{{ Stagehand_FSM_EventTestCase

/**
 * TestCase for Stagehand_FSM_Event
 *
 * @package    Stagehand_FSM
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_FSM_EventTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    function testGettingName()
    {
        $event = &new Stagehand_FSM_Event('foo');

        $this->assertEquals('foo', $event->getName());
    }

    function testSettingNextState()
    {
        $event = &new Stagehand_FSM_Event('foo');
        $event->setNextState('foo');

        $this->assertEquals('foo', $event->getNextState());
    }

    function testSettingAction()
    {
        $event = &new Stagehand_FSM_Event('foo');
        $event->setAction('foo');

        $this->assertEquals('foo', $event->getAction());
    }

    function testSettingGuard()
    {
        $event = &new Stagehand_FSM_Event('foo');
        $event->setGuard('foo');

        $this->assertEquals('foo', $event->getGuard());
    }

    function testEvaluatingGuard()
    {
        $fsm = &new Stagehand_FSM_EventTestCaseMockFSM();
        $fsm->name = 'bar';
        $fsm->payload = &new stdClass();
        $fsm->payload->name = 'baz';
        $event = &new Stagehand_FSM_Event('foo');
        $event->setGuard(array(&$this, 'returnTrue'));

        $this->assertTrue($event->evaluateGuard($fsm));

        $event->setGuard(array(&$this, 'returnFalse'));

        $this->assertFalse($event->evaluateGuard($fsm));

        $event->setGuard(array(&$this, 'assertValidArgs'));

        $this->assertTrue($event->evaluateGuard($fsm));
    }

    function testInvokingAction()
    {
        $fsm = &new Stagehand_FSM_EventTestCaseMockFSM();
        $fsm->name = 'bar';
        $fsm->payload = &new stdClass();
        $fsm->payload->name = 'baz';
        $event = &new Stagehand_FSM_Event('foo');
        $event->setAction(array(&$this, 'bar'));
        $event->invokeAction($fsm);

        $this->assertTrue($this->_barInvoked);

        $event->setAction(array(&$this, 'assertValidArgs'));
        $event->invokeAction($fsm);

        unset($this->_barInvoked);
    }

    function returnTrue()
    {
        return true;
    }

    function returnFalse()
    {
        return false;
    }

    function bar()
    {
        $this->_barInvoked = true;
    }

    function assertValidArgs(&$fsm, &$event, &$payload)
    {
        $this->assertEquals('bar', $fsm->name);
        $this->assertEquals('foo', $event->getName());
        $this->assertEquals('baz', $payload->name);

        return true;
    }

    function testSettingTransitionToHistoryMarker()
    {
        $event = &new Stagehand_FSM_Event('foo');
        $event->setTransitionToHistoryMarker(true);

        $this->assertTrue($event->getTransitionToHistoryMarker());
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    // }}}
}

// }}}

class Stagehand_FSM_EventTestCaseMockFSM
{
    var $name;
    var $payload;
    function &getPayload()
    {
        return $this->payload;
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
?>
