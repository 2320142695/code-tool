<?php
declare(strict_types=1);

namespace Cxx\Codetool\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcher implements EventDispatcherInterface
{

    /**
     * listener
     *
     * @var ListenerProviderInterface
     */
    protected $listener = null;

    public function __construct(ListenerProviderInterface $listener)
    {
        $this->listener = $listener;
    }

    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param object $event
     *   The object to process.
     *
     * @return object
     *   The Event that was passed, now modified by listeners.
     */
    public function dispatch(object $event): object
    {
        foreach ($this->listener->getListenersForEvent($event) as $listener) {
            $listener($event);
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }
}
