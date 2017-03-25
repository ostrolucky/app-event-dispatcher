<?php

namespace Ostrolucky\AppEventDispatcher\Test;


use Concise\Core\TestCase;
use Ostrolucky\AppEventDispatcher\Symfony\DependencyInjection\RegisterListenersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegisterListenersPassTest extends TestCase
{
    /** @var ContainerBuilder */
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->register('app.event_dispatcher', 'class');
    }

    public function testRegisterListenerWithNoMethod()
    {
        $this->container->register('a', 'class')->addTag('app.event_listener', ['event' => 'kernel.request']);
        $this->container
            ->register('b', 'class')
            ->addTag('app.event_listener', ['event' => 'kernel.request', 'method' => 'stuffMethod', 'priority' => 1]);
        /** @var RegisterListenersPass $registerListenerPass */
        $registerListenerPass = $this->niceMock(RegisterListenersPass::class)->expect('addSubscriber')->never()->get();
        $registerListenerPass->process($this->container);

        $definition = $this->container->getDefinition('app.event_dispatcher');
        $this->assertEquals([
            ['attach', ['kernel.request', [new Reference('b'), 'stuffMethod']]],
            ['attach', ['kernel.request', [new Reference('a'), 'onKernelRequest']]],
        ],$definition->getMethodCalls());
    }

    /**
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     * @expectedExceptionMessageRegExp /is an event subscriber, so why do you define listener specific tag/
     */
    public function testRegisterListenerWhichIsAlreadySubscriber()
    {
        $this->container->register('a', ValidSubscriber::class)->addTag('app.event_listener', ['event' => 'event1']);
        (new RegisterListenersPass)->process($this->container);
    }

    /**
     * @expectedException \Ostrolucky\AppEventDispatcher\AppEventDispatcherException
     * @expectedExceptionMessageRegExp /You need to define some events/
     */
    public function testRegisterListenerWithoutEvents()
    {
        $this->container->register('a', 'class')->addTag('app.event_listener');
        (new RegisterListenersPass)->process($this->container);
    }

    public function testRegisterSubscriber()
    {
        $this->container->register('a', ValidSubscriber::class)->addTag('app.event_listener');
        /** @var RegisterListenersPass $registerListenerPass */
        $registerListenerPass = $this->niceMock(RegisterListenersPass::class)->expect('addListener')->never()->get();
        $registerListenerPass->process($this->container);

        $definition = $this->container->getDefinition('app.event_dispatcher');
        $this->assertEquals([
            ['attach', ['event3', [new Reference('a'), 'method5']]],
            ['attach', ['event1', [new Reference('a'), 'method1']]],
            ['attach', ['event2', [new Reference('a'), 'method2']]],
            ['attach', ['event3', [new Reference('a'), 'method4']]],
            ['attach', ['event3', [new Reference('a'), 'method3']]],
        ],$definition->getMethodCalls());
    }
}

class ValidSubscriber implements EventSubscriberInterface {

    public static function getSubscribedEvents()
    {
        return [
            'event1' => 'method1',
            'event2' => ['method2'],
            'event3' => [['method3', -10], ['method4'], ['method5', 1]],
        ];
    }
}
