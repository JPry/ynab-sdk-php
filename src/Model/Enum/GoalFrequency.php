<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Enum;

enum GoalFrequency: string
{
	case Monthly = 'monthly';
	case Weekly = 'weekly';
	case Yearly = 'yearly';
}
