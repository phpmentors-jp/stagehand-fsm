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
 * @link       http://iteman.typepad.jp/stagehand/
 * @since      File available since Release 0.1.0
 */

require_once 'Stagehand/FSM/Event.php';

// {{{ constants

/*
 * Constants for special events.
 */
define('STAGEHAND_FSM_EVENT_ENTRY', '_entry');
define('STAGEHAND_FSM_EVENT_EXIT', '_exit');
define('STAGEHAND_FSM_EVENT_START', '_start');
define('STAGEHAND_FSM_EVENT_END', '_end');
define('STAGEHAND_FSM_EVENT_DO', '_do');

// }}}
// {{{ Stagehand_FSM_State

/**
 * A state class which builds initial structure of the state which consists
 * entry/exit actions and an activity, and behaves as event holder of the
 * state.
 *
 * @package    Stagehand_FSM
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/stagehand/
 * @since      Class available since Release 0.1.0
 */
class Stagehand_FSM_State
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
    var $_events;

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
    function Stagehand_FSM_State($name)
    {
        $this->_name = $name;
        $this->addEvent(STAGEHAND_FSM_EVENT_ENTRY);
        $this->addEvent(STAGEHAND_FSM_EVENT_EXIT);
        $this->addEvent(STAGEHAND_FSM_EVENT_DO);
    }

    // }}}
    // {{{ getEvent()

    /**
     * Finds and returns the event with the given name.
     *
     * @param string $event
     * @return mixed
     */
    function &getEvent($event)
    {
        if (!array_key_exists($event, $this->_events)) {
            $return = null;
            return $return;
        }

        return $this->_events[$event];
    }

    // }}}
    // {{{ addEvent()

    /**
     * Adds the event with the given name.
     *
     * @param string $event
     * @return Stagehand_FSM_Event
     */
    function &addEvent($event)
    {
        $this->_events[$event] = &new Stagehand_FSM_Event($event);
        return $this->_events[$event];
    }

    // }}}
    // {{{ getName()

    /**
     * Gets the name of the state.
     *
     * @return string
     */
    function getName()
    {
        return $this->_name;
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
