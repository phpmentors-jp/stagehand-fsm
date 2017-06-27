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
 * @since Trait available since Release 3.0.0
 */
trait StateActionTrait
{
    /**
     * @var EntryEvent
     */
    protected $entryEvent;

    /**
     * @var ExitEvent
     */
    protected $exitEvent;

    /**
     * @var DoEvent
     */
    protected $doEvent;

    /**
     * @return EntryEvent
     */
    public function getEntryEvent(): EntryEvent
    {
        return $this->entryEvent;
    }

    /**
     * @return ExitEvent
     */
    public function getExitEvent(): ExitEvent
    {
        return $this->exitEvent;
    }

    /**
     * @return DoEvent
     */
    public function getDoEvent(): DoEvent
    {
        return $this->doEvent;
    }
}
