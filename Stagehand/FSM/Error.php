<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 1.3.0
 */

require_once 'PEAR/ErrorStack.php';

// {{{ constants

/*
 * Error codes
 */
define('STAGEHAND_FSM_ERROR_ALREADY_SHUTDOWN', -1);
define('STAGEHAND_FSM_ERROR_NOT_CALLABLE',     -2);

// }}}
// {{{ Stagehand_FSM_Error

/**
 * An error class for Stagehand_FSM package.
 *
 * @package    Stagehand_FSM
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 1.3.0
 */
class Stagehand_FSM_Error
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     * @static
     */

    // }}}
    // {{{ push()

    /**
     * Adds an error to the stack for the package. This method is a wrapper
     * for PEAR_ErrorStack::staticPush() method.
     *
     * @param integer $code
     * @param string  $message
     * @param string  $level
     * @param array   $params
     * @param array   $repackage
     * @param array   $backtrace
     * @see PEAR_ErrorStack::staticPush()
     * @since Method available since Release 1.4.0
     */
    function push($code,
                  $message = false,
                  $level = 'exception',
                  $params = array(),
                  $repackage = false,
                  $backtrace = false
                  )
    {
        if (!$backtrace) {
            $backtrace = debug_backtrace();
        }

        PEAR_ErrorStack::staticPush('Stagehand_FSM',
                                    $code,
                                    'exception',
                                    $params,
                                    $message,
                                    $repackage,
                                    $backtrace
                                    );
    }

    // }}}
    // {{{ pushCallback()

    /**
     * Pushes a callback for this package.
     *
     * @param callback $callback
     * @since Method available since Release 1.4.0
     */
    function pushCallback($callback)
    {
        $errorStack = &PEAR_ErrorStack::singleton('Stagehand_FSM');
        $errorStack->pushCallback($callback);
    }

    // }}}
    // {{{ popCallback()

    /**
     * Pops a callback for this package.
     *
     * @return callback
     * @since Method available since Release 1.4.0
     */
    function popCallback()
    {
        $errorStack = &PEAR_ErrorStack::singleton('Stagehand_FSM');
        $errorStack->popCallback();
    }

    // }}}
    // {{{ hasErrors()

    /**
     * Returns whether the stack has errors or not. This method is a wrapper
     * for PEAR_ErrorStack::staticHasErrors() method.
     *
     * @return boolean
     * @see PEAR_ErrorStack::staticHasErrors()
     * @since Method available since Release 1.4.0
     */
    function hasErrors()
    {
        return PEAR_ErrorStack::staticHasErrors('Stagehand_FSM', 'exception');
    }

    // }}}
    // {{{ pop()

    /**
     * Pops an error off of the error stack for the package. This method is a
     * wrapper for PEAR_ErrorStack::pop() method.
     *
     * @return array
     * @see PEAR_ErrorStack::pop()
     * @since Method available since Release 1.4.0
     */
    function pop()
    {
        return PEAR_ErrorStack::staticPop('Stagehand_FSM');
    }

    // }}}
    // {{{ clearErrors()

    /**
     * Clears the error stack for the package.
     *
     * @see PEAR_ErrorStack::getErrors()
     * @since Method available since Release 1.4.0
     */
    function clearErrors()
    {
        $stack = &PEAR_ErrorStack::singleton('Stagehand_FSM');
        $stack->getErrors(true);
    }

    // }}}
    // {{{ disableCallback()

    /**
     * Disables the last callback.
     *
     * @since Method available since Release 1.10.0
     */
    function disableCallback()
    {
        Stagehand_FSM_Error::pushCallback(array(__CLASS__, 'handleError'));
    }

    // }}}
    // {{{ disableCallback()

    /**
     * Enables the last  callback.
     *
     * @since Method available since Release 1.10.0
     */
    function enableCallback()
    {
        Stagehand_FSM_Error::popCallback();
    }

    // }}}
    // {{{ handleError()

    /**
     * An error handler for this package.
     *
     * @since Method available since Release 1.10.0
     */
    function handleError()
    {
        return PEAR_ERRORSTACK_PUSHANDLOG;
    }

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
