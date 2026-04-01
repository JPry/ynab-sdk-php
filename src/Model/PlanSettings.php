<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;

final readonly class PlanSettings
{
	public function __construct(
		public ?string $dateFormat,
		public ?string $currencyIsoCode,
		public ?string $currencySymbol,
		public ?int $currencyDecimalDigits,
		public ?string $currencyDecimalSeparator,
		public ?string $currencyGroupSeparator,
		public ?bool $currencySymbolFirst,
		public ?bool $currencyDisplaySymbol,
		public ?string $currencyExampleFormat,
	) {
	}

	/** @param array<string,mixed> $row */
	public static function fromArray(array $row): self
	{
		$dateFormat = ArrayReader::nullableArray($row, 'date_format');
		$currencyFormat = ArrayReader::nullableArray($row, 'currency_format');

		return new self(
			dateFormat: isset($dateFormat['format'])
				? (string) $dateFormat['format']
				: null,
			currencyIsoCode: isset($currencyFormat['iso_code'])
				? (string) $currencyFormat['iso_code']
				: null,
			currencySymbol: isset($currencyFormat['currency_symbol'])
				? (string) $currencyFormat['currency_symbol']
				: null,
			currencyDecimalDigits: array_key_exists('decimal_digits', $currencyFormat ?? [])
				? (int) $currencyFormat['decimal_digits']
				: null,
			currencyDecimalSeparator: isset($currencyFormat['decimal_separator'])
				? (string) $currencyFormat['decimal_separator']
				: null,
			currencyGroupSeparator: isset($currencyFormat['group_separator'])
				? (string) $currencyFormat['group_separator']
				: null,
			currencySymbolFirst: array_key_exists('symbol_first', $currencyFormat ?? [])
				? (bool) $currencyFormat['symbol_first']
				: null,
			currencyDisplaySymbol: array_key_exists('display_symbol', $currencyFormat ?? [])
				? (bool) $currencyFormat['display_symbol']
				: null,
			currencyExampleFormat: isset($currencyFormat['example_format'])
				? (string) $currencyFormat['example_format']
				: null,
		);
	}
}
