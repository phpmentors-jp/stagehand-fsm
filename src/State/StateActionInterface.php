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

use Stagehand\FSM\Event\EventInterface;

/**
 * @since Interface available since Release 3.0.0
 */
interface StateActionInterface
{
    const EVENT_ENTRY = '__ENTRY__';
    const EVENT_EXIT = '__EXIT__';
    const EVENT_DO = '__DO__';

    /**
     * @return EventInterface
     */
    public function getEntryEvent(): EventInterface;

    /**
     * @return EventInterface
     */
    public function getExitEvent(): EventInterface;

    /**
     * @return EventInterface
     */
    public function getDoEvent(): EventInterface;
}
