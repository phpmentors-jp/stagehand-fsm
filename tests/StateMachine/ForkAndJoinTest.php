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

namespace Stagehand\FSM\StateMachine;

use PHPUnit\Framework\TestCase;
use Stagehand\FSM\State\StateActionInterface;

/**
 * @since Class available since Release 3.0.0
 */
class ForkAndJoinTest extends TestCase
{
    /**
     * @var StateMachineBuilder
     */
    private $builder;

    /**
     * @var ActionLogger
     */
    private $actionLogger;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->builder = new StateMachineBuilder('ForkAndJoin');
        $this->builder->addState('FillOrder');
        $this->builder->addForkState('FORK');
        $this->builder->addState('ProcessOrder');
        $this->builder->addChild('ProcessOrder', 'AcceptOrder', function (StateMachineBuilder $builder) {
            $builder->addState('AcceptOrder1');
            $builder->addState('AcceptOrder2');
            $builder->setStartState('AcceptOrder1');
            $builder->setEndState('AcceptOrder2', 'next');
            $builder->addTransition('AcceptOrder1', 'AcceptOrder2', 'next');
        });
        $this->builder->addChild('ProcessOrder', 'ShipOrder', function (StateMachineBuilder $builder) {
            $builder->addState('ShipOrder1');
            $builder->addState('ShipOrder2');
            $builder->setStartState('ShipOrder1');
            $builder->setEndState('ShipOrder2', 'next');
            $builder->addTransition('ShipOrder1', 'ShipOrder2', 'next');
        });
        $this->builder->addJoinState('JOIN');
        $this->builder->addState('CloseOrder');
        $this->builder->setStartState('FillOrder');
        $this->builder->setEndState('CloseOrder', 'next');
        $this->builder->addTransition('FillOrder', 'FORK', 'next');
        $this->builder->addTransition('FORK', 'ProcessOrder');
        $this->builder->addTransition('ProcessOrder', 'JOIN');
        $this->builder->addTransition('JOIN', 'CloseOrder', 'next');

