<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

/** @template T */
final readonly class ResourceCollection implements \IteratorAggregate, \Countable
{
	/** @param array<int,T> $items */
	public function __construct(
		public array $items,
		public ?int $serverKnowledge = null,
	) {
	}

	/** @return \ArrayIterator<int,T> */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->items);
	}

	public function count(): int
	{
		return count($this->items);
	}
}
