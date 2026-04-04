<?php

namespace GabrielDosAnjosBr\DomainEvents\Doctrine;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use GabrielDosAnjosBr\DomainEvents\DomainEventCollector;

class DocumentManager extends ObjectManagerDecorator
{
    public function __construct(
        #[AutowireDecorated] ObjectManager $wrapped,
        private readonly DomainEventCollector $collector
    ) {
        $this->wrapped = $wrapped;
    }

    /**
     * @throws \ReflectionException
     */
    public function flush(array $options = []): void
    {
        $this->wrapped->flush($options);

        $this->collector->collectFromManager($this->wrapped);

        while (!$this->collector->isEventsEmpty()) {
            $this->collector->dispatchCollectedEvents();

            $this->wrapped->flush($options);

            $this->collector->collectFromManager($this->wrapped);
        }
    }

    public function __call(string $method, array $args)
    {
        return $this->wrapped->$method(...$args);
    }
}