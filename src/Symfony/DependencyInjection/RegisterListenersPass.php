<?php

namespace Ostrolucky\AppEventDispatcher\Symfony\DependencyInjection;

use Ostrolucky\AppEventDispatcher\AppEventDispatcherException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegisterListenersPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $dispatcherService;

    /**
     * @var string
     */
    private $listenerTag;

    protected $listeners = [];

    public function __construct($dispatcherService = 'app.event_dispatcher', $listenerTag = 'app.event_listener')
    {
        $this->dispatcherService = $dispatcherService;
        $this->listenerTag = $listenerTag;
    }

    public function process(ContainerBuilder $container)
    {
        $parameterBag = $container->getParameterBag();

        foreach ($container->findTaggedServiceIds($this->listenerTag) as $id => $events) {
            $objectResource = $parameterBag->resolveValue($container->getDefinition($id)->getClass());
            $isSubscriber = false;

            if (is_subclass_of($objectResource, EventSubscriberInterface::class)) {
                $this->addSubscriber($id, $objectResource::getSubscribedEvents());
                $isSubscriber = true;
            }

            foreach ($events as $event) {
                if ($event && $isSubscriber) {
                    throw new AppEventDispatcherException(
                        'Service "%s" is an event subscriber, so why do you define listener specific '.
                        'tag attributes in service definition, instead of inside the class?', $id
                    );
                }
                if (isset($event['event'])) {
                    $this->addListener($id, $event);
                }
                elseif (!$isSubscriber) {
                    throw new AppEventDispatcherException(
                        'You need to define some events your listener service '.
                        '"%s" should listen to, or make it event subscriber.', $id
                    );
                }
            }
        }

        $definition = $container->findDefinition($this->dispatcherService);
        krsort($this->listeners);
        foreach (call_user_func_array('array_merge', $this->listeners) as $listener) {
            list($id, $event, $method) = $listener;
            $definition->addMethodCall('attach', [$event, [new Reference($id), $method]]);
        }
    }

    protected function addListener($id, array $event)
    {
        // generate callback method name from event name, if method name has not been provided
        if (!isset($event['method'])) {
            $closure = function ($matches) {
                return strtoupper($matches[0]);
            };
            $event['method'] = preg_replace_callback('/(?<=\b)[a-z]/i', $closure, $event['event']);
            $event['method'] = 'on'.preg_replace('/[^a-z0-9]/i', '', $event['method']);
        }

        $this->listeners[isset($event['priority']) ? $event['priority'] : 0][] = [$id, $event['event'], $event['method']];
    }

    protected function addSubscriber($id, array $subscribedEvents)
    {
        foreach ($subscribedEvents as $eventName => $params) {
            $params = (array)$params;

            if (is_string($params[0])) {
                $params = [$params];
            }

            foreach ($params as $listener) {
                $this->listeners[isset($listener[1]) ? $listener[1] : 0][] = [$id, $eventName, $listener[0]];
            }
        }
    }
}
