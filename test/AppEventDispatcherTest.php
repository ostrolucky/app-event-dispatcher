<?php

namespace Ostrolucky\AppEventDispatcher\Test;

use Concise\Core\TestCase;
use Ostrolucky\AppEventDispatcher\AppEventDispatcher;

class AppEventDispatcherTest extends TestCase
{
    /** @var AppEventDispatcher */
    private $eventDispatcher;
    /** @var callable */
    private $eventListener;
    private $storage;

    public function setUp()
    {
        $this->eventDispatcher = new AppEventDispatcher();
        $this->eventListener = function ($option1 = null, $option2 = null) {
            $this->storage = [$option1, $option2];
        };
    }

    /**
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     */
    public function testDispatchWithoutAttachedListener()
    {
        $this->eventDispatcher->dispatch('notExisting');
    }

    public function testAttachAndDetach()
    {
        $this->assert($this->getProperty($this->eventDispatcher, 'listeners'))->equals([]);
        $this->eventDispatcher->attach('e', $this->eventListener);
        $this->assertArray($this->getProperty($this->eventDispatcher, 'listeners'))->countIs(1);

        $this->eventDispatcher->detach('e', $this->eventListener);
        $this->assert($this->getProperty($this->eventDispatcher, 'listeners'))->equals(['e' => []]);
        $this->assert($this->eventDispatcher->hasListener('e'))->isFalse;

        $this->eventDispatcher->attach('e', $this->eventListener);
        $this->assertArray($this->getProperty($this->eventDispatcher, 'listeners'))->countIs(1);

        $this->eventDispatcher->attach('e2', $this->eventListener);
        $this->assertArray($this->getProperty($this->eventDispatcher, 'listeners'))->countIs(2);
        $this->assertArray($this->getProperty($this->eventDispatcher, 'listeners')['e'])->countIs(1);
        $this->assertArray($this->getProperty($this->eventDispatcher, 'listeners')['e2'])->countIs(1);
    }

    /**
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     */
    public function testAttachWithAlreadyAttachedListener()
    {
        $this->eventDispatcher->attach('e', $this->eventListener);
        $this->eventDispatcher->attach('e', $this->eventListener);
    }

    public function testAttachWithSimilarListenerAlreadyAttached()
    {
        $this->eventDispatcher->attach('e', $this->eventListener);
        $this->eventDispatcher->attach('e', clone $this->eventListener);
    }

    /**
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     */
    public function testDetachWithoutAttachedListener()
    {
        $this->eventDispatcher->detach('e', $this->eventListener);
    }

    /**
     * This makes sure array_search is triggered instead of isset
     *
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     */
    public function testDoubleDetach()
    {
        $this->eventDispatcher->attach('e', $this->eventListener);
        $this->eventDispatcher->detach('e', $this->eventListener);
        $this->eventDispatcher->detach('e', $this->eventListener);
    }

    /**
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     */
    public function testDetachWithoutSuchListener()
    {
        $this->eventDispatcher->attach('e', function () {});
        $this->eventDispatcher->detach('yep', $this->eventListener);
    }

    /**
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     */
    public function testDetachWithSimilarListenerAlreadyAttachedButNotExactOne()
    {
        $this->eventDispatcher->attach('e', $this->eventListener);
        $this->eventDispatcher->detach('e', clone $this->eventListener);
    }

    /**
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     */
    public function testDispatchAfterDetachingAttachedListener()
    {
        $this->eventDispatcher->attach('e', $this->eventListener);
        $this->eventDispatcher->detach('e', $this->eventListener);
        $this->eventDispatcher->dispatch('e');
    }

    public function testDispatch()
    {
        $this->eventDispatcher->attach('e', $this->eventListener);
        $this->eventDispatcher->dispatch('e');
        $this->assert($this->storage)->equals([null, null]);

        $this->eventDispatcher->dispatch('e', 1);
        $this->assert($this->storage)->equals([1, null]);

        $stdClass = new \stdClass;
        $this->eventDispatcher->dispatch('e', $stdClass, 1);
        $this->assert($this->storage)->equals([$stdClass, 1]);

        $array = ['foo', 'baz'];
        $this->eventDispatcher->dispatch('e', $array, null);
        $this->assert($this->storage)->equals([$array, null]);
    }
}
