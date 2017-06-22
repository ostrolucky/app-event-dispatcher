<?php

namespace Ostrolucky\AppEventDispatcher\Bridge;

use Ostrolucky\AppEventDispatcher\LaxEventDispatcherInterface;

trait LaxBridgeTrait
{
    private $dispatcher;

    public function __construct(LaxEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    protected function doLaxDispatch($eventName, $event = null)
    {
        $this->dispatcher->dispatch($eventName, ...array_merge($this->decapsulateEvent($event), [$event]));

        return $event;
    }

    private function throwUnsupported()
    {
        throw new \BadMethodCallException('This bridge supports dispatching only');
    }

    /**
     * @param $event
     * @return array
     */
    abstract protected function decapsulateEvent($event);
}