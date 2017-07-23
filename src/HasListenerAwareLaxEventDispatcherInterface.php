<?php

namespace Ostrolucky\AppEventDispatcher;

interface HasListenerAwareLaxEventDispatcherInterface extends LaxEventDispatcherInterface
{
    /**
     * Checks whether any of the attached callbacks are listening to supplied identifier ($event).
     *
     * @param string $event
     * @return bool
     */
    public function hasListener($event);
}