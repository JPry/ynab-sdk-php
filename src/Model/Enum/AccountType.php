<?php

declare(strict_types=1);

namespace JPry\YNAB\Model\Enum;

enum AccountType: string
{
	case Checking = 'checking';
	case Savings = 'savings';
	case Cash = 'cash';
	case CreditCard = 'creditCard';
	case LineOfCredit = 'lineOfCredit';
	case OtherAsset = 'otherAsset';
	case OtherLiability = 'otherLiability';
	case Mortgage = 'mortgage';
	case AutoLoan = 'autoLoan';
	case StudentLoan = 'studentLoan';
	case PersonalLoan = 'personalLoan';
	case MedicalDebt = 'medicalDebt';
	case OtherDebt = 'otherDebt';
}
