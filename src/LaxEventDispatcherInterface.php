<?php

namespace Ostrolucky\AppEventDispatcher;

/**
 * This interface has been created, because all of the current event dispatchers have too strict dispatching process.
 *
 * @author Gabriel Ostrolucký <gabriel.ostrolucky@gmail.com>
 */
interface LaxEventDispatcherInterface
{
    /**
     * Dispatch the event, which means call all of the callbacks which are attached to an identifier ($event).
     *
     * Does not enforce type or number of $listenerArguments
     * Does not even really enforce your $event to be string
     *
     * @param string $event
     * @param array  ...$listenerArguments
     */
    public function dispatch($event, ...$listenerArguments);
}