        $this->actionLogger = new ActionLogger();
    }

    /**
     * @test
     */
    public function forkAndJoin()
    {
        $stateMachine = $this->builder->getStateMachine();
        $stateMachine->addActionRunner($this->actionLogger);
        $stateMachine->start();

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('FillOrder'));

        $stateMachine->triggerEvent('next');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('ProcessOrder'));

        $childStateMachine = $stateMachine->getCurrentState()->getChild('AcceptOrder');

        $this->assertThat($childStateMachine->getCurrentState()->getStateId(), $this->equalTo('AcceptOrder1'));

        $childStateMachine->triggerEvent('next');

        $this->assertThat($childStateMachine->getCurrentState()->getStateId(), $this->equalTo('AcceptOrder2'));

        $childStateMachine->triggerEvent('next');

        $this->assertThat($childStateMachine->isEnded(), $this->equalTo(true));

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->logicalNot($this->equalTo('CloseOrder')));

        $childStateMachine = $stateMachine->getCurrentState()->getChild('ShipOrder');

        $this->assertThat($childStateMachine->getCurrentState()->getStateId(), $this->equalTo('ShipOrder1'));

        $childStateMachine->triggerEvent('next');

        $this->assertThat($childStateMachine->getCurrentState()->getStateId(), $this->equalTo('ShipOrder2'));

        $childStateMachine->triggerEvent('next');

        $this->assertThat($childStateMachine->isEnded(), $this->equalTo(true));

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('CloseOrder'));

        $this->assertThat(count($this->actionLogger), $this->equalTo(37));
        $this->assertThat($this->actionLogger[0]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[0]['state'], $this->equalTo(StateMachineInterface::STATE_INITIAL));
        $this->assertThat($this->actionLogger[0]['event'], $this->equalTo(StateMachineInterface::EVENT_START));
        $this->assertThat($this->actionLogger[1]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[1]['state'], $this->equalTo('FillOrder'));
        $this->assertThat($this->actionLogger[1]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[2]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[2]['state'], $this->equalTo('FillOrder'));
        $this->assertThat($this->actionLogger[2]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
        $this->assertThat($this->actionLogger[3]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[3]['state'], $this->equalTo('FillOrder'));
        $this->assertThat($this->actionLogger[3]['event'], $this->equalTo(StateActionInterface::EVENT_EXIT));
        $this->assertThat($this->actionLogger[4]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[4]['state'], $this->equalTo('FillOrder'));
        $this->assertThat($this->actionLogger[4]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionLogger[5]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[5]['state'], $this->equalTo('FORK'));
        $this->assertThat($this->actionLogger[5]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[6]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[6]['state'], $this->equalTo('FORK'));
        $this->assertThat($this->actionLogger[6]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
        $this->assertThat($this->actionLogger[7]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[7]['state'], $this->equalTo('FORK'));
        $this->assertThat($this->actionLogger[7]['event'], $this->equalTo(StateActionInterface::EVENT_EXIT));
        $this->assertThat($this->actionLogger[8]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[8]['state'], $this->equalTo('FORK'));
        $this->assertThat($this->actionLogger[8]['event'], $this->equalTo(StateMachineInterface::EVENT_FORK));
        $this->assertThat($this->actionLogger[9]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[9]['state'], $this->equalTo('ProcessOrder'));
        $this->assertThat($this->actionLogger[9]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[10]['stateMachine'], $this->equalTo('AcceptOrder'));
        $this->assertThat($this->actionLogger[10]['state'], $this->equalTo(StateMachineInterface::STATE_INITIAL));
        $this->assertThat($this->actionLogger[10]['event'], $this->equalTo(StateMachineInterface::EVENT_START));
        $this->assertThat($this->actionLogger[11]['stateMachine'], $this->equalTo('AcceptOrder'));
        $this->assertThat($this->actionLogger[11]['state'], $this->equalTo('AcceptOrder1'));
        $this->assertThat($this->actionLogger[11]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[12]['stateMachine'], $this->equalTo('AcceptOrder'));
        $this->assertThat($this->actionLogger[12]['state'], $this->equalTo('AcceptOrder1'));
        $this->assertThat($this->actionLogger[12]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
        $this->assertThat($this->actionLogger[13]['stateMachine'], $this->equalTo('ShipOrder'));
        $this->assertThat($this->actionLogger[13]['state'], $this->equalTo(StateMachineInterface::STATE_INITIAL));
        $this->assertThat($this->actionLogger[13]['event'], $this->equalTo(StateMachineInterface::EVENT_START));
        $this->assertThat($this->actionLogger[14]['stateMachine'], $this->equalTo('ShipOrder'));
        $this->assertThat($this->actionLogger[14]['state'], $this->equalTo('ShipOrder1'));
        $this->assertThat($this->actionLogger[14]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[15]['stateMachine'], $this->equalTo('ShipOrder'));
        $this->assertThat($this->actionLogger[15]['state'], $this->equalTo('ShipOrder1'));
        $this->assertThat($this->actionLogger[15]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
        $this->assertThat($this->actionLogger[16]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[16]['state'], $this->equalTo('ProcessOrder'));
        $this->assertThat($this->actionLogger[16]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
        $this->assertThat($this->actionLogger[17]['stateMachine'], $this->equalTo('AcceptOrder'));
        $this->assertThat($this->actionLogger[17]['state'], $this->equalTo('AcceptOrder1'));
        $this->assertThat($this->actionLogger[17]['event'], $this->equalTo(StateActionInterface::EVENT_EXIT));
        $this->assertThat($this->actionLogger[18]['stateMachine'], $this->equalTo('AcceptOrder'));
        $this->assertThat($this->actionLogger[18]['state'], $this->equalTo('AcceptOrder1'));
        $this->assertThat($this->actionLogger[18]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionLogger[19]['stateMachine'], $this->equalTo('AcceptOrder'));
        $this->assertThat($this->actionLogger[19]['state'], $this->equalTo('AcceptOrder2'));
        $this->assertThat($this->actionLogger[19]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[20]['stateMachine'], $this->equalTo('AcceptOrder'));
        $this->assertThat($this->actionLogger[20]['state'], $this->equalTo('AcceptOrder2'));
        $this->assertThat($this->actionLogger[20]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
        $this->assertThat($this->actionLogger[21]['stateMachine'], $this->equalTo('AcceptOrder'));
        $this->assertThat($this->actionLogger[21]['state'], $this->equalTo('AcceptOrder2'));
        $this->assertThat($this->actionLogger[21]['event'], $this->equalTo(StateActionInterface::EVENT_EXIT));
        $this->assertThat($this->actionLogger[22]['stateMachine'], $this->equalTo('AcceptOrder'));
        $this->assertThat($this->actionLogger[22]['state'], $this->equalTo('AcceptOrder2'));
        $this->assertThat($this->actionLogger[22]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionLogger[23]['stateMachine'], $this->equalTo('ShipOrder'));
        $this->assertThat($this->actionLogger[23]['state'], $this->equalTo('ShipOrder1'));
        $this->assertThat($this->actionLogger[23]['event'], $this->equalTo(StateActionInterface::EVENT_EXIT));
        $this->assertThat($this->actionLogger[24]['stateMachine'], $this->equalTo('ShipOrder'));
        $this->assertThat($this->actionLogger[24]['state'], $this->equalTo('ShipOrder1'));
        $this->assertThat($this->actionLogger[24]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionLogger[25]['stateMachine'], $this->equalTo('ShipOrder'));
        $this->assertThat($this->actionLogger[25]['state'], $this->equalTo('ShipOrder2'));
        $this->assertThat($this->actionLogger[25]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[26]['stateMachine'], $this->equalTo('ShipOrder'));
        $this->assertThat($this->actionLogger[26]['state'], $this->equalTo('ShipOrder2'));
        $this->assertThat($this->actionLogger[26]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
        $this->assertThat($this->actionLogger[27]['stateMachine'], $this->equalTo('ShipOrder'));
        $this->assertThat($this->actionLogger[27]['state'], $this->equalTo('ShipOrder2'));
        $this->assertThat($this->actionLogger[27]['event'], $this->equalTo(StateActionInterface::EVENT_EXIT));
        $this->assertThat($this->actionLogger[28]['stateMachine'], $this->equalTo('ShipOrder'));
        $this->assertThat($this->actionLogger[28]['state'], $this->equalTo('ShipOrder2'));
        $this->assertThat($this->actionLogger[28]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionLogger[29]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[29]['state'], $this->equalTo('ProcessOrder'));
        $this->assertThat($this->actionLogger[29]['event'], $this->equalTo(StateActionInterface::EVENT_EXIT));
        $this->assertThat($this->actionLogger[30]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[30]['state'], $this->equalTo('ProcessOrder'));
        $this->assertThat($this->actionLogger[30]['event'], $this->equalTo(StateMachineInterface::EVENT_JOIN));
        $this->assertThat($this->actionLogger[31]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[31]['state'], $this->equalTo('JOIN'));
        $this->assertThat($this->actionLogger[31]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[32]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[32]['state'], $this->equalTo('JOIN'));
        $this->assertThat($this->actionLogger[32]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
        $this->assertThat($this->actionLogger[33]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[33]['state'], $this->equalTo('JOIN'));
        $this->assertThat($this->actionLogger[33]['event'], $this->equalTo(StateActionInterface::EVENT_EXIT));
        $this->assertThat($this->actionLogger[34]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[34]['state'], $this->equalTo('JOIN'));
        $this->assertThat($this->actionLogger[34]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionLogger[35]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[35]['state'], $this->equalTo('CloseOrder'));
        $this->assertThat($this->actionLogger[35]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[36]['stateMachine'], $this->equalTo('ForkAndJoin'));
        $this->assertThat($this->actionLogger[36]['state'], $this->equalTo('CloseOrder'));
        $this->assertThat($this->actionLogger[36]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
    }
}
