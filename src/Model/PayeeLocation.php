<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

final readonly class PayeeLocation
{
	public function __construct(
		public string $id,
		public string $payeeId,
		public string $latitude,
		public string $longitude,
		public bool $deleted,
	) {
	}

	/** @param array<string,mixed> $row */
	public static function fromArray(array $row): ?self
	{
		$id = trim((string) ($row['id'] ?? ''));
		if ($id === '') {
			return null;
		}

		return new self(
			id: $id,
			payeeId: (string) ($row['payee_id'] ?? ''),
			latitude: (string) ($row['latitude'] ?? ''),
			longitude: (string) ($row['longitude'] ?? ''),
			deleted: (bool) ($row['deleted'] ?? false),
		);
	}
}
