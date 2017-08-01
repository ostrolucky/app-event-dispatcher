<?php

namespace Ostrolucky\AppEventDispatcher\Test\Adapter;

use Concise\Core\TestCase;
use Ostrolucky\AppEventDispatcher\Adapter\EventExtractorInterface;
use Ostrolucky\AppEventDispatcher\Adapter\SymfonyEventDispatcherAdapter;
use Ostrolucky\AppEventDispatcher\AppEventDispatcher;
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
        $this->dispatcher->attach('foo', function (\stdClass $stdClass, Event $event) {});
        $adapter = new SymfonyEventDispatcherAdapter($this->dispatcher, new WorkflowExtractor);
        $adapter->dispatch('foo', $this->workflowEvent);
    }

    /**
     * @expectedException \LogicException
     */
    public function testDispatchWithInvalidExtractor()
    {
        $adapter = new SymfonyEventDispatcherAdapter($this->dispatcher, new InvalidExtractor);
        $adapter->dispatch('foo');
    }

    public function testDispatchWithoutExtractor()
    {
        $this->dispatcher->attach('foo', function (Event $event) {});
        $adapter = new SymfonyEventDispatcherAdapter($this->dispatcher);
        $adapter->dispatch('foo', $this->workflowEvent);
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage null given
     */
    public function testDispatchNull()
    {
        $this->dispatcher->attach('foo', function (Event $event) {});
        $adapter = new SymfonyEventDispatcherAdapter($this->dispatcher);
        $adapter->dispatch('foo', null);
    }

    /**
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     */
    public function testDispatchWithErrorPropagation()
    {
        $adapter = new SymfonyEventDispatcherAdapter($this->dispatcher, null, false);
        $adapter->dispatch('foo');
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

class InvalidExtractor implements EventExtractorInterface
{
    public function extract($event = null)
    {
        throw new \LogicException();
    }
}
