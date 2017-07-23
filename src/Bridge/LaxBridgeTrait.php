<?php

namespace Ostrolucky\AppEventDispatcher\Bridge;

use Ostrolucky\AppEventDispatcher\AppEventDispatcherException;
use Ostrolucky\AppEventDispatcher\LaxEventDispatcherInterface;

trait LaxBridgeTrait
{
    private $dispatcher;
    private $eventExtractor;
    private $dynamicDispatching = true;

    public function __construct(
        LaxEventDispatcherInterface $dispatcher,
        EventExtractorInterface $eventExtractor = null,
        $dynamicDispatching = true
    ) {
        $this->dispatcher = $dispatcher;
        $this->eventExtractor = $eventExtractor;
        $this->dynamicDispatching = $dynamicDispatching;
    }

    /**
     * @param string      $eventName
     * @param object|null $event
     * @return object|null
     */
    protected function doLaxDispatch($eventName, $event = null)
    {
        $listenerArguments = $this->eventExtractor ? $this->eventExtractor->extract($event) : [$event];

        try {
            $this->dispatcher->dispatch($eventName, ...$listenerArguments);
        } catch (AppEventDispatcherException $e) {
            if (!$this->dynamicDispatching) {
                throw $e;
            }
        }

        return $event;
    }

    private function throwUnsupported()
    {
        throw new \BadMethodCallException(sprintf('%s does not support this method', __CLASS__));
    }
}