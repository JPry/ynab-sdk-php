<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Mutation;

interface RequestModel
{
	/** @return array<string,mixed> */
	public function toArray(): array;
}
