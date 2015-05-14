<?php
/*
 * Copyright (c) 2011-2012, 2014-2015 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\State;

use PHPMentors\DomainKata\Entity\EntityInterface;

/**
 * @since Class available since Release 2.0.0
 */
interface StateInterface extends EntityInterface
{
    const STATE_INITIAL = '__INITIAL__';
    const STATE_FINAL = '__FINAL__';

    /**
     * Gets the event according to the given ID.
     *
     * @param string $eventId
     *
     * @return EventInterfacee
     */
    public function getEvent($eventId);

    /**
     * Gets the ID of the state.
     *
     * @return string
     */
    public function getStateId();

    /**
     * Checks whether the state is connected to the final state or not.
     *
     * @return bool
     */
    public function isEndState();
}
