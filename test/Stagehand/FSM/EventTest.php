<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Copyright (c) 2006-2007, 2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2007, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\FSM;

/**
 * @package    Stagehand_FSM
 * @copyright  2006-2007, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getsTheName()
    {
        $event = new Event('foo');
        $this->assertEquals('foo', $event->getName());
    }

    /**
     * @test
     */
    public function setsTheNextState()
    {
        $event = new Event('foo');
        $event->setNextState('foo');
        $this->assertEquals('foo', $event->getNextState());
    }

    /**
     * @test
     */
    public function setsTheAction()
    {
        $event = new Event('foo');
        $event->setAction('foo');
        $this->assertEquals('foo', $event->getAction());
    }

    /**
     * @test
     */
    public function setsTheGuard()
    {
        $event = new Event('foo');
        $event->setGuard('foo');
        $this->assertEquals('foo', $event->getGuard());
    }

    /**
     * @test
     */
    public function evaluatesTheGuard()
    {
        $fsm = new FSM();
        $fsm->setName('bar');
        $payload = new \stdClass();
        $payload->name = 'baz';
        $fsm->setPayload($payload);
        $event = new Event('foo');
        $event->setGuard(function (FSM $fsm, $event, &$payload) { return true; });
        $this->assertTrue($event->evaluateGuard($fsm));
        $event->setGuard(function (FSM $fsm, $event, &$payload) { return false; });
        $this->assertFalse($event->evaluateGuard($fsm));
        $test = $this;
        $event->setGuard(function (FSM $fsm, $event, &$payload) use ($test)
        {
            $test->assertEquals('bar', $fsm->getName());
            $test->assertEquals('foo', $event->getName());
            $test->assertEquals('baz', $payload->name);
            return true;
        });
        $this->assertTrue($event->evaluateGuard($fsm));
    }

    /**
     * @test
     */
    public function invokesTheAction()
    {
        $barInvoked = false;
        $fsm = new FSM();
        $fsm->setName('bar');
        $payload = new \stdClass();
        $payload->name = 'baz';
        $fsm->setPayload($payload);
        $event = new Event('foo');
        $event->setAction(function (FSM $fsm, $event, &$payload) use (&$barInvoked)
        {
            $barInvoked = true;
        });
        $event->invokeAction($fsm);
        $this->assertTrue($barInvoked);
        $test = $this;
        $event->setAction(function (FSM $fsm, $event, &$payload) use ($test)
        {
            $test->assertEquals('bar', $fsm->getName());
            $test->assertEquals('foo', $event->getName());
            $test->assertEquals('baz', $payload->name);
            return true;
        });
        $event->invokeAction($fsm);
    }

    /**
     * @test
     */
    public function setsTransitionToHistoryMarker()
    {
        $event = new Event('foo');
        $event->setTransitionToHistoryMarker(true);
        $this->assertTrue($event->getTransitionToHistoryMarker());
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