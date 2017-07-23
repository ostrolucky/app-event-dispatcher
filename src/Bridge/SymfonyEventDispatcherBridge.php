<?php

namespace Ostrolucky\AppEventDispatcher\Bridge;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SymfonyEventDispatcherBridge implements EventDispatcherInterface
{
    use LaxBridgeTrait;

    public function dispatch($eventName, Event $event = null)
    {
        return $this->doLaxDispatch($eventName, $event);
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->throwUnsupported();
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->throwUnsupported();
    }

    public function removeListener($eventName, $listener)
    {
        $this->throwUnsupported();
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->throwUnsupported();
    }

    public function getListeners($eventName = null)
    {
        $this->throwUnsupported();
    }

    public function getListenerPriority($eventName, $listener)
    {
        $this->throwUnsupported();
    }

    public function hasListeners($eventName = null)
    {
        return $this->dispatcher->hasListener($eventName);
    }
}