<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Enum;

enum TransactionFlagColor: string
{
	case Red = 'red';
	case Orange = 'orange';
	case Yellow = 'yellow';
	case Green = 'green';
	case Blue = 'blue';
	case Purple = 'purple';
	/** Explicitly clears/removes a flag. */
	case None = '';
}
