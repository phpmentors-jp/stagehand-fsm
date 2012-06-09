<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Copyright (c) 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.0.0
 */

namespace Stagehand\FSM;

/**
 * @package    Stagehand_FSM
 * @copyright  2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.0.0
 */
class FSMBuilder
{
    /**
     * @var \Stagehand\FSM\FSM
     */
    protected $fsm;

    /**
     * @param string|\Stagemand\FSM\FSM $fsmNameOrFSM
     */
    public function __construct($fsmNameOrFSM = null)
    {
        if (!is_null($fsmNameOrFSM)) {
            if ($fsmNameOrFSM instanceof FSM) {
                $this->fsm = $fsmNameOrFSM;
            } else {
                $this->fsm = new FSM($fsmNameOrFSM);
            }
        } else {
            $this->fsm = new FSM();
        }
    }

    /**
     * @return \Stagehand\FSM\FSM
     */
    public function getFSM()
    {
        return $this->fsm;
    }

    /**
     * Sets the given state as the first state.
     *
     * @param string $firstStateName
     */
    public function setFirstState($firstStateName)
    {
        $this->addTransition(IState::STATE_INITIAL, Event::EVENT_START, $firstStateName);
    }

    /**
     * Sets the activity to the state.
     *
     * @param string   $stateName
     * @param callback $activity
     */
    public function setActivity($stateName, $activity)
    {
        $this->addTransition($stateName, EVENT::EVENT_DO, null, $activity);
    }

    /**
     * Adds the state with the given name.
     *
     * @param string $stateName
     */
    public function addState($stateName)
    {
        $this->fsm->addState(new State($stateName));
    }

    /**
     * Adds a FSM object to the FSM.
     *
     * @param \Stagehand\FSM\FSM $fsm
     */
    public function addFSM(FSM $fsm)
    {
        if (is_null($fsm->getPayload())) {
            $fsm->setPayload($this->fsm->getPayload());
        }
        $this->fsm->addState(FSMState::wrap($fsm));
    }

    /**
     * Adds the state transition.
     *
     * @param string   $stateName
     * @param string   $eventName
     * @param string   $nextStateName
     * @param callback $action
     * @param callback $guard
     * @param boolean  $usesHistoryMarker
     */
    public function addTransition(
        $stateName,
        $eventName,
        $nextStateName,
        $action = null,
        $guard = null,
        $usesHistoryMarker = false)
    {
        $state = $this->fsm->getState($stateName);
        if (is_null($state)) {
            $state = new State($stateName);
            $this->fsm->addState($state);
        }

        $event = $state->getEvent($eventName);
        if (is_null($event)) {
            $event = new Event($eventName);
            $state->addEvent($event);
        }

        $event->setNextState($nextStateName);
        $event->setAction($action);
        $event->setGuard($guard);
        $event->setUsesHistoryMarker($usesHistoryMarker);
    }

    /**
     * Sets the entry action to the state.
     *
     * @param string   $stateName
     * @param callback $action
     */
    public function setEntryAction($stateName, $action)
    {
        $this->addTransition($stateName, Event::EVENT_ENTRY, null, $action);
    }

    /**
     * Sets the exit action to the state.
     *
     * @param string   $stateName
     * @param callback $action
     */
    public function setExitAction($stateName, $action)
    {
        $this->addTransition($stateName, Event::EVENT_EXIT, null, $action);
    }

    /**
     * Sets the given payload.
     *
     * @param mixed &$payload
     */
    public function setPayload(&$payload)
    {
        $this->fsm->setPayload($payload);
    }

    /**
     * Sets the name of the FSM.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->fsm->setName($name);
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
