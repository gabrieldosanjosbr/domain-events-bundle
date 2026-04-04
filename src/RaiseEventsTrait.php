<?php

namespace GabrielDosAnjosBr\DomainEvents;

trait RaiseEventsTrait
{
	protected array $events = [];

	protected function raise(object $event): void
	{
        $this->events[] = $event;
	}

    public function popEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
