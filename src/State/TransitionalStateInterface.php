<?php
/*
 * Copyright (c) 2015 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\State;

use Stagehand\FSM\Event\TransitionEventInterface;

/**
 * @since Class available since Release 2.2.0
 */
interface TransitionalStateInterface extends StateInterface
{
    /**
     * @param TransitionEventInterface $event
     */
    public function addTransitionEvent(TransitionEventInterface $event);
}
