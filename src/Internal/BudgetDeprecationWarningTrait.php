<?php

declare(strict_types=1);

namespace JPry\YNAB\Internal;

trait BudgetDeprecationWarningTrait
{
	private function warnBudgetDeprecation(string $oldUsage, string $newUsage): void
	{
		/** @var array<string,bool> $warned */
		static $warned = [];
		if (isset($warned[$oldUsage])) {
			return;
		}

		$warned[$oldUsage] = true;
		trigger_error(
			"{$oldUsage} is deprecated and will be removed in a future release. Use {$newUsage}.",
			E_USER_DEPRECATED,
		);
	}
}
