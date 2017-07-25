<?php

namespace Ostrolucky\AppEventDispatcher\Adapter;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;

class DoctrineEventManagerAdapter extends EventManager
{
    use LaxAdapterTrait;

    public function dispatchEvent($eventName, EventArgs $eventArgs = null)
    {
        $this->doLaxDispatch($eventName, $eventArgs);
    }

    public function getListeners($event = null)
    {
        $this->throwUnsupported();
    }

    public function hasListeners($event)
    {
        return $this->dispatcher->hasListener($event);
    }

    public function addEventListener($events, $listener)
    {
        $this->throwUnsupported();
    }

    public function removeEventListener($events, $listener)
    {
        $this->throwUnsupported();
    }

    public function addEventSubscriber(EventSubscriber $subscriber)
    {
        $this->throwUnsupported();
    }

    public function removeEventSubscriber(EventSubscriber $subscriber)
    {
        $this->throwUnsupported();
    }
}
