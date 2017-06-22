<?php

namespace Ostrolucky\AppEventDispatcher\Bridge\Symfony;

use Symfony\Component\Workflow\Event\Event;

class WorkflowDispatcherBridge extends AbstractEventDispatcherBridge
{
    /**
     * @param Event $event
     * @return array
     */
    protected function decapsulateEvent($event)
    {
        return [$event->getSubject()];
    }
}