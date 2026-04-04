# Domain Events Bundle

Simple domain events for Symfony using Doctrine (ORM, ODM, or both).

Aggregates record events during execution, and everything is dispatched automatically after `flush()`, regardless of when they are raised.


No Doctrine lifecycle hooks. No configuration.

The responsibility of creating events belongs to the model (entity/document), following DDD principles.

Events can be raised anywhere inside the aggregate and are only dispatched after persistence.

---

## Installation

```bash
composer require gabrieldosanjosbr/domain-events-bundle
```

---

## Usage

### 1. Aggregate (Entity or Document)

Your entity/document must:

* implement `AggregateRootInterface`
* use `RaiseEventsTrait`

```php
use GabrielDosAnjosBr\DomainEvents\AggregateRootInterface;
use GabrielDosAnjosBr\DomainEvents\RaiseEventsTrait;

class User implements AggregateRootInterface
{
    use RaiseEventsTrait;

    public function __construct()
    {
        $this->raise(new UserCreated($this->id));
    }
}
```

---

### 2. Event

Events are just regular Symfony events:

```php
use Symfony\Contracts\EventDispatcher\Event;

class UserCreated extends Event
{
    public function __construct(
        public readonly string $userId,
        public readonly \DateTimeImmutable $occurredAt = new \DateTimeImmutable('now'),
    ) {}
}
```

---

### 3. Listener

```php
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserCreated::class)]
class UserCreatedListener
{
    public function __invoke(UserCreated $event): void
    {
        // your logic here
    }
}
```

---

### 4. Dispatching

When you call:

```php
$entityManager->flush();
```
or
```php
$documentManager->flush();
```

All recorded events are dispatched automatically.

---

## Batch events

If your event implements `BatchableEvent`, multiple events of the same type are grouped and delivered as a single batch.

### Event

```php
use GabrielDosAnjosBr\DomainEvents\BatchableEvent;

class UserCreated extends Event implements BatchableEvent
{
    public function __construct(
        public readonly string $userId,
        public readonly \DateTimeImmutable $occurredAt = new \DateTimeImmutable('now'),
    ) {}
}
```

---

### Listener (batched)

```php
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use GabrielDosAnjosBr\DomainEvents\EventBatch;

#[AsEventListener(event: UserCreated::class)]
class UserCreatedListener
{
    public function __invoke(EventBatch $events): void
    {
        foreach ($events as $event) {
            // handle each event
        }
    }
}
```

---

## How it works

* Aggregates collect events internally
* Doctrine managers are decorated automatically
* After `flush()`, all events are dispatched

Works with:

* Doctrine ORM (`EntityManager`)
* Doctrine MongoDB ODM (`DocumentManager`)
* or both

---

## Notes

* No configuration required
* Events are dispatched only after `flush()`
* Keeps domain logic inside aggregates, not in Doctrine hooks

---

## License

MIT
