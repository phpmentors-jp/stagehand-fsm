<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'Stagehand/FSM.php';
require_once 'Stagehand/FSM/State.php';

// {{{ Stagehand_FSMFSMState

/**
 * A sub-class of FSM which has capability of Stagehand_FSM_State.
 *
 * @package    Stagehand_FSM
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_FSM_FSMState extends Stagehand_FSM
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_state;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ wrap()

    /**
     * Wraps a Stagehand_FSM object up with a Stagehand_FSM_FSMState object.
     *
     * @param Stagehand_FSM &$fsm
     * @return Stagehand_FSM_FSMState
     * @static
     */
    function &wrap(&$fsm)
    {
        $class = __CLASS__;
        $fsmState = &new $class($fsm);
        return $fsmState;
    }

    // }}}
    // {{{ getEvent()

    /**
     * Finds and returns the event with the given name.
     *
     * @param string $eventName
     * @return mixed
     */
    function &getEvent($eventName)
    {
        $event = &$this->_state->getEvent($eventName);
        return $event;
    }

    // }}}
    // {{{ addEvent()

    /**
     * Adds the event with the given name.
     *
     * @param string $eventName
     * @return Stagehand_FSM_Event
     */
    function &addEvent($eventName)
    {
        $event = &$this->_state->addEvent($eventName);
        return $event;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ constructor

    /**
     * Constructor
     *
     * @param Stagehand_FSM &$fsm
     */
    function Stagehand_FSM_FSMState(&$fsm)
    {
        $this->_currentState = &$fsm->_currentState;
        $this->_previousState = &$fsm->_previousState;
        $this->_states = $fsm->_states;
        $this->_name = $fsm->_name;
        $this->_payload = &$fsm->_payload;
        $this->_state = &new Stagehand_FSM_State($fsm->_name);
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
