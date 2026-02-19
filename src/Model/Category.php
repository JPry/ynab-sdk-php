<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class Category
{
    public function __construct(
        public string $id,
        public string $name,
        public string $groupId,
        public string $groupName,
        public int $groupOrder,
        public int $categoryOrder,
        public bool $hidden,
    ) {
    }
}
