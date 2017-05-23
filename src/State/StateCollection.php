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
 * @since Class available since Release 2.2.0
 */
class StateCollection
{
    /**
     * @var array
     */
    private $states = [];

    /**
     * @param array $states
     */
    public function __construct(array $states = [])
    {
        $this->states = $states;
    }

    public function add(StateInterface $entity)
    {
        $this->states[$entity->getStateId()] = $entity;
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->states)) {
            return $this->states[$key];
        } else {
            return null;
        }
    }

    public function remove(StateInterface $entity)
    {
        if (array_key_exists($entity->getStateId(), $this->states)) {
        }
    }

    public function count()
    {
        return count($this->states);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->states);
    }

    public function toArray()
    {
        return $this->states;
    }
}
