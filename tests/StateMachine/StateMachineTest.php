<?php
/*
 * Copyright (c) 2006-2008, 2011-2015 KUBO Atsuhiro <kubo@iteman.jp>,
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
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @param EventInterface $event
     * @param callback
     * @param StateMachine   $stateMachine
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
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->stateMachineBuilder = new StateMachineBuilder('Registration');
        $this->stateMachineBuilder->addState('Input');
        $this->stateMachineBuilder->addState('Confirmation');
        $this->stateMachineBuilder->addState('Success');
        $this->stateMachineBuilder->addState('Validation');
        $this->stateMachineBuilder->addState('Registration');
        $this->stateMachineBuilder->setStartState('Input', array($this, 'logActionCall'));
        $this->stateMachineBuilder->addTransition('Input', 'next', 'Validation', array($this, 'logActionCall'));
        $this->stateMachineBuilder->addTransition('Validation', 'valid', 'Confirmation', array($this, 'logActionCall'));
        $this->stateMachineBuilder->addTransition('Validation', 'invalid', 'Input', array($this, 'logActionCall'));
        $this->stateMachineBuilder->addTransition('Confirmation', 'next', 'Registration', array($this, 'logActionCall'));
        $this->stateMachineBuilder->addTransition('Confirmation', 'prev', 'Input', array($this, 'logActionCall'));
        $this->stateMachineBuilder->addTransition('Registration', 'next', 'Success', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setEndState('Success', 'next', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setEntryAction('Input', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setActivity('Input', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setExitAction('Input', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setEntryAction('Confirmation', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setActivity('Confirmation', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setExitAction('Confirmation', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setEntryAction('Success', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setActivity('Success', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setExitAction('Success', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setEntryAction('Validation', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setActivity('Validation', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setExitAction('Validation', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setEntryAction('Registration', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setActivity('Registration', array($this, 'logActionCall'));
        $this->stateMachineBuilder->setExitAction('Registration', array($this, 'logActionCall'));
    }

    /**
     * @test
     *
     * @since Method available since Release 2.0.0
     */
    public function transitions()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
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
        $self = $this;
        $this->stateMachineBuilder->addTransition('Input', 'next', 'Validation', array($this, 'logActionCall'), function (EventInterface $event, $payload, StateMachine $stateMachine) use ($self) {
            $self->logActionCall($event, $payload, $stateMachine);

            return true;
        });
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
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
        $self = $this;
        $this->stateMachineBuilder->addTransition('Input', 'next', 'Validation', array($this, 'logActionCall'), function (EventInterface $event, $payload, StateMachine $stateMachine) use ($self) {
            $self->logActionCall($event, $payload, $stateMachine);

            return false;
        });
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
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
        $self = $this;
        $this->stateMachineBuilder->addTransition('Input', 'next', 'Validation', function (EventInterface $event, $payload, StateMachine $stateMachine) use ($self) {
            $payload->foo = 'baz';

            return false;
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
        $self = $this;
        $this->stateMachineBuilder->addTransition('Input', 'next', 'Validation', null, function (EventInterface $event, $payload, StateMachine $stateMachine) use ($self) {
            $payload->foo = 'baz';

            return true;
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
        $eventDispatcher->addListener(StateMachineEvents::EVENT_PROCESS, function (StateMachineEvent $event) use (&$events) {
            $events[] = $event;
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_EXIT, function (StateMachineEvent $event) use (&$events) {
            $events[] = $event;
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_TRANSITION, function (StateMachineEvent $event) use (&$events) {
            $events[] = $event;
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_ENTRY, function (StateMachineEvent $event) use (&$events) {
            $events[] = $event;
        });
        $eventDispatcher->addListener(StateMachineEvents::EVENT_DO, function (StateMachineEvent $event) use (&$events) {
            $events[] = $event;
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

        $this->assertThat($events[0]->getName(), $this->equalTo(StateMachineEvents::EVENT_PROCESS));
        $this->assertThat($events[0]->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[0]->getState()->getStateId(), $this->equalTo(StateInterface::STATE_INITIAL));
        $this->assertThat($events[0]->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\TransitionEventInterface'));
        $this->assertThat($events[0]->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_START));

        $this->assertThat($events[1]->getName(), $this->equalTo(StateMachineEvents::EVENT_EXIT));
        $this->assertThat($events[1]->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[1]->getState()->getStateId(), $this->equalTo(StateInterface::STATE_INITIAL));
        $this->assertThat($events[1]->getEvent(), $this->isNull());

        $this->assertThat($events[2]->getName(), $this->equalTo(StateMachineEvents::EVENT_TRANSITION));
        $this->assertThat($events[2]->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[2]->getState()->getStateId(), $this->equalTo(StateInterface::STATE_INITIAL));
        $this->assertThat($events[2]->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\TransitionEventInterface'));
        $this->assertThat($events[2]->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_START));

        $this->assertThat($events[3]->getName(), $this->equalTo(StateMachineEvents::EVENT_ENTRY));
        $this->assertThat($events[3]->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[3]->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[3]->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[3]->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_ENTRY));

        $this->assertThat($events[4]->getName(), $this->equalTo(StateMachineEvents::EVENT_DO));
        $this->assertThat($events[4]->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[4]->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[4]->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[4]->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_DO));

        $this->assertThat($events[5]->getName(), $this->equalTo(StateMachineEvents::EVENT_PROCESS));
        $this->assertThat($events[5]->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[5]->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[5]->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\TransitionEventInterface'));
        $this->assertThat($events[5]->getEvent()->getEventId(), $this->equalTo('insertCoin'));

        $this->assertThat($events[6]->getName(), $this->equalTo(StateMachineEvents::EVENT_EXIT));
        $this->assertThat($events[6]->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[6]->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[6]->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[6]->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_EXIT));

        $this->assertThat($events[7]->getName(), $this->equalTo(StateMachineEvents::EVENT_TRANSITION));
        $this->assertThat($events[7]->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[7]->getState()->getStateId(), $this->equalTo('locked'));
        $this->assertThat($events[7]->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\TransitionEventInterface'));
        $this->assertThat($events[7]->getEvent()->getEventId(), $this->equalTo('insertCoin'));

        $this->assertThat($events[8]->getName(), $this->equalTo(StateMachineEvents::EVENT_ENTRY));
        $this->assertThat($events[8]->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[8]->getState()->getStateId(), $this->equalTo('unlocked'));
        $this->assertThat($events[8]->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[8]->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_ENTRY));

        $this->assertThat($events[9]->getName(), $this->equalTo(StateMachineEvents::EVENT_DO));
        $this->assertThat($events[9]->getStateMachine(), $this->identicalTo($stateMachine));
        $this->assertThat($events[9]->getState()->getStateId(), $this->equalTo('unlocked'));
        $this->assertThat($events[9]->getEvent(), $this->isInstanceOf('Stagehand\FSM\Event\EventInterface'));
        $this->assertThat($events[9]->getEvent()->getEventId(), $this->equalTo(EventInterface::EVENT_DO));
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
     * @return array
     *
     * @since Method available since Release 2.3.0
     */
    public function provideUnserializedStateMachines()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('unserialize() will raise an error "Notice: Unable to unserialize: ...Id x out of range. in ..."');
        }

        $phpVersion = 'php'.PHP_MAJOR_VERSION.PHP_MINOR_VERSION;

        return array(
            array(unserialize(file_get_contents(__DIR__.'/'.basename(__FILE__, '.php').'/'.$phpVersion.'/StateMachine20.ser'))),
            array(unserialize(file_get_contents(__DIR__.'/'.basename(__FILE__, '.php').'/'.$phpVersion.'/StateMachine22.ser'))),
            array(unserialize(file_get_contents(__DIR__.'/'.basename(__FILE__, '.php').'/'.$phpVersion.'/StateMachine23.ser'))),
        );
    }

    /**
     * @param StateMachine $stateMachine20
     *
     * @since Method available since Release 2.2.0
     *
     * @test
     * @dataProvider provideUnserializedStateMachines
     */
    public function migratesAStateMachine(StateMachine $stateMachine)
    {
        $this->assertThat($stateMachine->getStateMachineId(), $this->equalTo('registration'));
        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('input'));
        $this->assertThat($stateMachine->getPreviousState()->getStateId(), $this->equalTo(StateInterface::STATE_INITIAL));
        $this->assertThat($stateMachine->getState(StateInterface::STATE_INITIAL)->getEvent(EventInterface::EVENT_START), $this->logicalNot($this->isNull()));
        $this->assertThat($stateMachine->getState(StateInterface::STATE_INITIAL)->getEvent(EventInterface::EVENT_START)->getNextState(), $this->logicalNot($this->isNull()));
        $this->assertThat($stateMachine->getState('input')->getEvent('confirmation')->getNextState(), $this->logicalNot($this->isNull()));
        $this->assertThat($stateMachine->getState('confirmation')->getEvent('success')->getNextState(), $this->logicalNot($this->isNull()));
        $this->assertThat($stateMachine->getState('success')->getEvent(StateInterface::STATE_FINAL)->getNextState(), $this->logicalNot($this->isNull()));

        $stateMachine->triggerEvent('confirmation');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('confirmation'));

        $stateMachine->triggerEvent('input');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('input'));

        $stateMachine->triggerEvent('confirmation');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('confirmation'));

        $stateMachine->triggerEvent('success');

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo('success'));

        $stateMachine->triggerEvent(StateInterface::STATE_FINAL);

        $this->assertThat($stateMachine->getCurrentState()->getStateId(), $this->equalTo(StateInterface::STATE_FINAL));
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
        $transitionLogs = $stateMachine->getTransitionLogs();

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
