<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Enum;

enum TransactionClearedStatus: string
{
	case Cleared = 'cleared';
	case Uncleared = 'uncleared';
	case Reconciled = 'reconciled';
}
