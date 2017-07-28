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
use Stagehand\FSM\Token\TokenAwareTrait;

/**
 * @since Class available since Release 0.1.0
 */
class State implements TransitionalStateInterface, StateActionInterface
{
    use StateActionTrait;
    use TokenAwareTrait;
    use TransitionalStateTrait;

    /**
     * @var string
     */
    private $stateId;

    /**
     * @param string $stateId
     */
    public function __construct($stateId)
    {
        $this->stateId = $stateId;
        $this->entryEvent = new EntryEvent();
        $this->exitEvent = new ExitEvent();
        $this->doEvent = new DoEvent();
    }

    /**
     * {@inheritdoc}
     */
    public function getStateId()
    {
        return $this->stateId;
    }
}
