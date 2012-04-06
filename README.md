Event
=====
The 'Event' class allows you to register handlers for named events. It also allows you to invoke ('fire') the events on demand. This provides you with a mechanism for sending signals or sharing data between loosely coupled blocks of code. It also allows you to configure 'hooks' within a code base so you can easily extend or override the behaviour of an application.

Usage
-----
### Bind and Fire
Use the ``bind`` method to register a handler (a callback function) for an event.

Each execution of the method will add an additional callback. The callbacks are executed in the order they are defined.

    Event::bind('router.match', function() {
      return "The event 'router.match' was called.";
    });

    Event::bind('router.match', function() {
      // Log data...
      return "Routing data was logged.";
    });

Use the ``fire`` method to trigger all callback functions for an event. The method returns an array containing the responses from all of the event handlers (even empty responses).

    // Trigger the 'router.match' event:
    $responses = Event::fire('router.match');

    // Result:
    $responses = array(
      "The event 'router.match' was called.",
      "Routing data was logged."
    );

To only execute a callback once, irrespective of the number of times the event is fired, when registering an event handler, call the ``bind`` method with a third argument of TRUE:

    // Only execute this handler once.
    Event::bind('router.match', function() {
      return "The event 'router.match' was called.";
    }, TRUE);

    // Execute this handler every time.
    Event::bind('router.match', function() {
      // Log data...
      return "Routing data was logged.";
    });

Now if the event is fired twice, the first handler will only be called once:

    // Trigger the 'router.match' event twice:
    $one = Event::fire('router.match');
    $two = Event::fire('router.match');

    // Results:
    $one = array(
      "The event 'router.match' was called.",
      "Routing data was logged."
    );
    
    $two = array(
      "Routing data was logged."
    );

You can easily pass data to the registered callback functions when firing an event:

    // Register a handler that expects two parameters:
    Event::bind('log', function($log, $data) {
      // Write $data to a $log.
    });

    // Pass data to the event handler:
    Event::fire('log', array('log.txt', 'Something happened.'));

### Append and Insert
The ``append`` method is an alias for the ``bind`` method; both add event handlers to the end of the list of existing handlers.

The ``insert`` method takes the same parameters as ``bind``, but adds the event handler to the start of the queue, ensuring it is executed before any of the other callback functions.

### First and Until
The ``first`` method triggers all callback functions for an event and returns only the first response (even if it is empty). As the method returns only a single value, the result is not wrapped in an array.

    // Returns: "The event 'router.match' was called."
    $response = Event::first('router.match');

The ``until`` method is very similar to the ``first`` method, but it returns the first non-empty response.

### Bound, Fired and Reset
Use the ``bound`` method to check if any handlers are registered for an event:

    // Returns: TRUE.
    $bound = Event::bound('router.match');

Use the ``fired`` method to check if an event has fired. 

    // Fire the event:
    Event::fire('router.match');

    // Returns: TRUE.
    $fired = Event::fired('router.match');

Use the ``reset`` method to clear flag indicating an event has fired. If called without an argument, all event counters are reset.

    // Clear the 'fire' count for the event:
    Event::reset('router.match');

    // Returns: FALSE.
    $times = Event::fired('router.match');

### Rebind and Unbind
The ``rebind`` method takes the same parameters as ``bind``, but it will overwrite all existing handlers defined for an event with a new callback function.

The ``unbind`` method deregisters the handlers for an event:

    // Remove all callback functions bound to the event:
    Event::unbind('router.match');

    // Returns NULL as all handlers have been removed:
    Event::fire('router.match');
