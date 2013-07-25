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

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\Event\TransitionEventInterface;

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
    }

    /**
     * @param  \Stagehand\FSM\Event\EventInterface        $event
     * @throws \Stagehand\FSM\State\InvalidEventException
     * @since Method available since Release 2.0.0
     */
    public function setEntryEvent(EventInterface $event)
    {
        if ($event->getEventID() != EventInterface::EVENT_ENTRY) {
            throw new InvalidEventException(sprintf('The event "%s" is not an entry event. "%s" must be set as the ID for an entry event ', $event->getEventID(), EventInterface::EVENT_ENTRY));
        }

        $this->events[ $event->getEventID() ] = $event;
    }

    /**
     * @param  \Stagehand\FSM\Event\EventInterface        $event
     * @throws \Stagehand\FSM\State\InvalidEventException
     * @since Method available since Release 2.0.0
     */
    public function setExitEvent(EventInterface $event)
    {
        if ($event->getEventID() != EventInterface::EVENT_EXIT) {
            throw new InvalidEventException(sprintf('The event "%s" is not an exit event. "%s" must be set as the ID for an exit event ', $event->getEventID(), EventInterface::EVENT_EXIT));
        }

        $this->events[ $event->getEventID() ] = $event;
    }

    /**
     * @param  \Stagehand\FSM\Event\EventInterface        $event
     * @throws \Stagehand\FSM\State\InvalidEventException
     * @since Method available since Release 2.0.0
     */
    public function setDoEvent(EventInterface $event)
    {
        if ($event->getEventID() != EventInterface::EVENT_DO) {
            throw new InvalidEventException(sprintf('The event "%s" is not a do event. "%s" must be set as the ID for an do event ', $event->getEventID(), EventInterface::EVENT_DO));
        }

        $this->events[ $event->getEventID() ] = $event;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvent($eventID)
    {
        if (array_key_exists($eventID, $this->events)) {
            return $this->events[$eventID];
        } else {
            return null;
        }
    }

    /**
     * @param \Stagehand\FSM\Event\TransitionEventInterface $event
     * @throws \Stagehand\FSM\State\DuplicateEventException
     */
    public function addTransitionEvent(TransitionEventInterface $event)
    {
        if (array_key_exists($event->getEventID(), $this->events)) {
            throw new DuplicateEventException(sprintf('The event "%s" already exists in the state "%s".', $event->getEventID(), $this->getStateID()));
        }

        $this->events[ $event->getEventID() ] = $event;
    }

    public function getStateID()
    {
        return $this->stateID;
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
