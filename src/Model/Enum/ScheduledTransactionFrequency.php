<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Enum;

enum ScheduledTransactionFrequency: string
{
	case Never = 'never';
	case Daily = 'daily';
	case Weekly = 'weekly';
	case EveryOtherWeek = 'everyOtherWeek';
	case TwiceAMonth = 'twiceAMonth';
	case Every4Weeks = 'every4Weeks';
	case Monthly = 'monthly';
	case EveryOtherMonth = 'everyOtherMonth';
	case Every3Months = 'every3Months';
	case Every4Months = 'every4Months';
	case TwiceAYear = 'twiceAYear';
	case Yearly = 'yearly';
	case EveryOtherYear = 'everyOtherYear';
}
