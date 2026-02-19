<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

/** @template T */
final readonly class ResourceCollection
{
    /** @param array<int,T> $items */
    public function __construct(
        public array $items,
        public ?int $serverKnowledge = null,
    ) {
    }
}
