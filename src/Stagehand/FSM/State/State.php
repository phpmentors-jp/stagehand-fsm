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

namespace Stagehand\FSM\State;

use Stagehand\FSM\Event\Event;

/**
 * A state class which builds initial structure of the state which consists
 * entry/exit actions and an activity, and behaves as event holder of the
 * state.
 *
 * @package    Stagehand_FSM
 * @copyright  2006-2007, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class State implements StateInterface
{
    /**
     * @var string
     */
     protected $stateID;

    /**
     * @var array
     */
    protected $events = array();

    /**
     * @param string $stateID
     */
    public function __construct($stateID)
    {
        $this->stateID = $stateID;
        $this->addEvent(new Event(Event::EVENT_ENTRY));
        $this->addEvent(new Event(Event::EVENT_EXIT));
        $this->addEvent(new Event(Event::EVENT_DO));
    }

    /**
     * {@inheritDoc}
     *
     * @return \Stagehand\FSM\Event\Event
     */
    public function getEvent($eventID)
    {
        if ($this->hasEvent($eventID)) {
            return $this->events[$eventID];
        } else {
            return null;
        }
    }

    public function addEvent(Event $event)
    {
        $this->events[ $event->getEventID() ] = $event;
    }

    public function getStateID()
    {
        return $this->stateID;
    }

    /**
     * @since Method available since Release 1.6.0
     */
    public function hasEvent($eventID)
    {
        return array_key_exists($eventID, $this->events);
    }

    /**
     * Returns whether a state is a protected event such as the pseudo states and so on.
     *
     * @param  string  $stateID
     * @return boolean
     * @since Method available since Release 2.0.0
     */
    public static function isProtectedState($stateID)
    {
        return $stateID == StateInterface::STATE_INITIAL || $stateID == StateInterface::STATE_FINAL;
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
