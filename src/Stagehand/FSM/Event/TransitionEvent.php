<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2006-2007, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2007, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\FSM\Event;

use Stagehand\FSM\State\StateInterface;

/**
 * An event class which manages an event such as a event which triggers
 * transition and entry/exit/do special events.
 *
 * @package    Stagehand_FSM
 * @copyright  2006-2007, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class TransitionEvent implements TransitionEventInterface
{
    /**
     * @var string
     */
    protected $eventID;

    /**
     * @var \Stagehand\FSM\State\StateInterface
     */
    protected $nextState;

    /**
     * @var callback
     */
    protected $action;

    /**
     * @var callback
     */
    protected $guard;

    /**
     * @param string $eventID
     */
    public function __construct($eventID)
    {
        $this->eventID = $eventID;
    }

    /**
     * Sets the next state to the event.
     *
     * @param \Stagehand\FSM\State\StateInterface $state
     */
    public function setNextState(StateInterface $state)
    {
        $this->nextState = $state;
    }

    /**
     * Sets the action for the event.
     *
     * @param callback $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Sets the guard for the event.
     *
     * @param callback $guard
     */
    public function setGuard($guard)
    {
        $this->guard = $guard;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventID()
    {
        return $this->eventID;
    }

    /**
     * {@inheritDoc}
     */
    public function getNextState()
    {
        return $this->nextState;
    }

    /**
     * {@inheritDoc}
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * {@inheritDoc}
     */
    public function getGuard()
    {
        return $this->guard;
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
