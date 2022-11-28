<?php

namespace FabianMichael\TemplateAttributes;

use Kirby\Toolkit\A;
use Kirby\Toolkit\Html;
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

	public function __construct(self|array $data = [])
	{
		if (is_a($data, self::class)) {
			$data = $data->data;
		}

		foreach ($data as $name => $value) {
			$this->set($name, $value);
		}
	}

	public static function createClass(mixed $value): Attribute
	{
		return new Attribute($value, Attribute::MERGE_APPEND, ' ');
	}

	public static function createStyle(mixed $value): Attribute
	{
		return new Attribute($value, Attribute::MERGE_APPEND, '; ');
	}

	public function get(string $name): ?Attribute
	{
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

	public function set(string $name, mixed $value): static
	{

		$method = 'resolve' . ucfirst($name);
		if (method_exists(static::class, $method)) {
			// If a resolve method for this attribute exists, execute
			// it first.
			$value = static::$method($value);
		}

		if (array_key_exists($name, $this->data)) {
			// Merge with existing attribute
			$this->data[$name] = $this->data[$name]->merge($value);
		} else if (is_a($value, Attribute::class)) {
			$this->data[$name] = $value;
		} else {
			// Set new attribute
			$method = 'create' . ucfirst($name);
			$this->data[$name] = method_exists(static::class, $method)
				? static::$method($value)
				: new Attribute($value);
		}

		return $this;
	}

	/**
	 * Resolves an array of class names to a string, e.g.
	 * array(['button', 'is-disabled' => false]) => "button"
	 * array(['button', 'is-disabled' => true]) => "button is-disabled"
	 */
	public static function resolveClass(array|string $classes): string
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

	/**
	 * Getter/Setter for the suffix appended when casting to string
	 */
	public function after(string|null $after = null): static|string|null
	{
		if (func_num_args() === 0) {
			return $this->after;
		}

		$this->before = $after;

		return $this;
	}

	public function __toString()
	{
		return (string) Html::attr(
			array_map(fn ($item) => $item->value(), $this->data),
			before: $this->before,
			after: $this->after
		);
	}


	public function __call(string $name, array $args = []): self
	{
		return $this->set($name, ...$args);
	}
}
