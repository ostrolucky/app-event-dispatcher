<?php

namespace Ostrolucky\AppEventDispatcher\Test\Adapter;

use Concise\Core\TestCase;
use Ostrolucky\AppEventDispatcher\AppEventDispatcher;
use Ostrolucky\AppEventDispatcher\Adapter\EventExtractorInterface;
use Ostrolucky\AppEventDispatcher\Adapter\SymfonyEventDispatcherAdapter;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class SymfonyEventDispatcherAdapterTest extends TestCase
{
    /** @var AppEventDispatcher */
    private $dispatcher;

    /** @var Event */
    private $workflowEvent;

    public function setUp()
    {
        $this->dispatcher = new AppEventDispatcher;
        /** @var Marking $marking */
        $marking = $this->mock(Marking::class)->get();
        /** @var Transition $transition */
        $transition = $this->mock(Transition::class)->get();
        $this->workflowEvent = new Event(new \stdClass, $marking, $transition);
    }

    public function testCanBeInjected()
    {
        new Workflow(new Definition([], []), $this->mock(MarkingStoreInterface::class)->get(), new SymfonyEventDispatcherAdapter($this->dispatcher));
    }

    public function testDispatchWithExtractor()
    {
        $this->dispatcher->attach('foo', function(\stdClass $stdClass, Event $event) {});
        $bridge = new SymfonyEventDispatcherAdapter($this->dispatcher, new WorkflowExtractor());
        $bridge->dispatch('foo', $this->workflowEvent);
    }

    public function testDispatchWithoutExtractor()
    {
        $this->dispatcher->attach('foo', function(Event $event) {});
        $bridge = new SymfonyEventDispatcherAdapter($this->dispatcher);
        $bridge->dispatch('foo', $this->workflowEvent);
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage null given
     */
    public function testDispatchNull()
    {
        $this->dispatcher->attach('foo', function(Event $event) {});
        $bridge = new SymfonyEventDispatcherAdapter($this->dispatcher);
        $bridge->dispatch('foo', null);
    }

    /**
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     */
    public function testDispatchWithErrorPropagation()
    {
        $bridge = new SymfonyEventDispatcherAdapter($this->dispatcher, null, false);
        $bridge->dispatch('foo');
    }
}

class WorkflowExtractor implements EventExtractorInterface
{
    /**
     * @param Event|null $event
     * @return array
     */
    public function extract($event = null)
    {
        return [$event->getSubject(), $event];
    }
}
