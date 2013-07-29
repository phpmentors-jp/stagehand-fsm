<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_FSM
 * @copyright  2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://opensource.org/licenses/BSD-2-Clause  The BSD 2-Clause License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

namespace Stagehand\FSM\StateMachine;

use Stagehand\FSM\Event\EventInterface;
use Stagehand\FSM\State\State;
use Stagehand\FSM\State\StateInterface;

/**
 * @package    Stagehand_FSM
 * @copyright  2006-2008, 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://opensource.org/licenses/BSD-2-Clause  The BSD 2-Clause License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class StateMachineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Stagehand\FSM\StateMachine\StateMachineBuilder
     * @since Property available since Release 2.0.0
     */
    protected $stateMachineBuilder;

    /**
     * @var array
     * @since Property available since Release 2.0.0
     */
    protected $actionCalls = array();

    /**
     * @param \Stagehand\FSM\Event\EventInterface $event
     * @param callback
     * @param \Stagehand\FSM\StateMachine\StateMachine $stateMachine
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
            'state' => $stateMachine->getCurrentState()->getStateID(),
            'event' => $event->getEventID(),
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
     * @since Method available since Release 2.0.0
     */
    public function transitions()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();
        $stateMachine->start();

        $this->assertThat($stateMachine->getCurrentState()->getStateID(), $this->equalTo('Input'));
        $this->assertThat($stateMachine->getPreviousState()->getStateID(), $this->equalTo(StateInterface::STATE_INITIAL));

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

        $this->assertThat($stateMachine->getCurrentState()->getStateID(), $this->equalTo('Validation'));
        $this->assertThat($stateMachine->getPreviousState()->getStateID(), $this->equalTo('Input'));

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

        $this->assertThat($stateMachine->getCurrentState()->getStateID(), $this->equalTo(StateInterface::STATE_FINAL));

        try {
            $stateMachine->triggerEvent('foo');
        } catch (StateMachineAlreadyShutdownException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @test
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

        $this->assertThat($stateMachine->getCurrentState()->getStateID(), $this->equalTo('Validation'));
        $this->assertThat($stateMachine->getPreviousState()->getStateID(), $this->equalTo('Input'));
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

        $this->assertThat($stateMachine->getCurrentState()->getStateID(), $this->equalTo('Input'));
        $this->assertThat($stateMachine->getPreviousState()->getStateID(), $this->equalTo(StateInterface::STATE_INITIAL));
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
     * @since Method available since Release 2.0.0
     */
    public function getsTheIdOfTheStateMachine()
    {
        $stateMachine = $this->stateMachineBuilder->getStateMachine();

        $this->assertThat($stateMachine->getStateMachineID(), $this->equalTo('Registration'));
    }

    /**
     * @test
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
    public function raisesAnExceptionIfTheStateIsAlreadyDefinedWhenAddingAState()
    {
        $stateMachine = new StateMachine();
        $stateMachine->addState(new State('foo'));

        try {
            $stateMachine->addState(new State('foo'));
        } catch (DuplicateStateException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
