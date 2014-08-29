<?php
/*
 * Copyright (c) 2013 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of Stagehand_FSM.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace Stagehand\FSM\StateMachine;

/**
 * @since Class available since Release 2.1.0
 */
final class StateMachineEvents
{
    const EVENT_PROCESS = 'statemachine.process';
    const EVENT_EXIT = 'statemachine.exit';
    const EVENT_TRANSITION = 'statemachine.transition';
    const EVENT_ENTRY = 'statemachine.entry';
    const EVENT_DO = 'statemachine.do';
}
