<?php
/*
 * Copyright (c) 2013-2015 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\Event;

use PHPMentors\DomainKata\Entity\EntityInterface;

/**
 * @since Class available since Release 2.0.0
 */
interface EventInterface extends EntityInterface
{
    /*
     * Constants for special events.
     */
    const EVENT_ENTRY = '__ENTRY__';
    const EVENT_EXIT = '__EXIT__';
    const EVENT_START = '__START__';
    const EVENT_DO = '__DO__';

    /**
     * Gets the ID of the event.
     *
     * @return string
     */
    public function getEventId();

    /**
     * Gets the action for the event.
     *
     * @return callback
     */
    public function getAction();
}
