<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
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
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\FSM;

/**
 * @package    Stagehand_FSM
 * @copyright  2006-2007, 2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class FSMStateTest extends \PHPUnit_Framework_TestCase
{
    protected $fsmState;

    protected function setUp()
    {
        $builder = new FSMBuilder('play');
        $builder->setFirstState('playing');
        $builder->addTransition('playing', 'pause', 'paused');
        $builder->addTransition('paused', 'play', 'playing');
        $this->fsmState = FSMState::wrap($builder->getFSM());
    }

    /**
     * @test
     */
    public function wrapsAFsmWithAState()
    {
        $this->assertInstanceOf('\Stagehand\FSM\IState', $this->fsmState);
        $this->assertEquals('play', $this->fsmState->getName());
        $this->assertNull($this->fsmState->getCurrentState());
    }

    /**
     * @test
     */
    public function createsSpecialEventsInTheConstructor()
    {
        $entry = $this->fsmState->getEvent(Event::EVENT_ENTRY);
        $this->assertInstanceOf('\Stagehand\FSM\Event', $entry);
        $this->assertEquals(Event::EVENT_ENTRY, $entry->getName());

        $exit  = $this->fsmState->getEvent(Event::EVENT_EXIT);
        $this->assertInstanceOf('\Stagehand\FSM\Event', $exit);
        $this->assertEquals(Event::EVENT_EXIT, $exit->getName());

        $do  = $this->fsmState->getEvent(Event::EVENT_DO);
        $this->assertInstanceOf('\Stagehand\FSM\Event', $do);
        $this->assertEquals(Event::EVENT_DO, $do->getName());
    }

    /**
     * @test
     */
    public function addsAnEvent()
    {
        $this->fsmState->addEvent(new Event('foo'));
        $this->assertTrue($this->fsmState->hasEvent('foo'));

        $this->fsmState->addEvent(new Event('bar'));
        $this->assertTrue($this->fsmState->hasEvent('bar'));
    }

    /**
     * @test
     * @since Method available since Release 1.6.0
     */
    public function checksWhetherTheStateHasTheGivenEvent()
    {
        $this->fsmState->addEvent(new Event('foo'));
        $this->fsmState->addEvent(new Event('bar'));
        $this->assertTrue($this->fsmState->hasEvent('foo'));
        $this->assertTrue($this->fsmState->hasEvent('bar'));
        $this->assertFalse($this->fsmState->hasEvent('baz'));
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
