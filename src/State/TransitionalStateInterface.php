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
 * @since Class available since Release 2.2.0
 */
interface TransitionalStateInterface extends StateInterface
{
    /**
     * @param EventInterface $event
     */
    public function addTransitionEvent(EventInterface $event);

    /**
     * @param string $eventId
     *
     * @return EventInterface|null
     *
     * @since Method available since Release 3.0.0
     */
    public function getTransitionEvent($eventId);
}
