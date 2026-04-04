<?php

declare(strict_types=1);

namespace GabrielDosAnjosBr\DomainEvents;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use GabrielDosAnjosBr\DomainEvents\DependencyInjection\DomainEventExtension;

class DomainEventBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DomainEventExtension();
    }
}