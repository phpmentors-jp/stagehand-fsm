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

namespace Stagehand\FSM;

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
class Event
{
    /*
     * Constants for special events.
     */
    const EVENT_ENTRY = '__entry';
    const EVENT_EXIT = '__exit';
    const EVENT_START = '__start';
    const EVENT_END = '__end';
    const EVENT_DO = '__do';

    /**
     * @var string
     */
    protected $eventID;

    /**
     * @var string
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
     * @var boolean
     */
    protected $historyMarker = false;

    /**
     * @param string $eventID
     */
    public function __construct($eventID)
    {
        $this->eventID = $eventID;
    }

    /**
     * Sets the next state of the event.
     *
     * @param string $stateID
     */
    public function setNextState($stateID)
    {
        $this->nextState = $stateID;
    }

    /**
     * Sets the action the event.
     *
     * @param  callback                                  $action
     * @throws \Stagehand\FSM\ObjectNotCallableException
     */
    public function setAction($action)
    {
        if (!is_null($action)) {
            if (!is_callable($action)) {
                throw new ObjectNotCallableException('The action is not callable.');
            }

            $this->action = $action;
        }
    }

    /**
     * Sets the guard the event.
     *
     * @param  callback                                  $guard
     * @throws \Stagehand\FSM\ObjectNotCallableException
     */
    public function setGuard($guard)
    {
        if (!is_null($guard)) {
            if (!is_callable($guard)) {
                throw new ObjectNotCallableException('The guard is not callable.');
            }

            $this->guard = $guard;
        }
    }

    /**
     * Sets whether the event transitions to the history marker or not.
     *
     * @param boolean $historyMarker
     */
    public function setHistoryMarker($historyMarker)
    {
        $this->historyMarker = $historyMarker;
    }

    /**
     * Gets the ID of the event.
     *
     * @return string
     */
    public function getEventID()
    {
        return $this->eventID;
    }

    /**
     * Gets the next state of the event.
     *
     * @return string
     */
    public function getNextState()
    {
        return $this->nextState;
    }

    /**
     * Gets the action the event.
     *
     * @return callback
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Gets the guard the event.
     *
     * @return callback
     */
    public function getGuard()
    {
        return $this->guard;
    }

    /**
     * Returns whether the event transitions to the history marker or not.
     *
     * @return boolean
     */
    public function getHistoryMarker()
    {
        return $this->historyMarker;
    }

    /**
     * Evaluates the guard.
     *
     * @param  \Stagehand\FSM\FSM $fsm
     * @return boolean
     */
    public function evaluateGuard(FSM $fsm)
    {
        if (is_null($this->guard)) {
            return true;
        } else {
            return call_user_func($this->guard, $this, $fsm->getPayload(), $fsm);
        }
    }

    /**
     * Invokes the action.
     *
     * @param \Stagehand\FSM\FSM $fsm
     */
    public function invokeAction(FSM $fsm)
    {
        if (!is_null($this->action)) {
            call_user_func($this->action, $this, $fsm->getPayload(), $fsm);
        }
    }

    /**
     * Returns whether the event is special event or not.
     *
     * @param  string  $eventID
     * @return boolean
     * @since Method available since Release 2.0.0
     */
    public static function isSpecialEvent($eventID)
    {
        return $eventID == self::EVENT_ENTRY || $eventID == self::EVENT_EXIT || $eventID == self::EVENT_DO;
    }

    /**
     * Returns whether an event is a protected event such as the special
     * events and so on.
     *
     * @param  string  $eventID
     * @return boolean
     * @since Method available since Release 2.0.0
     */
    public static function isProtectedEvent($eventID)
    {
        return static::isSpecialEvent($eventID) || $eventID == self::EVENT_START || $eventID == self::EVENT_END;
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
