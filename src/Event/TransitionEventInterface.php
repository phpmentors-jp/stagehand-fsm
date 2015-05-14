<?php
/*
 * Copyright (c) 2013 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\Event;

use Stagehand\FSM\State\StateInterface;

/**
 * @since Class available since Release 2.0.0
 */
interface TransitionEventInterface extends EventInterface
{
    /**
     * Gets the next state that the event will transition to.
     *
     * @return StateInterface
     */
    public function getNextState();

    /**
     * Gets the guard for the event.
     *
     * @return callback
     */
    public function getGuard();

    /**
     * Checks whether the event is connected to the final state or not.
     *
     * @return bool
     */
    public function isEndEvent();
}
