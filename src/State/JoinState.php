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

/**
 * @since Class available since Release 3.0.0
 */
class JoinState implements TransitionalStateInterface, AutomaticTransitionInterface, StateActionInterface
{
    use AutomaticTransitionTrait;
    use StateActionTrait;

    /**
     * @var string
     */
    private $stateId;

    /**
     * @param string $stateId
     */
    public function __construct(string $stateId)
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
}
