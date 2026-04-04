<?php

namespace GabrielDosAnjosBr\DomainEvents;

interface AggregateRootInterface
{
	public function popEvents(): array;
}
