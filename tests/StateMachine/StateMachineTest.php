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
use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\State\StateActionInterface;
use Stagehand\FSM\Transition\ActionRunnerInterface;
use Stagehand\FSM\Transition\GuardEvaluatorInterface;
use Stagehand\FSM\Transition\TransitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @since Class available since Release 0.1.0
 */
class StateMachineTest extends TestCase
{
    /**
     * @var StateMachineBuilder
     *
     * @since Property available since Release 2.0.0
     */
    protected $stateMachineBuilder;

    /**
     * @var ActionLogger
     *
     * @since Property available since Release 3.0.0
     */
    private $actionLogger;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->stateMachineBuilder = new StateMachineBuilder('Registration');
        $this->stateMachineBuilder->addState('Input');
        $this->stateMachineBuilder->addState('Confirmation');
        $this->stateMachineBuilder->addState('Success');
        $this->stateMachineBuilder->addState('Validation');
        $this->stateMachineBuilder->addState('Registration');
        $this->stateMachineBuilder->setStartState('Input');
        $this->stateMachineBuilder->addTransition('Input', 'Validation', 'next');
        $this->stateMachineBuilder->addTransition('Validation', 'Confirmation', 'valid');
        $this->stateMachineBuilder->addTransition('Validation', 'Input', 'invalid');
        $this->stateMachineBuilder->addTransition('Confirmation', 'Registration', 'next');
        $this->stateMachineBuilder->addTransition('Confirmation', 'Input', 'prev');
        $this->stateMachineBuilder->addTransition('Registration', 'Success', 'next');
        $this->stateMachineBuilder->setEndState('Success', 'next');

