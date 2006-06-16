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
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://iteman.typepad.jp/stagehand/
 * @since      File available since Release 0.1.0
 */

// {{{ Stagehand_FSM_Event

/**
 * An event class which manages an event such as a event which triggers
 * transition and entry/exit/do special events.
 *
 * @package    Stagehand_FSM
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/stagehand/
 * @since      Class available since Release 0.1.0
 */
class Stagehand_FSM_Event
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_name;
    var $_nextState;
    var $_action;
    var $_guard;
    var $_transitionToHistoryMarker = false;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Constructor
     *
     * @param string $name
     */
    function Stagehand_FSM_Event($name)
    {
        $this->_name = $name;
    }

    // }}}
    // {{{ setNextState()

    /**
     * Sets the next state of the event.
     *
     * @param string $state
     */
    function setNextState($state)
    {
        $this->_nextState = $state;
    }

    // }}}
    // {{{ setAction()

    /**
     * Sets the action the event.
     *
     * @param callback $action
     */
    function setAction($action)
    {
        $this->_action = $action;
    }

    // }}}
    // {{{ setGuard()

    /**
     * Sets the guard the event.
     *
     * @param callback $guard
     */
    function setGuard($guard)
    {
        $this->_guard = $guard;
    }

    // }}}
    // {{{ setTransitionToHistoryMarker()

    /**
     * Sets whether the event transitions to the history marker or not.
     *
     * @param boolean $transitionToHistoryMarker
     */
    function setTransitionToHistoryMarker($transitionToHistoryMarker)
    {
        $this->_transitionToHistoryMarker = $transitionToHistoryMarker;
    }

    // }}}
    // {{{ getName()

    /**
     * Gets the name of the event.
     *
     * @return string
     */
    function getName()
    {
        return $this->_name;
    }

    // }}}
    // {{{ getNextState()

    /**
     * Gets the next state of the event.
     *
     * @return string
     */
    function getNextState()
    {
        return $this->_nextState;
    }

    // }}}
    // {{{ getAction()

    /**
     * Gets the action the event.
     *
     * @return callback
     */
    function getAction()
    {
        return $this->_action;
    }

    // }}}
    // {{{ getGuard()

    /**
     * Gets the guard the event.
     *
     * @return callback
     */
    function getGuard()
    {
        return $this->_guard;
    }

    // }}}
    // {{{ getTransitionToHistoryMarker()

    /**
     * Returns whether the event transitions to the history marker or not.
     *
     * @return boolean
     */
    function getTransitionToHistoryMarker()
    {
        return $this->_transitionToHistoryMarker;
    }

    // }}}
    // {{{ evaluateGuard()

    /**
     * Evaluates the guard.
     *
     * @param Stagehand_FSM &$fsm
     * @return boolean
     */
    function evaluateGuard(&$fsm)
    {
        if (!is_callable($this->_guard)) {
            return true;
        }

        $payload = &$fsm->getPayload();
        return call_user_func_array($this->_guard,
                                    array(&$fsm, &$this, &$payload)
                                    );
    }

    // }}}
    // {{{ invokeAction()

    /**
     * Invokes the action.
     *
     * @param Stagehand_FSM &$fsm
     */
    function invokeAction(&$fsm)
    {
        if (!is_callable($this->_action)) {
            return;
        }

        $payload = &$fsm->getPayload();
        call_user_func_array($this->_action,
                             array(&$fsm, &$this, &$payload)
                             );
    }

    /**#@-*/

    /**#@+
     * @access private
     */

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
