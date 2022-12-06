<?php

namespace FabianMichael\TemplateAttributes;

use Kirby\Toolkit\A;
use Kirby\Toolkit\Html;
use Kirby\Toolkit\Str;
use Stringable;

/**
 * The `Attribute` class represents a set of HTML attributes intended
 * for rendering them as a string.
 */
class Attributes implements Stringable
{
	protected array $data = [];
	protected ?string $before = null;
	protected ?string $after = null;

	public function __construct(...$data)
	{
		if (count($data) === 1 && array_key_first($data) === 0) {
			// Array input
			$data = $data[0];
		} elseif (A::isAssociative($data)) {
			// Named arguments, convert camelCase to kebab-case
			$data = array_combine(
				array_map(fn ($key) => Str::kebab($key), array_keys($data)),
				array_values($data)
			);
		}

		if (is_a($data, self::class)) {
			$data = $data->data;
		}

		foreach ($data as $name => $value) {
			$this->set($name, $value);
		}
	}

	/**
	 * Getter/Setter for the suffix appended when casting to string
	 */
	public function after(string|null $after = null): static|string|null
	{
		if (func_num_args() === 0) {
			return $this->after;
		}

		$this->after = $after;

		return $this;
	}

	public function append(mixed $value, ?string $separator = null): AttributeValue
	{
		return new AttributeValue($value, MergeStrategy::APPEND, $separator);
	}

	/**
	 * Getter/Setter for the prefix prepended when casting to string
	 */
	public function before(string|null $before = null): static|string|null
	{
		if (func_num_args() === 0) {
			return $this->before;
		}

		$this->before = $before;

		return $this;
	}

	protected static function createClassValue(string $value): AttributeValue
	{
		return new AttributeValue($value, MergeStrategy::APPEND, ' ');
	}

	protected static function createStyleValue(string $value): AttributeValue
	{
		return new AttributeValue($value, MergeStrategy::APPEND, '; ');
	}

	public function get(?string $name = null): AttributeValue|array|null
	{
		if (is_null($name)) {
			return $this->data;
		}

		return $this->data[$name] ?? null;
	}

	public function merge(array|self $data = []): static
	{
		$data = is_a($data, static::class) ? $data->data : $data;

		foreach ($data as $name => $value) {
			$this->set($name, $value);
		}

		return $this;
	}

	public function prepend(mixed $value, ?string $separator = null): AttributeValue
	{
		return new AttributeValue($value, MergeStrategy::PREPEND, $separator);
	}

	public function protect(mixed $value): AttributeValue
	{
		return new AttributeValue($value, MergeStrategy::PROTECT);
	}

	/**
	 * normalizes an array of class names to a string, e.g.
	 * array(['button', 'is-disabled' => false]) => "button"
	 * array(['button', 'is-disabled' => true]) => "button is-disabled"
	 */
	public static function normalizeClassValue(array|string $classes): string
	{
		$value = [];

		foreach (A::wrap($classes) as $key => $class) {
			if (is_numeric($key)) {
				$value[] = $class;
			} elseif ($class) {
				$value[] = $key;
			}
		}

		return implode(' ', array_unique($value));
	}

	public static function normalizeStyleValue(array|string $styles): string
	{
		return is_array($styles) ? implode('; ', $styles): $styles;
	}

	public function set(string $name, mixed $value): static
	{
		if (method_exists(static::class, $method = 'normalize' . ucfirst($name) . 'Value')) {
			$value = static::$method($value);
		}

		if (array_key_exists($name, $this->data)) {
			// Merge with existing attribute
			$this->data[$name] = $this->data[$name]->merge($value);
		} elseif (is_a($value, Attribute::class)) {
			$this->data[$name] = $value;
		} else {
			// Set new attribute
			$method = 'create' . ucfirst($name) . 'Value';
			$this->data[$name] = method_exists(static::class, $method)
				? static::$method($value)
				: new AttributeValue($value);
		}

		return $this;
	}

	public function __call(string $name, array $arguments): self
	{
		if (func_num_args() === 1) {
			return $this->get($name);
		}

		return $this->set($name, ...$arguments);
	}

	public function __toString()
	{
		return (string)Html::attr(
			array_map(fn ($item) => $item->value(), $this->data),
			before: $this->before,
			after: $this->after
		);
	}

}
