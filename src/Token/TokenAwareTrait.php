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

namespace Stagehand\FSM\Token;

/**
 * @since Trait available since Release 3.0.0
 */
trait TokenAwareTrait
{
    /**
     * @var Token|null
     */
    protected $token;

    /**
     * @param Token $token
     */
    public function setToken(Token $token)
    {
        $this->token = $token;
    }

    /**
     * @return Token|null
     */
    public function getToken()
    {
        $token = $this->token;
        $this->token = null;

        return $token;
    }

    /**
     * @return bool
     */
    public function hasToken(): bool
    {
        return $this->token !== null;
    }
}
