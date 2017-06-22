<?php

namespace Ostrolucky\AppEventDispatcher\Test\Symfony\Bridge;

use Concise\Core\TestCase;
use Ostrolucky\AppEventDispatcher\AppEventDispatcher;
use Ostrolucky\AppEventDispatcher\Bridge\Symfony\WorkflowDispatcherBridge;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class WorkflowDispatcherBridgeTest extends TestCase
{
    /** @var WorkflowDispatcherBridge */
    private $bridge;
    /** @var AppEventDispatcher */
    private $dispatcher;

    public function setUp()
    {
        $this->dispatcher = new AppEventDispatcher;
        $this->bridge = new WorkflowDispatcherBridge($this->dispatcher);
    }

    public function testCanBeInjected()
    {
        new Workflow(new Definition([], []), $this->mock(MarkingStoreInterface::class)->get(), $this->bridge);
    }

    public function testDispatch()
    {
        $subject = new \stdClass;
        /** @var Marking $marking */
        $marking = $this->mock(Marking::class)->get();
        /** @var Transition $transition */
        $transition = $this->mock(Transition::class)->get();
        $event = new Event($subject, $marking, $transition);
        $this->dispatcher->attach('foo', function(\stdClass $stdClass, Event $event) {});
        $this->bridge->dispatch('foo', $event);
    }
}