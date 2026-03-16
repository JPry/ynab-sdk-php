<?php

declare(strict_types=1);

namespace JPry\YNAB\Internal;

trait HasId
{
	public function getId(): string
	{
		return $this->id;
	}
}
