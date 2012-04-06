<?php
/**
 * @author  Peter Hinchley
 * @license http://sam.zoy.org/wtfpl
 */

/**
 * The 'Event' class allows you to register handlers for named
 * events. It also allows you to invoke ('fire') the events on
 * demand. This provides you with a mechanism for sending signals
 * or sharing data between loosely coupled blocks of code. It also
 * allows you to configure 'hooks' within a code base so you can
 * easily extend or override the behaviour of an application.
 */
class Event {
  /**
   * The registered events.
   *
   * @var array
   */
  protected static $events = array();

  /**
   * The events that have been fired.
   *
   * @var array
   */
  protected static $fired = array();

  /**
   * Register a handler (a callback function) for an event.
   *
   * Each execution of the method will add an additional callback.
   * The callbacks are executed in the order they are defined.
   *
   * To only execute a callback once, irrespective of the number
   * of times the event is fired, call the method with a third
   * argument of TRUE.
   *
   * <code>
   *   Event::bind('router.match', function() {
   *     // Do stuff when the 'router.match' event is fired.
   *   });
   * </code>
   *
   * @param  string   $name The name of the event.
   * @param  function $callback The callback function.
   * @param  bool     $once Only fire the callback once.
   * @return void
   */
  public static function bind($name, $callback, $once = FALSE) {
    static::append($name, $callback, $once);
  }

  /**
   * Replace the handlers for an event with a new handler.
   *
   * @param  string   $name The name of the event.
   * @param  function $callback The callback function.
   * @param  bool     $once Only fire the callback once.
   * @return void
   */
  public static function rebind($name, $callback, $once = FALSE) {
    static::unbind($name);
    static::append($name, $callback, $once);
  }

  /**
   * A synonym for the bind method.
   *
   * The event handler is added to the end of the queue.
   *
   * @param  string   $name The name of the event.
   * @param  function $callback The callback function.
   * @param  bool     $once Only fire the callback once.
   * @return void
   */
  public static function append($name, $callback, $once = FALSE) {
    static::$events[$name][] = array(
      $once ? 'once' : 'always' => $callback
    );
  }

  /**
   * Identical to the append method, except the event handler is
   * added to the start of the queue.
   *
   * @param  string   $name The name of the event.
   * @param  function $callback The callback function.
   * @param  bool     $once Only fire the callback once.
   * @return void
   */
  public static function insert($name, $callback, $once = FALSE) {
    if (static::bound($name)) {
      array_unshift(
        static::$events[$name],
        array($once ? 'once' : 'always' => $callback)
      );
    } else {
      static::append($name, $callback, $once);
    }
  }

  /**
   * Trigger all callback functions for an event.
   *
   * The method returns an array containing the responses from all
   * of the event handlers (even empty responses).
   *
   * Returns NULL if the event has no handlers.
   *
   * <code>
   *   // Fire the 'start' event.
   *   $responses = Event::fire('start');
   *
   *   // Fire the 'start' event passing two arguments to the
   *   // callback function.
   *   $responses = Event::fire('start', array('foo', 'bar'));
   * </code>
   *
   * @param  string $name The name of the event.
   * @param  array  $data The data passed to the event handlers.
   * @param  array  $stop Return after the first non-empty response.
   * @return mixed
   */
  public static function fire($name, $data = array(), $stop = FALSE) {
    if (static::bound($name)) {
      static::$fired[$name] = TRUE;

      foreach (static::$events[$name] as $key => $value) {
        list($type, $callback) = each($value);

        $responses[] = $response =
          call_user_func_array($callback, (array) $data);

        if ($type == 'once') unset(static::$events[$name][$key]);
        if ($stop && !empty($response)) return $responses;
      }
    }

    return isset($responses) ? $responses : NULL;
  }

  /**
   * Trigger all callback functions for an event and return only the
   * first response (even if it is empty).
   *
   * The return value is not wrapped in an array.
   *
   * @param  string $name The name of the event.
   * @param  string $data The data passed to the event handlers.
   * @return mixed
   */
  public static function first($name, $data = array()) {
    $result = static::fire($name, $data);
    return reset($result);
  }

  /**
   * Trigger all callback functions for an event and return the
   * first non-empty response.
   *
   * The return value is not wrapped in an array.
   *
   * @param  string $name The name of the event.
   * @param  string $data The data passed to the event handlers.
   * @return mixed
   */
  public static function until($name, $data = array()) {
    $result = static::fire($name, $data, TRUE);
    return end($result);
  }

  /**
   * Check if an event has fired.
   *
   * @param  string $name The name of the event.
   * @return bool
   */
  public static function fired($name) {
    return isset(static::$fired[$name]);
  }

  /**
   * Deregister the handlers for an event.
   *
   * To remove the event handlers for a specific event, pass the
   * name of the event to the method. To remove all event handlers,
   * call the method without any arguments. 
   *
   * @param  string $name The name of the event.
   * @return void
   */
  public static function unbind($name = NULL) {
    static::clear(static::$events, $name);
  }

  /**
   * Reset the flag indicating an event has fired.
   *
   * If called without an argument, the 'fired' flag for all events
   * will be cleared.
   *
   * @param  string $name The name of the event.
   * @return void
   */
  public static function reset($name = NULL) {
    static::clear(static::$fired, $name);
  }

  /**
   * Check if any callback functions are bound to an event.
   *
   * @param  string $name The name of the event.
   * @return bool
   */
  public static function bound($name) {
    return isset(static::$events[$name]);
  }

  /**
   * Remove an element from an array, or clear an entire array.
   *
   * @param  array  $array The array.
   * @param  string $name The name of the element to clear.
   * @return void
   */
  protected static function clear(&$array, $name) {
    if ($name == NULL) $array = array();
    else unset($array[$name]);
  }
}