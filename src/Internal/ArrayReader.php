<?php

declare(strict_types=1);

namespace JPry\YNAB\Internal;

/**
 * Utility for safely extracting typed values from untyped API response arrays.
 */
final class ArrayReader
{
	/**
	 * Returns the trimmed string value for the given key, or null if the key is
	 * missing, null, or empty after trimming. Suitable for required ID fields.
	 */
	public static function requiredString(array $row, string $key): ?string
	{
		$value = trim((string) ($row[$key] ?? ''));

		return $value !== '' ? $value : null;
	}

	/**
	 * Returns the string value for the given key, or null if absent or null.
	 * Preserves empty strings (use requiredString() if empty should map to null).
	 */
	public static function nullableString(array $row, string $key): ?string
	{
		return isset($row[$key]) ? (string) $row[$key] : null;
	}

	/**
	 * Like nullableString() but also maps empty strings to null.
	 */
	public static function nullableNonEmptyString(array $row, string $key): ?string
	{
		if (!isset($row[$key])) {
			return null;
		}

		$value = trim((string) $row[$key]);

		return $value !== '' ? $value : null;
	}

	/**
	 * Returns the int value for the given key, or null if absent or null.
	 */
	public static function nullableInt(array $row, string $key): ?int
	{
		return array_key_exists($key, $row) && $row[$key] !== null
			? (int) $row[$key]
			: null;
	}

	/**
	 * Returns the bool value for the given key, or null if absent or null.
	 */
	public static function nullableBool(array $row, string $key): ?bool
	{
		return array_key_exists($key, $row) && $row[$key] !== null
			? (bool) $row[$key]
			: null;
	}

	/**
	 * Returns the float value for the given key, or null if absent or null.
	 */
	public static function nullableFloat(array $row, string $key): ?float
	{
		return array_key_exists($key, $row) && $row[$key] !== null
			? (float) $row[$key]
			: null;
	}

	/**
	 * Returns the array value for the given key, or null if absent, null, or not an array.
	 *
	 * @return array<mixed>|null
	 */
	public static function nullableArray(array $row, string $key): ?array
	{
		return isset($row[$key]) && is_array($row[$key]) ? $row[$key] : null;
	}

	/**
	 * Returns the int value for the given key, defaulting to 0 if absent or null.
	 */
	public static function int(array $row, string $key, int $default = 0): int
	{
		return (int) ($row[$key] ?? $default);
	}

	/**
	 * Returns the bool value for the given key, defaulting to false if absent or null.
	 */
	public static function bool(array $row, string $key, bool $default = false): bool
	{
		return (bool) ($row[$key] ?? $default);
	}
}
