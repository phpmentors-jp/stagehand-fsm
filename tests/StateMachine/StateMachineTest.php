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

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\State\State;
use Stagehand\FSM\State\StateInterface;
use Stagehand\FSM\StateMachine\StateMachineTest\CallableActionRunner;
use Stagehand\FSM\StateMachine\StateMachineTest\CallableGuardEvaluator;
use Stagehand\FSM\Transition\ActionRunnerInterface;
use Stagehand\FSM\Transition\GuardEvaluatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @since Class available since Release 0.1.0
 */
class StateMachineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StateMachineBuilder
     *
     * @since Property available since Release 2.0.0
     */
    protected $stateMachineBuilder;

    /**
     * @var array
     *
     * @since Property available since Release 2.0.0
     */
    protected $actionCalls = array();

    /**
     * @var ActionRunnerInterface
     *
     * @since Property available since Release 3.0.0
     */
    private $actionRunner;

    /**
     * @var GuardEvaluatorInterface
     *
     * @since Property available since Release 3.0.0
     */
    private $guardEvaluator;

    /**
     * @param EventInterface $event
     * @param mixed callback
     * @param StateMachine $stateMachine
     *
     * @since Method available since Release 2.0.0
     */
    public function logActionCall(EventInterface $event, $payload, StateMachine $stateMachine)
    {
        foreach (debug_backtrace() as $stackFrame) {
            if ($stackFrame['function'] == 'invokeAction' || $stackFrame['function'] == 'evaluateGuard') {
                $calledBy = $stackFrame['function'];
            }
        }

        $this->actionCalls[] = array(
            'state' => $stateMachine->getCurrentState()->getStateId(),
            'event' => $event->getEventId(),
            'calledBy' => @$calledBy,
        );
    }

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
        $this->stateMachineBuilder->addTransition('Input', 'next', 'Validation');
        $this->stateMachineBuilder->addTransition('Validation', 'valid', 'Confirmation');
        $this->stateMachineBuilder->addTransition('Validation', 'invalid', 'Input');
        $this->stateMachineBuilder->addTransition('Confirmation', 'next', 'Registration');
        $this->stateMachineBuilder->addTransition('Confirmation', 'prev', 'Input');
        $this->stateMachineBuilder->addTransition('Registration', 'next', 'Success');
        $this->stateMachineBuilder->setEndState('Success', 'next');

        $this->actionRunner = new CallableActionRunner(array($this, 'logActionCall'));
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function transitions()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->addActionRunner($this->actionRunner);
        $stateMachine->start();

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('Input'));
        $this->assertThat($stateMachine->getPreviousState()->getStateId(), $this->equalTo(StateInterface::STATE_INITIAL));

        $this->assertThat(count($this->actionCalls), $this->equalTo(3));
        $this->assertThat($this->actionCalls[0]['state'], $this->equalTo(StateInterface::STATE_INITIAL));
        $this->assertThat($this->actionCalls[0]['event'], $this->equalTo(EventInterface::EVENT_START));
        $this->assertThat($this->actionCalls[0]['calledBy'], $this->equalTo('invokeAction'));
        $this->assertThat($this->actionCalls[1]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionCalls[1]['event'], $this->equalTo(EventInterface::EVENT_ENTRY));
        $this->assertThat($this->actionCalls[1]['calledBy'], $this->equalTo('invokeAction'));
        $this->assertThat($this->actionCalls[2]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionCalls[2]['event'], $this->equalTo(EventInterface::EVENT_DO));
        $this->assertThat($this->actionCalls[2]['calledBy'], $this->equalTo('invokeAction'));

        $stateMachine->triggerEvent('next');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('Validation'));
        $this->assertThat($stateMachine->getPreviousState()->getStateId(), $this->equalTo('Input'));

        $this->assertThat(count($this->actionCalls), $this->equalTo(7));
        $this->assertThat($this->actionCalls[3]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionCalls[3]['event'], $this->equalTo(EventInterface::EVENT_EXIT));
        $this->assertThat($this->actionCalls[3]['calledBy'], $this->equalTo('invokeAction'));
        $this->assertThat($this->actionCalls[4]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionCalls[4]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionCalls[4]['calledBy'], $this->equalTo('invokeAction'));
        $this->assertThat($this->actionCalls[5]['state'], $this->equalTo('Validation'));
        $this->assertThat($this->actionCalls[5]['event'], $this->equalTo(EventInterface::EVENT_ENTRY));
        $this->assertThat($this->actionCalls[5]['calledBy'], $this->equalTo('invokeAction'));
        $this->assertThat($this->actionCalls[6]['state'], $this->equalTo('Validation'));
        $this->assertThat($this->actionCalls[6]['event'], $this->equalTo(EventInterface::EVENT_DO));
        $this->assertThat($this->actionCalls[6]['calledBy'], $this->equalTo('invokeAction'));
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

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo(StateInterface::STATE_FINAL));

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
        $stateMachine->addActionRunner($this->actionRunner);
        $stateMachine->addGuardEvaluator(new CallableGuardEvaluator(function (EventInterface $event, $payload, StateMachineInterface $stateMachine) {
            if ($stateMachine->getCurrentState()->getStateId() == 'Input' && $event->getEventId() == 'next') {
                $this->logActionCall($event, $payload, $stateMachine);

                return true;
            }

            return true;
        }));
        $stateMachine->start();
        $stateMachine->triggerEvent('next');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('Validation'));
        $this->assertThat($stateMachine->getPreviousState()->getStateId(), $this->equalTo('Input'));
        $this->assertThat(count($this->actionCalls), $this->equalTo(8));

        $this->assertThat($this->actionCalls[3]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionCalls[3]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionCalls[3]['calledBy'], $this->equalTo('evaluateGuard'));
        $this->assertThat($this->actionCalls[4]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionCalls[4]['event'], $this->equalTo(EventInterface::EVENT_EXIT));
        $this->assertThat($this->actionCalls[4]['calledBy'], $this->equalTo('invokeAction'));
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function doesNotTransitionToTheNextStateWhenTheGuardConditionIsFalse()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->addActionRunner($this->actionRunner);
        $stateMachine->addGuardEvaluator(new CallableGuardEvaluator(function (EventInterface $event, $payload, StateMachineInterface $stateMachine) {
            if ($stateMachine->getCurrentState()->getStateId() == 'Input' && $event->getEventId() == 'next') {
                $this->logActionCall($event, $payload, $stateMachine);

                return false;
            }

            return true;
        }));
        $stateMachine->start();
        $stateMachine->triggerEvent('next');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('Input'));
        $this->assertThat($stateMachine->getPreviousState()->getStateId(), $this->equalTo(StateInterface::STATE_INITIAL));
        $this->assertThat(count($this->actionCalls), $this->equalTo(5));

        $this->assertThat($this->actionCalls[3]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionCalls[3]['event'], $this->equalTo('next'));
        $this->assertThat($this->actionCalls[3]['calledBy'], $this->equalTo('evaluateGuard'));
        $this->assertThat($this->actionCalls[4]['state'], $this->equalTo('Input'));
        $this->assertThat($this->actionCalls[4]['event'], $this->equalTo(EventInterface::EVENT_DO));
        $this->assertThat($this->actionCalls[4]['calledBy'], $this->equalTo('invokeAction'));
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function passesTheUserDefinedPayloadToActions()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->addActionRunner($this->actionRunner);
        $stateMachine->addActionRunner(new CallableActionRunner(function (EventInterface $event, $payload, StateMachineInterface $stateMachine) {
            if ($stateMachine->getCurrentState()->getStateId() == 'Input' && $event->getEventId() == 'next') {
                $payload->foo = 'baz';
            }
        }));
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
        $stateMachine->addActionRunner($this->actionRunner);
        $stateMachine->addGuardEvaluator(new CallableGuardEvaluator(function (EventInterface $event, $payload, StateMachineInterface $stateMachine) {
            $this->logActionCall($event, $payload, $stateMachine);

            if ($stateMachine->getCurrentState()->getStateId() == 'Input' && $event->getEventId() == 'next') {
                $payload->foo = 'baz';
            }

            return true;
        }));
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
     *
     * @since Method available since Release 2.0.0
     */
    public function excludesThePayloadPropertyForSerialization()
    {
        $stateMachineBuilder = new StateMachineBuilder();
        $stateMachineBuilder->addState('locked');
        $stateMachineBuilder->addState('unlocked');
        $stateMachineBuilder->setStartState('locked');
        $stateMachineBuilder->addTransition('locked', 'insertCoin', 'unlocked');
        $stateMachineBuilder->addTransition('unlocked', 'pass', 'locked');
        $stateMachine = $stateMachineBuilder->getStateMachine();
        $stateMachine->setPayload(new \stdClass());

        $unserializedStateMachine = unserialize(serialize($stateMachine));

        $this->assertThat($unserializedStateMachine->getPayload(), $this->isNull());
    }

    /**
     * @test
     */
    public function dispatchesSystemEventsToListenersIfTheEventDispatcherHasBeenSet()
    {
        $events = array();
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(StateMachineEvents::EVENT_PROCESS, function (StateMachineEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) use (&$events) {
            $events[] = array('name' => $eventName, 'event' => $event);
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_EXIT, function (StateMachineEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) use (&$events) {
            $events[] = array('name' => $eventName, 'event' => $event);
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_TRANSITION, function (StateMachineEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) use (&$events) {
            $events[] = array('name' => $eventName, 'event' => $event);
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_ENTRY, function (StateMachineEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) use (&$events) {
            $events[] = array('name' => $eventName, 'event' => $event);
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_DO, function (StateMachineEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) use (&$events) {
            $events[] = array('name' => $eventName, 'event' => $event);
        });
        $stateMachineBuilder = new StateMachineBuilder();
        $stateMachineBuilder->addState('locked');
        $stateMachineBuilder->addState('unlocked');
        $stateMachineBuilder->setStartState('locked');
        $stateMachineBuilder->addTransition('locked', 'insertCoin', 'unlocked');
        $stateMachineBuilder->addTransition('unlocked', 'pass', 'locked');
        $stateMachine = $stateMachineBuilder->getStateMachine();
        $stateMachine->setEventDispatcher($eventDispatcher);
        $stateMachine->start();
        $stateMachine->triggerEvent('insertCoin');

        $this->assertThat(count($events), $this->equalTo(10));

        $this->assertThat($events[0]['name'], $this->equalTo(StateMachineEvents::EVENT_PROCESS));
        $this->assertThat($events[0]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[0]['event']->getState()->getStateId(), $this->equalTo(StateInterface::STATE_INITIAL));
        $this->assertThat($events[0]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\TransitionEventInterface'));
        $this->assertThat($events[0]['event']->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_START));

        $this->assertThat($events[1]['name'], $this->equalTo(StateMachineEvents::EVENT_EXIT));
        $this->assertThat($events[1]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[1]['event']->getState()->getStateId(), $this->equalTo(StateInterface::STATE_INITIAL));
        $this->assertThat($events[1]['event']->getEvent(), $this->isNull());

        $this->assertThat($events[2]['name'], $this->equalTo(StateMachineEvents::EVENT_TRANSITION));
        $this->assertThat($events[2]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[2]['event']->getState()->getStateId(), $this->equalTo(StateInterface::STATE_INITIAL));
        $this->assertThat($events[2]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\TransitionEventInterface'));
        $this->assertThat($events[2]['event']->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_START));

        $this->assertThat($events[3]['name'], $this->equalTo(StateMachineEvents::EVENT_ENTRY));
        $this->assertThat($events[3]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[3]['event']->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[3]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[3]['event']->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_ENTRY));

        $this->assertThat($events[4]['name'], $this->equalTo(StateMachineEvents::EVENT_DO));
        $this->assertThat($events[4]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[4]['event']->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[4]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[4]['event']->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_DO));

        $this->assertThat($events[5]['name'], $this->equalTo(StateMachineEvents::EVENT_PROCESS));
        $this->assertThat($events[5]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[5]['event']->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[5]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\TransitionEventInterface'));
        $this->assertThat($events[5]['event']->getEvent()->getEventId(), $this->equalTo('insertCoin'));

        $this->assertThat($events[6]['name'], $this->equalTo(StateMachineEvents::EVENT_EXIT));
        $this->assertThat($events[6]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[6]['event']->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[6]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[6]['event']->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_EXIT));

        $this->assertThat($events[7]['name'], $this->equalTo(StateMachineEvents::EVENT_TRANSITION));
        $this->assertThat($events[7]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[7]['event']->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[7]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\TransitionEventInterface'));
        $this->assertThat($events[7]['event']->getEvent()->getEventId(), $this->equalTo('insertCoin'));

        $this->assertThat($events[8]['name'], $this->equalTo(StateMachineEvents::EVENT_ENTRY));
        $this->assertThat($events[8]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[8]['event']->getState()->getStateId(), $this->equalTo('unlocked'));
        $this->assertThat($events[8]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[8]['event']->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_ENTRY));

        $this->assertThat($events[9]['name'], $this->equalTo(StateMachineEvents::EVENT_DO));
        $this->assertThat($events[9]['event']->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[9]['event']->getState()->getStateId(), $this->equalTo('unlocked'));
        $this->assertThat($events[9]['event']->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[9]['event']->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_DO));
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

        $expectedTransitionLogs = array(
            array(StateInterface::STATE_INITIAL, EventInterface::EVENT_START, 'Input'),
            array('Input', 'next', 'Validation'),
            array('Validation', 'valid', 'Confirmation'),
            array('Confirmation', 'next', 'Registration'),
            array('Registration', 'next', 'Success'),
            array('Success', 'next', StateInterface::STATE_FINAL),
        );

        $this->assertThat(count($transitionLogs), $this->equalTo(count($expectedTransitionLogs)));

        for ($i = 0; $i < count($transitionLogs); ++$i) {
            $this->assertThat($transitionLogs[$i]->getFromState()->getStateId(), $this->equalTo($expectedTransitionLogs[$i][0]));
            $this->assertThat($transitionLogs[$i]->getEvent()->getEventId(), $this->equalTo($expectedTransitionLogs[$i][1]));
            $this->assertThat($transitionLogs[$i]->getToState()->getStateId(), $this->equalTo($expectedTransitionLogs[$i][2]));
            $this->assertThat($transitionLogs[$i]->getTransitionDate(), $this->isInstanceOf('DateTime'));
        }
    }
}
