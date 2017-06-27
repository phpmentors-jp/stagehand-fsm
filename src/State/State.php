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

use Stagehand\FSM\Token\TokenAwareTrait;

/**
 * @since Class available since Release 0.1.0
 */
class State implements TransitionalStateInterface
{
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
    }

    /**
     * {@inheritdoc}
     */
    public function getStateId()
    {
        return $this->stateId;
    }
}
