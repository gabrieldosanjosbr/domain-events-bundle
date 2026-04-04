<?php

namespace GabrielDosAnjosBr\DomainEvents;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DomainEventCollector
{
    private array $events = [];

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * @throws \ReflectionException
     */
    public function collectFromManager(ObjectManager $manager): void
    {
        if (!method_exists($manager, 'getUnitOfWork')) {
            return;
        }

        $objects = [];
        $uow = $manager->getUnitOfWork();
        $uow->computeChangeSets();

        $reflection = new \ReflectionObject($manager);

        if (
            interface_exists('\Doctrine\ORM\EntityManagerInterface') &&
            $reflection->implementsInterface('\Doctrine\ORM\EntityManagerInterface')
        ) {
            $objects = array_merge(
                $objects,
                $uow->getScheduledEntityInsertions(),
                $uow->getScheduledEntityUpdates(),
                $uow->getScheduledEntityDeletions()
            );
        }

        if (get_class($manager) === 'Doctrine\ODM\MongoDB\DocumentManager') {
            $objects = array_merge(
                $objects,
                $uow->getScheduledDocumentInsertions(),
                $uow->getScheduledDocumentUpdates(),
                $uow->getScheduledDocumentDeletions()
            );
        }

        $unique = [];

        foreach ($objects as $object) {
            $unique[spl_object_id($object)] = $object;
        }

        $objects = array_values($unique);

        foreach ($objects as $object) {
            if (!$object instanceof AggregateRootInterface) {
                continue;
            }

            foreach ($object->popEvents() as $event) {
                $this->events[] = $event;
            }
        }
    }

    /**
     * Returns all collected events and then clear those.
     */
    public function dispatchCollectedEvents(): void
    {
        $batch = [];
        $collectedEvents = $this->events;
        $this->events = [];

        foreach ($collectedEvents as $event) {
            if ($event instanceof BatchableEvent) {
                $batch[$event::class][] = $event;
                continue;
            }

            $this->eventDispatcher->dispatch($event, $event::class);
        }

        foreach ($batch as $eventClass => $groupedEvents) {
            $this->eventDispatcher->dispatch(
                new EventBatch($eventClass, $groupedEvents),
                $eventClass
            );
        }
    }

    public function isEventsEmpty(): bool
    {
        return empty($this->events);
    }
}
