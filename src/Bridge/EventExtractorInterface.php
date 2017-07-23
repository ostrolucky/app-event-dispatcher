<?php

namespace Ostrolucky\AppEventDispatcher\Bridge;

interface EventExtractorInterface
{
    /**
     * Extracts data you want from object dispatched by 3rd party event dispatcher.
     * These data will be passed as variadic arguments to LaxEventDispatcher via bridge.
     *
     * @param object|null $event
     * @return array
     */
    public function extract($event = null);
}