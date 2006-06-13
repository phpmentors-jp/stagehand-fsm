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
 * @link       http://iteman.typepad.jp/piece/
 * @since      File available since Release 1.3.0
 */

require_once 'PEAR/ErrorStack.php';

// {{{ constants

/*
 * Error codes
 */
define('STAGEHAND_FSM_ERROR_INVALID_OPERATION', -1);

// }}}
// {{{ Stagehand_FSM_Error

/**
 * An error class for Stagehand_FSM package.
 *
 * @package    Stagehand_FSM
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @since      File available since Release 1.3.0
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
    // {{{ getErrorStack()

    /**
     * Returns a single error stack for the package.
     *
     * @param string  $package
     * @return PEAR_ErrorStack
     */
    function &getErrorStack($package = 'Stagehand_FSM')
    {
        $stack = &PEAR_ErrorStack::singleton(strtolower($package));
        return $stack;
    }

    // }}}
    // {{{ raiseError()

    /**
     * Returns a PEAR_ErrorStack object for the package.
     *
     * @param integer $code
     * @param string  $message
     * @param array   $params
     * @param string  $package
     * @param array   $backtrace
     * @return PEAR_ErrorStack
     */
    function &raiseError($code,
                         $message = false,
                         $params = array(),
                         $package = 'Stagehand_FSM',
                         $backtrace = false
                         )
    {
        if (!$backtrace) {
            $backtrace = debug_backtrace();
        }

        $stack = &Stagehand_FSM_Error::getErrorStack($package);
        $stack->push($code, 'error', $params, $message, false, $backtrace);
        return $stack;
    }

    // }}}
    // {{{ isError()

    /**
     * Returns whether the value is a PEAR_ErrorStack object.
     *
     * @param mixed $error
     * @return boolean
     */
    function isError($error)
    {
        if (is_object($error) && is_a($error, 'PEAR_ErrorStack')) {
            return true;
        }

        return false;
    }

    // }}}
    // {{{ pushCallback()

    /**
     * Pushes a callback. This method is a wrapper for
     * PEAR_ErrorStack::staticPushCallback method.
     *
     * @param callback $callback
     */
    function pushCallback($callback)
    {
        PEAR_ErrorStack::staticPushCallback($callback);
    }

    // }}}
    // {{{ popCallback()

    /**
     * Pops a callback. This method is a wrapper for
     * PEAR_ErrorStack::staticPopCallback method.
     *
     * @return callback
     */
    function popCallback()
    {
        return PEAR_ErrorStack::staticPopCallback();
    }

    // }}}
    // {{{ hasErrors()

    /**
     * Returns whether the stack has errors or not. This method is a wrapper
     * for PEAR_ErrorStack::staticHasErrors method.
     *
     * @param string $package
     * @param string $level
     * @return callback
     */
    function hasErrors($package = false, $level = false)
    {
        return PEAR_ErrorStack::staticHasErrors($package, $level);
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