        $this->actionLogger = new ActionLogger();
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function transitions()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->addActionRunner($this->actionLogger);
        $stateMachine->start();

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('Input'));
        $this->assertThat($stateMachine->getPreviousState()->getStateId(), $this->equalTo(StateMachineInterface::STATE_INITIAL));

        $this->assertThat(count($this->actionLogger), $this->equalTo(3));
        $this->assertThat($this->actionLogger[0]['state'], $this->equalTo(StateMachineInterface::STATE_INITIAL));
        $this->assertThat($this->actionLogger[0]['event'], $this->equalTo(StateMachineInterface::EVENT_START));
        $this->assertThat($this->actionLogger[1]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionLogger[1]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[2]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionLogger[2]['event'], $this->equalTo(StateActionInterface::EVENT_DO));

        $stateMachine->triggerEvent('next');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('Validation'));
        $this->assertThat($stateMachine->getPreviousState()->getStateId(), $this->equalTo('Input'));

        $this->assertThat(count($this->actionLogger), $this->equalTo(7));
        $this->assertThat($this->actionLogger[3]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionLogger[3]['event'], $this->equalTo(StateActionInterface::EVENT_EXIT));
        $this->assertThat($this->actionLogger[4]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionLogger[4]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionLogger[5]['state'], $this->equalTo('Validation'));
        $this->assertThat($this->actionLogger[5]['event'], $this->equalTo(StateActionInterface::EVENT_ENTRY));
        $this->assertThat($this->actionLogger[6]['state'], $this->equalTo('Validation'));
        $this->assertThat($this->actionLogger[6]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function raisesAnExceptionWhenAnEventIsTriggeredOnTheFinalState()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->start();
        $stateMachine->triggerEvent('next');
        $stateMachine->triggerEvent('valid');
        $stateMachine->triggerEvent('next');
        $stateMachine->triggerEvent('next');
        $stateMachine->triggerEvent('next');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo(StateMachineInterface::STATE_FINAL));

        try {
            $stateMachine->triggerEvent('foo');
        } catch (StateMachineAlreadyShutdownException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function transitionsToTheNextStateWhenTheGuardConditionIsTrue()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->addActionRunner($this->actionLogger);
        $stateMachine->addGuardEvaluator($this->actionLogger);
        $this->actionLogger->setGuardEvaluator(new class() implements GuardEvaluatorInterface {
            /**
             * {@inheritdoc}
             */
            public function evaluate(EventInterface $event, $payload, StateMachineInterface $stateMachine, TransitionInterface $transition)
            {
                if ($stateMachine->getCurrentState()->getStateId() == StateMachineInterface::STATE_INITIAL && $event->getEventId() == StateMachineInterface::EVENT_START) {
                    return true;
                } elseif ($stateMachine->getCurrentState()->getStateId() == 'Input' && $event->getEventId() == 'next') {
                    return true;
                } else {
                    return false;
                }
            }
        });
        $stateMachine->start();
        $stateMachine->triggerEvent('next');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('Validation'));
        $this->assertThat($stateMachine->getPreviousState()->getStateId(), $this->equalTo('Input'));
        $this->assertThat(count($this->actionLogger), $this->equalTo(9));

        $this->assertThat($this->actionLogger[4]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionLogger[4]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionLogger[4]['calledBy'], $this->equalTo('evaluateGuard'));
        $this->assertThat($this->actionLogger[4]['result'], $this->isTrue());
        $this->assertThat($this->actionLogger[5]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionLogger[5]['event'], $this->equalTo(StateActionInterface::EVENT_EXIT));
        $this->assertThat($this->actionLogger[5]['calledBy'], $this->equalTo('runAction'));
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function doesNotTransitionToTheNextStateWhenTheGuardConditionIsFalse()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->addActionRunner($this->actionLogger);
        $stateMachine->addGuardEvaluator($this->actionLogger);
        $this->actionLogger->setGuardEvaluator(new class() implements GuardEvaluatorInterface {
            /**
             * {@inheritdoc}
             */
            public function evaluate(EventInterface $event, $payload, StateMachineInterface $stateMachine, TransitionInterface $transition)
            {
                if ($stateMachine->getCurrentState()->getStateId() == 'Input' && $event->getEventId() == 'next') {
                    return false;
                }

                return true;
            }
        });
        $stateMachine->start();
        $stateMachine->triggerEvent('next');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('Input'));
        $this->assertThat($stateMachine->getPreviousState()->getStateId(), $this->equalTo(StateMachineInterface::STATE_INITIAL));
        $this->assertThat(count($this->actionLogger), $this->equalTo(6));

        $this->assertThat($this->actionLogger[4]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionLogger[4]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionLogger[4]['calledBy'], $this->equalTo('evaluateGuard'));
        $this->assertThat($this->actionLogger[4]['result'], $this->isFalse());
        $this->assertThat($this->actionLogger[5]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionLogger[5]['event'], $this->equalTo(StateActionInterface::EVENT_DO));
        $this->assertThat($this->actionLogger[5]['calledBy'], $this->equalTo('runAction'));
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function passesTheUserDefinedPayloadToActions()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->addActionRunner(new class() implements ActionRunnerInterface {
            /**
             * {@inheritdoc}
             */
            public function run(EventInterface $event, $payload, StateMachineInterface $stateMachine, TransitionInterface $transition = null)
            {
                if (($transition === null ? $stateMachine->getCurrentState() : $transition->getFromState())->getStateId() == 'Input' && $event->getEventId() == 'next') {
                    $payload->foo = 'baz';
                }
            }
        });
        $payload = new \stdClass();
        $payload->foo = 'bar';
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->setPayload($payload);
        $stateMachine->start();
        $stateMachine->triggerEvent('next');

        $this->assertThat($payload->foo, $this->equalTo('baz'));
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function passesTheUserDefinedPayloadToGuards()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->addActionRunner($this->actionLogger);
        $stateMachine->addGuardEvaluator($this->actionLogger);
        $this->actionLogger->setGuardEvaluator(new class() implements GuardEvaluatorInterface {
            /**
             * {@inheritdoc}
             */
            public function evaluate(EventInterface $event, $payload, StateMachineInterface $stateMachine, TransitionInterface $transition)
            {
                if ($stateMachine->getCurrentState()->getStateId() == 'Input' && $event->getEventId() == 'next') {
                    $payload->foo = 'baz';
                }

                return true;
            }
        });
        $payload = new \stdClass();
        $payload->foo = 'bar';
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->setPayload($payload);
        $stateMachine->start();
        $stateMachine->triggerEvent('next');

        $this->assertThat($payload->foo, $this->equalTo('baz'));
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function getsTheIdOfTheStateMachine()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();

        $this->assertThat($stateMachine->getStateMachineId(), $this->equalTo('Registration'));
    }

    /**
     * @test
     */
    public function dispatchesSystemEventsToListenersIfTheEventDispatcherHasBeenSet()
    {
        $events = [];
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(StateMachineEvents::EVENT_PROCESS, function (StateMachineEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) use (&$events) {
            $events[] = ['name' => $eventName, 'event' => $event];
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_EXIT, function (StateMachineEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) use (&$events) {
            $events[] = ['name' => $eventName, 'event' => $event];
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_TRANSITION, function (StateMachineEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) use (&$events) {
            $events[] = ['name' => $eventName, 'event' => $event];
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_ENTRY, function (StateMachineEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) use (&$events) {
            $events[] = ['name' => $eventName, 'event' => $event];
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_DO, function (StateMachineEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) use (&$events) {
            $events[] = ['name' => $eventName, 'event' => $event];
        });
        $stateMachineBuilder = new StateMachineBuilder();
        $stateMachineBuilder->addState('locked');
        $stateMachineBuilder->addState('unlocked');
        $stateMachineBuilder->setStartState('locked');
        $stateMachineBuilder->addTransition('locked', 'unlocked', 'insertCoin');
        $stateMachineBuilder->addTransition('unlocked', 'locked', 'pass');
        $stateMachine = $stateMachineBuilder->getStateMachine();
        $stateMachine->setEventDispatcher($eventDispatcher);
        $stateMachine->start();
        $stateMachine->triggerEvent('insertCoin');

        $this->assertThat(count($events), $this->equalTo(9));

        $this->assertThat($events[0]['name'], $this->equalTo(StateMachineEvents::EVENT_PROCESS));
        $this->assertThat($events[0]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[0]['event']->getState()->getStateId(), $this->equalTo(StateMachineInterface::STATE_INITIAL));
        $this->assertThat($events[0]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[0]['event']->getEvent()->getEventId(), $this->equalTo(StateMachineInterface::EVENT_START));

        $this->assertThat($events[1]['name'], $this->equalTo(StateMachineEvents::EVENT_TRANSITION));
        $this->assertThat($events[1]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat(($events[1]['event']->getTransition() === null ? $events[1]['event']->getState() : $events[1]['event']->getTransition()->getFromState())->getStateId(), $this->equalTo(StateMachineInterface::STATE_INITIAL));
        $this->assertThat($events[1]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[1]['event']->getEvent()->getEventId(), $this->equalTo(StateMachineInterface::EVENT_START));

        $this->assertThat($events[2]['name'], $this->equalTo(StateMachineEvents::EVENT_ENTRY));
        $this->assertThat($events[2]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[2]['event']->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[2]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[2]['event']->getEvent()->getEventId(), $this->equalTo(StateActionInterface::EVENT_ENTRY));

        $this->assertThat($events[3]['name'], $this->equalTo(StateMachineEvents::EVENT_DO));
        $this->assertThat($events[3]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[3]['event']->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[3]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[3]['event']->getEvent()->getEventId(), $this->equalTo(StateActionInterface::EVENT_DO));

        $this->assertThat($events[4]['name'], $this->equalTo(StateMachineEvents::EVENT_PROCESS));
        $this->assertThat($events[4]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[4]['event']->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[4]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[4]['event']->getEvent()->getEventId(), $this->equalTo('insertCoin'));

        $this->assertThat($events[5]['name'], $this->equalTo(StateMachineEvents::EVENT_EXIT));
        $this->assertThat($events[5]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[5]['event']->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[5]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[5]['event']->getEvent()->getEventId(), $this->equalTo(StateActionInterface::EVENT_EXIT));

        $this->assertThat($events[6]['name'], $this->equalTo(StateMachineEvents::EVENT_TRANSITION));
        $this->assertThat($events[6]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat(($events[6]['event']->getTransition() === null ? $events[6]['event']->getState() : $events[6]['event']->getTransition()->getFromState())->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[6]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[6]['event']->getEvent()->getEventId(), $this->equalTo('insertCoin'));

        $this->assertThat($events[7]['name'], $this->equalTo(StateMachineEvents::EVENT_ENTRY));
        $this->assertThat($events[7]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[7]['event']->getState()->getStateId(), $this->equalTo('unlocked'));
        $this->assertThat($events[7]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[7]['event']->getEvent()->getEventId(), $this->equalTo(StateActionInterface::EVENT_ENTRY));

        $this->assertThat($events[8]['name'], $this->equalTo(StateMachineEvents::EVENT_DO));
        $this->assertThat($events[8]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[8]['event']->getState()->getStateId(), $this->equalTo('unlocked'));
        $this->assertThat($events[8]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[8]['event']->getEvent()->getEventId(), $this->equalTo(StateActionInterface::EVENT_DO));
    }

    /**
     * @test
     *
     * @since Method available since Release 2.1.0
     */
    public function returnsNullAsTheCurrentStateBeforeStartingTheStateMachine()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();

        $this->assertThat($stateMachine->getCurrentState(), $this->isNull());
    }

    /**
     * @test
     *
     * @since Method available since Release 2.1.0
     */
    public function returnsNullAsThePreviousStateBeforeStartingTheStateMachine()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();

        $this->assertThat($stateMachine->getPreviousState(), $this->isNull());
    }

    /**
     * @test
     *
     * @since Method available since Release 2.1.0
     */
    public function raisesAnExceptionWhenAnEventIsTriggeredBeforeStartingTheStateMachine()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();

        try {
            $stateMachine->triggerEvent('foo');
        } catch (StateMachineNotStartedException $e) {
            $this->assertTrue(true);

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @test
     *
     * @since Method available since Release 2.1.0
     */
    public function raisesAnExceptionWhenStartingTheStateMachineIfItIsAlreadyStarted()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->start();

        try {
            $stateMachine->start();
        } catch (StateMachineAlreadyStartedException $e) {
            $this->assertTrue(true);

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @test
     *
     * @since Method available since Release 2.3.0
     */
    public function logsTransitions()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->start();
        $stateMachine->triggerEvent('next');
        $stateMachine->triggerEvent('valid');
        $stateMachine->triggerEvent('next');
        $stateMachine->triggerEvent('next');
        $stateMachine->triggerEvent('next');
        $transitionLogs = $stateMachine->getTransitionLog();

        $expectedTransitionLogs = [
            [StateMachineInterface::STATE_INITIAL, StateMachineInterface::EVENT_START, 'Input'],
            ['Input', 'next', 'Validation'],
            ['Validation', 'valid', 'Confirmation'],
            ['Confirmation', 'next', 'Registration'],
            ['Registration', 'next', 'Success'],
            ['Success', 'next', StateMachineInterface::STATE_FINAL],
        ];

        $this->assertThat(count($transitionLogs), $this->equalTo(count($expectedTransitionLogs)));

        for ($i = 0; $i < count($transitionLogs); ++$i) {
            $this->assertThat($transitionLogs[$i]->getFromState()->getStateId(), $this->equalTo($expectedTransitionLogs[$i][0]));
            $this->assertThat($transitionLogs[$i]->getEvent()->getEventId(), $this->equalTo($expectedTransitionLogs[$i][1]));
            $this->assertThat($transitionLogs[$i]->getToState()->getStateId(), $this->equalTo($expectedTransitionLogs[$i][2]));
            $this->assertThat($transitionLogs[$i]->getTransitionDate(), $this->isInstanceOf('DateTime'));
        }
    }
}
