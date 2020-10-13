<?php
declare(strict_types=1);

namespace Cxx\Codetool\Event;

use Psr\EventDispatcher\ListenerProviderInterface;

class Listener implements ListenerProviderInterface
{

    /**
     * events
     *
     * @var array
     */
    protected $events = [];

    /**
     * @param object $event
     *   An event for which to return the relevant listeners.
     * @return iterable[callable]
     *   An iterable (array, iterator, or generator) of callables.  Each
     *   callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent(object $event): iterable
    {
        $queue = new \SplPriorityQueue();
        $event_name = get_class($event);
        foreach ($this->events[$event_name] ?? [] as [$listener, $priority]) {
            $queue->insert($listener, $priority);
        }
        return $queue;
    }

    /**
     * 添加事件
     *
     * @param string $event_name
     * @param callable $listener
     * @param integer $priority
     * @return void
     */
    public function add(string $event_name, callable $listener, int $priority = 1): void
    {
        $this->events[$event_name][] = [$listener, $priority];
    }
}
