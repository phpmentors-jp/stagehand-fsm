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

use Stagehand\FSM\StateMachine\StateMachineInterface;

interface ParentStateInterface
{
    /**
     * @return bool
     */
    public function hasChildren();

    /**
     * @param StateMachineInterface $stateMachine
     */
    public function addChild(StateMachineInterface $stateMachine);

    /**
     * @param string $stateMachineId
     *
     * @return StateMachineInterface|null
     */
    public function getChild($stateMachineId);

    /**
     * @return StateMachineInterface[]
     */
    public function getChildren();
}
