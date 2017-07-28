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
 * @since Trait available since Release 3.0.0
 */
trait StateActionTrait
{
    /**
     * @var EventInterface
     */
    protected $entryEvent;

    /**
     * @var EventInterface
     */
    protected $exitEvent;

    /**
     * @var EventInterface
     */
    protected $doEvent;

    /**
     * @return EventInterface
     */
    public function getEntryEvent(): EventInterface
    {
        return $this->entryEvent;
    }

    /**
     * @return EventInterface
     */
    public function getExitEvent(): EventInterface
    {
        return $this->exitEvent;
    }

    /**
     * @return EventInterface
     */
    public function getDoEvent(): EventInterface
    {
        return $this->doEvent;
    }
}
