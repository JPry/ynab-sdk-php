<?php

declare(strict_types=1);

namespace JPry\YNAB\Model;

use JPry\YNAB\Internal\ArrayReader;

final readonly class PayeeLocation implements Model
{
	public function __construct(
		public string $id,
		public string $payeeId,
		public string $latitude,
		public string $longitude,
		public bool $deleted,
	) {
	}

	public function getId(): string
	{
		return $this->id;
	}

	/** @param array<string,mixed> $row */
	public static function fromArray(array $row): ?self
	{
		$id = ArrayReader::requiredString($row, 'id');
		if ($id === null) {
			return null;
		}

		return new self(
			id: $id,
			payeeId: (string) ($row['payee_id'] ?? ''),
			latitude: (string) ($row['latitude'] ?? ''),
			longitude: (string) ($row['longitude'] ?? ''),
			deleted: ArrayReader::bool($row, 'deleted'),
		);
	}
}
