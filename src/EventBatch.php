<?php

namespace GabrielDosAnjosBr\DomainEvents;

use Symfony\Contracts\EventDispatcher\Event;

class EventBatch extends Event implements \IteratorAggregate, \Countable
{
    public function __construct(
        protected readonly string $eventClass,
        protected readonly array $events
    ) {}

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->events);
    }

    public function count(): int
    {
        return count($this->events);
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function first(): object|null
    {
        return $this->events[0] ?? null;
    }
}
