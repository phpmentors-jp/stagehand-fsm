<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2006-2007, 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2007, 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\FSM;

/**
 * A sub-class of the FSM class that has capability of the State class.
 *
 * @package    Stagehand_FSM
 * @copyright  2006-2007, 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class FSMState extends FSM implements StateInterface
{
    /**
     * @var \Stagehand\FSM\StateInterface
     */
    protected $state;

    /**
     * @return array
     */
    public function __sleep()
    {
        return array_merge(parent::__sleep(), array('state'));
    }

    /**
     * Wraps a FSM object up with a FSMState object.
     *
     * @param  \Stagehand\FSM\FSM            $fsm
     * @return \Stagehand\FSM\StateInterface
     */
    public static function wrap($fsm)
    {
        return new self($fsm);
    }

    public function getEvent($eventID)
    {
        return $this->state->getEvent($eventID);
    }

    public function addEvent(Event $event)
    {
        $this->state->addEvent($event);
    }

    public function hasEvent($eventID)
    {
        return $this->state->hasEvent($eventID);
    }

    /**
     * @param \Stagehand\FSM\FSM $fsm
     */
    public function __construct(FSM $fsm)
    {
        parent::__construct();
        $this->currentStateID = $fsm->currentStateID;
        $this->previousStateID = $fsm->previousStateID;
        $this->states = $fsm->states;
        $this->fsmID = $fsm->fsmID;
        $this->payload = $fsm->payload;
        $this->state = new State($fsm->fsmID);
    }

    /**
     * {@inheritDoc}
     */
    public function getStateID()
    {
        return $this->state->getStateID();
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
