<?php

namespace GabrielDosAnjosBr\DomainEvents\Doctrine;

use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use GabrielDosAnjosBr\DomainEvents\DomainEventCollector;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;

class EntityManager extends EntityManagerDecorator
{
    public function __construct(
        #[AutowireDecorated] EntityManagerInterface $wrapped,
        private readonly DomainEventCollector $collector
    ) {
        parent::__construct($wrapped);
    }

    /**
     * @throws \ReflectionException
     */
    public function flush($entity = null): void
    {
        $this->collector->collectFromManager($this->wrapped);

        $this->wrapped->flush($entity);

        while (!$this->collector->isEventsEmpty()) {
            $this->collector->dispatchCollectedEvents();

            $this->wrapped->flush($entity);

            $this->collector->collectFromManager($this->wrapped);
        }
    }
}