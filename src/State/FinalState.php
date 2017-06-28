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

use Stagehand\FSM\Token\Token;

/**
 * @since Class available since Release 2.0.0
 */
class FinalState implements StateInterface
{
    /**
     * @var Token
     *
     * @since Property available since Release 3.0.0
     */
    private $token;

    /**
     * @since Method available since Release 2.1.0
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getStateId()
    {
        return StateInterface::STATE_FINAL;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(Token $token)
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    public function hasToken(): bool
    {
        return $this->token !== null;
    }
}
