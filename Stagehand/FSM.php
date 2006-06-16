<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman2002@yahoo.co.jp>
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
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @author     MIYAI Fumihiko <fumichz@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://iteman.typepad.jp/stagehand/
 * @link       http://en.wikipedia.org/wiki/Finite_state_machine
 * @link       http://www.isd.mel.nist.gov/projects/omacapi/Software/FiniteStateMachine/doc/FSMExample.html
 * @link       http://www.isd.mel.nist.gov/projects/omacapi/Software/FiniteStateMachine/doc/
 * @link       http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @link       http://pear.php.net/package/FSM
 * @link       http://www.microsoft.com/japan/msdn/net/aspnet/aspnet-finitestatemachines.asp
 * @link       http://www.generation5.org/content/2003/FSM_Tutorial.asp
 * @since      File available since Release 0.1.0
 */

require_once 'Stagehand/FSM/State.php';
require_once 'Stagehand/FSM/FSMState.php';
require_once 'Stagehand/FSM/Error.php';

// {{{ constants

/*
 * Constants for pseudo states.
 */
define('STAGEHAND_FSM_STATE_INITIAL', '_initial');
define('STAGEHAND_FSM_STATE_FINAL', '_final');

// }}}
// {{{ Stagehand_FSM

/**
 * A Finite State Machine.
 *
 * Stagehand_FSM provides a self configuring Finite State Machine(FSM).
 * The following is a list of features of Stagehand_FSM.
 * o Transition action
 * o Entry and Exit state actions
 * o Initial and Final pseudo states
 * o Nested FSM
 * o History Marker
 * o Activity
 * o User defined payload
 *
 * @package    Stagehand_FSM
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @author     MIYAI Fumihiko <fumichz@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/stagehand/
 * @link       http://en.wikipedia.org/wiki/Finite_state_machine
 * @link       http://www.isd.mel.nist.gov/projects/omacapi/Software/FiniteStateMachine/doc/FSMExample.html
 * @link       http://www.isd.mel.nist.gov/projects/omacapi/Software/FiniteStateMachine/doc/
 * @link       http://www.sparxsystems.com/resources/uml2_tutorial/uml2_statediagram.html
 * @link       http://pear.php.net/package/FSM
 * @link       http://www.microsoft.com/japan/msdn/net/aspnet/aspnet-finitestatemachines.asp
 * @link       http://www.generation5.org/content/2003/FSM_Tutorial.asp
 * @since      Class available since Release 0.1.0
 */
