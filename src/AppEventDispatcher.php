<?php

namespace Ostrolucky\AppEventDispatcher;

class AppEventDispatcher implements HasListenerAwareLaxEventDispatcherInterface
{
    private $listeners = [];

    /**
     * {@inheritdoc}
     */
    public function dispatch($event, ...$listenerArguments)
    {
        if (!$this->hasListener($event)) {
            throw new AppEventDispatcherException('No listener has been attached for event "%s"', $event);
        }

        foreach ($this->listeners[$event] as $listener) {
            call_user_func_array($listener, $listenerArguments);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasListener($event)
    {
        return !empty($this->listeners[$event]);
    }

    /**
     * @param string   $event
     * @param callable $listener
     */
    public function attach($event, callable $listener)
    {
        if (isset($this->listeners[$event]) && in_array($listener, $this->listeners[$event])) {
            throw new AppEventDispatcherException('This listener is already attached for event "%s"', $event);
        }

        $this->listeners[$event][] = $listener;
    }

    /**
     * @param string   $event
     * @param callable $listener
     */
    public function detach($event, callable $listener)
    {
        if (!isset($this->listeners[$event]) || ($key = array_search($listener, $this->listeners[$event])) === false) {
            throw new AppEventDispatcherException('No such listener has been attached for event "%s"', $event);
        }

        unset($this->listeners[$event][$key]);
    }
}