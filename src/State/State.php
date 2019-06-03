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

use Stagehand\FSM\Event\Event;

/**
 * @since Class available since Release 0.1.0
 */
class State implements TransitionalStateInterface, StateActionInterface
{
    use StateActionTrait;
    use TransitionalStateTrait;

    /**
     * @since Constant available since Release 3.0.0
     */
    const EVENT_ENTRY = '__ENTRY__';

    /**
     * @since Constant available since Release 3.0.0
     */
    const EVENT_EXIT = '__EXIT__';

    /**
     * @since Constant available since Release 3.0.0
     */
    const EVENT_DO = '__DO__';

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
        $this->entryEvent = new Event(self::EVENT_ENTRY);
        $this->exitEvent = new Event(self::EVENT_EXIT);
        $this->doEvent = new Event(self::EVENT_DO);
    }

    /**
     * {@inheritdoc}
     */
    public function getStateId()
    {
        return $this->stateId;
    }
}
