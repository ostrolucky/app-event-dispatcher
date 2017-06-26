# AppEventDispatcher
Most event dispatchers are designed to be used as hook system by frameworks and other libraries. This one is different. 
It's strictly for use in **domain of your application**. This allows it to:

* [Expose less restrictive public dispatching interface](#1-less-restrictive-public-dispatching-interface): easier usage
* [Do stricter validation](#2-stricter-validation): catch bugs in your application early
* [Skip problematic features](#3-skip-problematic-features): have less things to worry about 


## Install

Via Composer

``` bash
composer require ostrolucky/app-event-dispatcher:dev-master
```

## Requirements
* For basic functionality, all you need is PHP >= 5.6.
* If you would like to use my compiler pass, you'll need `symfony/dependency-injection:*`
* If you would like to utilize event subscriber feature in my compiler pass, you will need `symfony/event-dispatcher:*`

## Usage
Basic usage is following:
```php
$dispatcher = new Ostrolucky\AppEventDispatcher\AppEventDispatcher();
$dispatcher->attach('my.event.name', function(array $parameter1, \stdClass $parameter2) {
    var_dump($parameter1, $parameter2);
}));
$dispatcher->dispatch('my.event.name', ['hey'], new \stdClass);
```
If you use [Symfony built in event dispatcher](http://symfony.com/doc/current/event_dispatcher.html) for dispatching your domain events, 
I provide compiler pass you can easily use as compatible replacement.

All you need to do is:
1. Define this event dispatcher as a service, such as:
    ```yml
    # services.yml:
    app.event_dispatcher:
        class: Ostrolucky\AppEventDispatcher\AppEventDispatcher
    ```
1.  Add RegisterListenersPass in your main bundle, as such:
    ```php
    class AppBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);
            $container->addCompilerPass(new Ostrolucky\AppEventDispatcher\Symfony\DependencyInjection\RegisterListenersPass());
        }
    }
    ```
1. Replace `kernel.event_subscriber` and `kernel.event_listener` tags with `app.event_listener`. Yes, you no longer need to 
differentiate between them. It's going to be treated as a subscriber if it implements EventSubscriber interface, otherwise 
it's going to be treated as regular listener. Don't worry, it will also alert you when you try to define events in a tag for a subscriber.

1. In places you dispatch events, replace `event_dispatcher` service with `app.event_dispatcher`

## Advantages in detail

### 1. Less restrictive public dispatching interface
In contrast to most event dispatchers, this dispatcher encourages you to shift responsibility of argument signature
validation into event listener. You are free to dispatch directly whatever arguments your listener needs. Any types, any number of them.

In most dispatchers you are forced to wrap all of the arguments into single argument. In case of Symfony event 
dispatcher it's event object:
```php
// symfony event dispatcher
$event = new \Symfony\Component\EventDispatcher\GenericEvent(null, [
    'group' => null, 'user' => new User(), 'array' => [3, 5]
    ]
);
$eventDispatcher->dispatch('some.event', $event);

// vs. this dispatcher
$appEventDisdpatcher->dispatch('some.event', null, new User(), [3, 5]);
```
Then in listener if you want to ensure correct arguments are passed, in most dispatchers you are forced to unwrap 
it and check the types manually:

```php
public function onSomeEvent(GenericEvent $event) {
    $arguments = $event->getArguments();
    if (isset($arguments['group']) && !$arguments['group'] instanceof Group) {
        throw new \InvalidArgumentException('Invalid group');
    }
    
    if (isset($arguments['user']) && !$arguments['user'] instanceof User) {
        throw new \InvalidArgumentException('Invalid user');
    }
    
    if (!is_array($arguments['array'])) {
        throw new \InvalidArgumentException('Invalid array');
    }
    
    /** @var Group $group */
    $group = $arguments['group'] ?? null;
    /** @var User $user */
    $user = $arguments['user'] ?? null;
    $array = $arguments['array'];
    
    // do the actual work...
}
```
vs. this dispatcher:
```php
public function onSomeEvent(?Group $group, ?User $user, array $array) {
    // do the actual work
}

```

As you can see, symfony event dispatcher violates [DRY](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself) hard 
and makes writing new listeners very repetitive with lot of boilerplate. And even if you decide type safety isn't worth
it so you skip all of this argument validation, you are still required to write annotations if you want your IDE 
understand what type of arguments you are working with. 


To be fair, in case of symfony event dispatcher you are encouraged to write custom event class instead, where you can utilize 
type hints. In that case you can move argument validation into this class. However you need to do this again every time
your new listener requires different parameters and you still need to write boilerplate code for injecting these parameters
into your new event object and retrieving them. That's why it's in practice in non-library applications almost never 
done and some generic event class is used instead.
### 2. Stricter validation
Since this dispatcher isn't meant to be used as a hook system, it allows it to do stricter validation:
1. Alert you when you are trying to dispatch event for which you did not attach any event listener. 
There is number of reasons this could happen:
    * You forgot to attach event listener for this event, or you made a mistake during this process
    * You removed all listeners listening to provided event and forgot to remove dispatching code for this event
    * You made a typo in event name
    * You are dispatching event dynamically, but don't check if something is listening for it
1. Alert you when you are trying to attach listener which is already attached, or when you are trying to detach listener
which isn't attached. It's a sign of a bug in your application,
because something in your code is trying to do operation which has already been done.

None of these cases are handled by other dispatchers.

### 3. Skip problematic features
Number of problematic features have been purposely left out because they don't make much sense when you have full control
over attaching. This allows to make this dispatcher super lightweight: 

1. Hooks. This dispatcher doesn't expect javascript style listeners, which can stop propagation. If you need to do this, 
you can implement it easily with single listener which redirects the call further, according your constraints. This
allows you to be confident that all of the attached listeners will always be called.
1. Priorities inside event dispatcher. This dispatcher itself is FIFO style and as such it does not do any sorting. 
Correct place to do this is in code which attaches listeners to dispatcher. In case of Symfony framework you can use my
compiler pass and it will attach listeners in correct order based on priorities you specify.
1. "Event subscribers" inside of event dispatcher. This shouldn't be a responsibility of event dispatcher,
but responsibility of code which you use to attach listeners to dispatcher. It's easy to create own implementation by simply iterating over 
the list of `events|callbacks` and attach them them to event dispatcher in regular way. Still, this is supported in
my compiler pass for Symfony framework.

## FAQ
**Q**: What's the point of using dispatcher for application events, instead of doing direct service calls?

**A**: I agree that most people are doing it wrong and they should use direct service calls instead, because using
dispatcher means harder debugging, since it's only known at runtime what listeners are actually attached. It makes it hard
to know what callback will be triggered by following regular flow of the program, because attaching is mostly done totally 
out of context of dispatch call. That said:
* Many applications already heavily use dispatching inside their application domain and suffer from limitations
of regular dispatchers. Removing it is harder than replacing dispatcher they use with this one
* Heavy usage of event listeners allows you to do IoC. You don't need to modify code which does dispatching, you just
 attach new listener. It's especially useful for FSM.

**Q**: Why don't you provide implementation for LaxEventDispatcherInterface for usage as a library?

**A**: I didn't do this because regular event dispatchers are better suited for this. Their restrictive public interface 
and additional features are actually plus here, as it allows to keep better backwards compatibility for libraries and
more control over process when multiple different 3rd party libraries listen to same event. That's why is this dispatcher 
focused on usage in application domain only.
