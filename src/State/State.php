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

/**
 * @since Class available since Release 0.1.0
 */
class State implements TransitionalStateInterface, StateActionInterface, ParentStateInterface
{
    use StateActionTrait;
    use TransitionalStateTrait;

    /**
     * @var string
     */
    private $stateId;

    /**
     * @var StateMachineInterface[]
     */
    private $children = [];

    /**
     * @param string $stateId
     */
    public function __construct($stateId)
    {
        $this->stateId = $stateId;
        $this->initializeStateActionEvents();
    }

    /**
     * {@inheritdoc}
     */
    public function getStateId()
    {
        return $this->stateId;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return count($this->children) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(StateMachineInterface $stateMachine)
    {
        $this->children[$stateMachine->getStateMachineId()] = $stateMachine;
    }

    /**
     * {@inheritdoc}
     */
    public function getChild($stateMachineId)
    {
        return $this->children[$stateMachineId] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }
}
