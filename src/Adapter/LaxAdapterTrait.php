<?php

namespace Ostrolucky\AppEventDispatcher\Adapter;

use Ostrolucky\AppEventDispatcher\HasListenerAwareLaxEventDispatcherInterface;

trait LaxAdapterTrait
{
    private $dispatcher;
    private $eventExtractor;
    private $dynamicDispatching = true;

    public function __construct(
        HasListenerAwareLaxEventDispatcherInterface $dispatcher,
        EventExtractorInterface $eventExtractor = null,
        $makeSureDispatcherHasListenerForSuppliedEventBeforeDispatching = true
    ) {
        $this->dispatcher = $dispatcher;
        $this->eventExtractor = $eventExtractor;
        $this->dynamicDispatching = $makeSureDispatcherHasListenerForSuppliedEventBeforeDispatching;
    }

    /**
     * @param string      $eventName
     * @param object|null $event
     *
     * @return object|null
     */
    protected function doLaxDispatch($eventName, $event = null)
    {
        $listenerArguments = $this->eventExtractor === null ? [$event] : $this->eventExtractor->extract($event);

        if (!$this->dynamicDispatching || $this->dispatcher->hasListener($eventName)) {
            $this->dispatcher->dispatch($eventName, ...$listenerArguments);
        }

        return $event;
    }

    private function throwUnsupported()
    {
        throw new \BadMethodCallException(sprintf('%s does not support this method', __CLASS__));
    }
}
