<?php

namespace FabianMichael\TemplateAttributes;

use Exception;
use Stringable;
use Kirby\Toolkit\Str;

/**
 * The `Attribute` class represents the value of an HTML attributes
 * and provides various utilities for merging and joinging attribute
 * values.
 */
class Attribute implements Stringable
{
	public const MERGE_REPLACE = 0;
	public const MERGE_APPEND = 1;
	public const MERGE_PREPEND = 2;

	public const DEFAULT_SEPARATOR = ' ';

	public readonly mixed $value;
	public readonly ?int $mergeStrategy;
	public readonly ?string $separator;

	public function __construct(
		mixed $value,
		?int $mergeStrategy = null,
		?string $separator = null
	) {
		if (is_a($value, static::class)) {
			// If an instance of this class is passed as value,
			// extract all informartion and transfer to this instance.
			$this->value = $value->value;
			$this->mergeStrategy = $mergeStrategy ?? $value->mergeStrategy;
			$this->separator = $separator ?? $value->separator;
		} else {
			$this->value = $value;
			$this->mergeStrategy = $mergeStrategy;
			$this->separator = $separator;
		}
	}

	/**
	 * Merge this attribute with another one depending on merge strategy
	 */
	public function merge(mixed $attribute): static
	{
		if (in_array($this->mergeStrategy, [static::MERGE_APPEND, static::MERGE_PREPEND])) {
			// append or prepend strategy

			if (is_a($attribute, self::class)) {
				if (! is_null($attribute->mergeStrategy) && $attribute->mergeStrategy !== $this->mergeStrategy) {
					throw new Exception('Could not merge attribute values, because merge strategies do not match');
				}

				if (! is_null($attribute->separator) && $attribute->separator !== $this->separator) {
					throw new Exception('Could not merge attribute values, because separators do not match');
				}
			}

			// first, remove excess separators between chunks
			$first = ($this->mergeStrategy === static::MERGE_APPEND ? $this->value : $attribute);
			$first = Str::beforeEnd((string) $first, $this->separator);
			$last = ($this->mergeStrategy === static::MERGE_PREPEND ? $this->value : $attribute);
			$last = Str::afterStart((string) $last, $this->separator);

			// synthesize new value
			$value = array_filter([$first, $last], fn($item) => ! empty($item));
			$value = implode($this->separator, $value);

			// return new instance
			return new static($value, $this->mergeStrategy, $this->separator);
		}

		// fallback to replace strategy, i.e. just replace this with the new value
		return new static($attribute);
	}

	public function value(): mixed
	{
		return $this->value;
	}

	public function __toString()
	{
		return (string) $this->value;
	}
}
