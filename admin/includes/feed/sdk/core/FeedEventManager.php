<?php

class FeedEventManager
{
    /**
     * Map of registered listeners.
     * <event> => <listeners>
     *
     * @var array
     */
    private $_listeners = array();

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string $eventName The name of the event to dispatch. The name of the event is
     *                          the name of the method that is invoked on listeners.
     * @param EventArgs $eventArgs The event arguments to pass to the event handlers/listeners.
     *                             If not supplied, the single empty EventArgs instance is used.
     * @return boolean
     */
    public function dispatchEvent($eventName, FeedEvent $event = null)
    {
        if (isset($this->_listeners[$eventName])) {
            foreach ($this->_listeners[$eventName] as $listener) {
                $listener->$eventName($event);
            }
        }
    }

    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string $eventName The name of the event.
     * @return array The event listeners for the specified event, or all event listeners.
     */
    public function getListeners($eventName = null)
    {
        return ($eventName && isset($this->_listeners[$eventName])) ? $this->_listeners[$eventName] : $this->_listeners;
    }

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $eventName
     * @return boolean TRUE if the specified event has any listeners, FALSE otherwise.
     */
    public function hasListeners($eventName)
    {
        return isset($this->_listeners[$eventName]) && $this->_listeners[$eventName];
    }

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string|array $events The event(s) to listen on.
     * @param object $listener The listener object.
     */
    public function addEventListener($eventNames, $listener)
    {
        // Picks the hash code related to that listener
        $hash = spl_object_hash($listener);

        foreach ((array) $eventNames as $event) {
            // Overrides listener if a previous one was associated already
            // Prevents duplicate listeners on same event (same instance only)
            $this->_listeners[$event][$hash] = $listener;
        }
    }

    /**
     * Removes an event listener from the specified events.
     *
     * @param string|array $events
     * @param object $listener
     */
    public function removeEventListener($eventNames, $listener)
    {
        // Picks the hash code related to that listener
        $hash = spl_object_hash($listener);

        foreach ((array) $eventNames as $event) {
            // Check if actually have this listener associated
            if (isset($this->_listeners[$event][$hash])) {
                unset($this->_listeners[$event][$hash]);
            }
        }
    }
}
