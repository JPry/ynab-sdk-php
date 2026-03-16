<?php

declare(strict_types=1);

namespace JPry\YNAB\Exception;

use InvalidArgumentException;

class InvalidStringException extends InvalidArgumentException
{
	public static function forEmptyString(string $name): self
	{
		return new self("The string '{$name}' cannot be empty.");
	}
}
