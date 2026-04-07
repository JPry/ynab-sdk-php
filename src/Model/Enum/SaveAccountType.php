<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Enum;

enum SaveAccountType: string
{
	case Checking = 'checking';
	case Savings = 'savings';
	case Cash = 'cash';
	case CreditCard = 'creditCard';
	case OtherAsset = 'otherAsset';
	case OtherLiability = 'otherLiability';
}