class Stagehand_FSM
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_currentState;
    var $_previousState;
    var $_states = array();
    var $_name;
    var $_payload;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Constructor
     *
     * @param string $state
     */
    function Stagehand_FSM($state = null)
    {
        if (!is_null($state)) {
            $this->setFirstState($state);
        }
    }

    // }}}
    // {{{ setFirstState()

    /**
     * Sets the given state as the first state.
     *
     * @param string $state
     */
    function setFirstState($state)
    {
        $this->addTransition(STAGEHAND_FSM_STATE_INITIAL, STAGEHAND_FSM_EVENT_START,
                             $state
                             );
    }

    // }}}
    // {{{ start()

    /**
     * Starts the Finite State Machine.
     */
    function start()
    {
        $this->_initialize();
        $this->triggerEvent(STAGEHAND_FSM_EVENT_START);
    }

    // }}}
    // {{{ getCurrentState()

    /**
     * Gets the current state.
     *
     * @return Stagehand_FSM_State
     */
    function &getCurrentState()
    {
        return $this->_currentState;
    }

    // }}}
    // {{{ getPreviousState()

    /**
     * Gets the previous state.
     *
     * @return Stagehand_FSM_State
     */
    function &getPreviousState()
    {
        return $this->_previousState;
    }

    // }}}
    // {{{ getPayload()

    /**
     * Gets the payload.
     *
     * @return mixed $payload
     */
    function &getPayload()
    {
        return $this->_payload;
    }

    // }}}
    // {{{ triggerEvent()

    /**
     * Triggers the given state.
     *
     * @param string  $eventName
     * @param boolean $transitionToHistoryMarker
     * @return Stagehand_FSM_State
     * @throws PEAR_ErrorStack
     */
    function &triggerEvent($eventName, $transitionToHistoryMarker = false)
    {
        if ($this->_currentState->getName() == STAGEHAND_FSM_STATE_FINAL
            && !$this->_isSpecialEvent($eventName)
            ) {
            $error = Stagehand_FSM_Error::raiseError(STAGEHAND_FSM_ERROR_INVALID_OPERATION,
                                                     'The FSM was already shutdown.'
                                                     );
            return $error;
        }

        $event = &$this->_currentState->getEvent($eventName);

        if (is_null($event)
            || !$this->_isSpecialEvent($eventName)
            && !$event->evaluateGuard($this)
            ) {
            $eventName = STAGEHAND_FSM_EVENT_DO;
            $event = &$this->_currentState->getEvent(STAGEHAND_FSM_EVENT_DO);
        }

        if (!$this->_isSpecialEvent($eventName)) {
            $this->triggerEvent(STAGEHAND_FSM_EVENT_EXIT);
        }

        if (!$this->_isSpecialEvent($eventName)) {
            $nextStateName = $event->getNextState();
            $this->_transition($nextStateName);
        }

        $event->invokeAction($this);

        if ($this->_isEntryEvent($eventName)
            && is_a($this->_currentState, __CLASS__)
            && !$transitionToHistoryMarker
            ) {
            $this->_currentState->start();
        }

        if (!$this->_isSpecialEvent($eventName)) {
            $this->triggerEvent(STAGEHAND_FSM_EVENT_ENTRY,
                                $event->getTransitionToHistoryMarker()
                                );
        }

        if (!$this->_isSpecialEvent($eventName)) {
            $this->triggerEvent(STAGEHAND_FSM_EVENT_DO,
                                $event->getTransitionToHistoryMarker()
                                );
        }

        return $this->_currentState;
    }

    // }}}
    // {{{ addTransition()

    /**
     * Adds the state transition.
     *
     * @param string   $stateName
     * @param string   $eventName
     * @param string   $nextStateName
     * @param callback $action
     * @param callback $guard
     * @param boolean  $transitionToHistoryMarker
     */
    function addTransition($stateName, $eventName, $nextStateName,
                           $action = null, $guard = null,
                           $transitionToHistoryMarker = false
                           )
    {
        $state = &$this->_findState($stateName);
        if (is_null($state)) {
            $state = &$this->addState($stateName);
        }

        $event = &$state->getEvent($eventName);
        if (is_null($event)) {
            $event = &$state->addEvent($eventName);
        }

        $event->setNextState($nextStateName);
        $event->setAction($action);
        $event->setGuard($guard);
        $event->setTransitionToHistoryMarker($transitionToHistoryMarker);
    }

    // }}}
    // {{{ setExitAction()

    /**
     * Sets the exit action to the state.
     *
     * @param string   $state
     * @param callback $action
     */
    function setExitAction($state, $action)
    {
        $this->addTransition($state, STAGEHAND_FSM_EVENT_EXIT, null, $action);
    }

    // }}}
    // {{{ setEntryAction()

    /**
     * Sets the entry action to the state.
     *
     * @param string   $state
     * @param callback $action
     */
    function setEntryAction($state, $action)
    {
        $this->addTransition($state, STAGEHAND_FSM_EVENT_ENTRY, null, $action);
    }

    // }}}
    // {{{ getState()

    /**
     * Finds and returns the state with the given name. This method finds the
     * state recursively if child FSMs exists.
     *
     * @param string $stateName
     * @return mixed
     */
    function &getState($stateName)
    {
        $state = &$this->_findState($stateName);
        if (is_null($state)) {
            foreach ($this->_states as $value) {
                if (is_a($value, __CLASS__)) {
                    if (!is_null($value->getState($stateName))) {
                        return $value;
                    }
                }
            }
        }

        return $state;
    }

    // }}}
    // {{{ addState()

    /**
     * Adds the state with the given name.
     *
     * @param string $state
     * @return Stagehand_FSM_State
     */
    function &addState($state)
    {
        $this->_states[$state] = &new Stagehand_FSM_State($state);
        return $this->_states[$state];
    }

    // }}}
    // {{{ setName()

    /**
     * Sets the name of the FSM.
     *
     * @param string $name
     */
    function setName($name)
    {
        $this->_name = $name;
    }

    // }}}
    // {{{ getName()

    /**
     * Gets the name of the FSM.
     *
     * @return string
     */
    function getName()
    {
        return $this->_name;
    }

    // }}}
    // {{{ addFSM()

    /**
     * Adds a Stagehand_FSM object to the FSM.
     *
     * @param Stagehand_FSM $fsm
     * @return Stagehand_FSM_FSMState
     */
    function &addFSM(&$fsm)
    {
        if (is_null($fsm->getPayload())) {
            $fsm->setPayload($this->_payload);
        }
        $name = $fsm->getName();
        $this->_states[$name] = &Stagehand_FSM_FSMState::wrap($fsm);
        return $this->_states[$name];
    }

    // }}}
    // {{{ setActivity()

    /**
     * Sets the activity to the state.
     *
     * @param string   $state
     * @param callback $activity
     */
    function setActivity($state, $activity)
    {
        $this->addTransition($state, STAGEHAND_FSM_EVENT_DO, null, $activity);
    }

    // }}}
    // {{{ setPayload()

    /**
     * Sets the given payload.
     *
     * @param mixed $payload
     */
    function setPayload(&$payload)
    {
        $this->_payload = &$payload;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _isSpecialEvent()

    /**
     * Returns whether the event is special event or not.
     *
     * @param string $event
     * @return boolean
     */
    function _isSpecialEvent($event)
    {
        return $event == STAGEHAND_FSM_EVENT_ENTRY
            || $event == STAGEHAND_FSM_EVENT_EXIT
            || $event == STAGEHAND_FSM_EVENT_DO;
    }

    // }}}
    // {{{ _transition()

    /**
     * Transitions to the next state.
     *
     * @param string $stateName
     */
    function _transition($stateName)
    {
        $this->_previousState = &$this->_currentState;
        $state = &$this->getState($stateName);
        if (is_null($state)) {
            $state = &$this->addState($stateName);
        }
        $this->_currentState = &$state;
    }

    // }}}
    // {{{ _findState()

    /**
     * Finds and returns the state with the given name in the FSM.
     *
     * @param string $name
     * @return mixed
     */
    function &_findState($name)
    {
        if (!array_key_exists($name, $this->_states)) {
            $return = null;
            return $return;
        }

        return $this->_states[$name];
    }

    // }}}
    // {{{ _isEntryEvent()

    /**
     * Returns whether the event is entry event or not.
     *
     * @param string $event
     * @return boolean
     */
    function _isEntryEvent($event)
    {
        return $event == STAGEHAND_FSM_EVENT_ENTRY;
    }

    // }}}
    // {{{ _initialize()

    /**
     * Initializes the FSM.
     */
    function _initialize()
    {
        $this->_currentState = &$this->_findState(STAGEHAND_FSM_STATE_INITIAL);
        if (is_null($this->_currentState)) {
            $this->_currentState = &$this->addState(STAGEHAND_FSM_STATE_INITIAL);
        }
    }

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
