<?php
/*
 * Copyright (c) KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\State;

use Stagehand\FSM\Event\DoEvent;
use Stagehand\FSM\Event\EntryEvent;
use Stagehand\FSM\Event\ExitEvent;

/**
 * @since Interface available since Release 3.0.0
 */
interface StateActionInterface
{
    /**
     * @return EntryEvent
     */
    public function getEntryEvent(): EntryEvent;

    /**
     * @return ExitEvent
     */
    public function getExitEvent(): ExitEvent;

    /**
     * @return DoEvent
     */
    public function getDoEvent(): DoEvent;
}
