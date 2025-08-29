<?php

namespace FabianMichael\TemplateAttributes;

use ArrayAccess;
use DOMDocument;
use Exception;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Html;
use Kirby\Toolkit\Xml;
use Stringable;

/**
 * The `Attribute` class represents a set of HTML attributes intended
 * for rendering them as a string.
 */
class Attributes implements ArrayAccess, Stringable
{
	protected array $data = [];
	protected ?string $before = null;
	protected ?string $after = null;

	// see https://html.spec.whatwg.org/multipage/indices.html#attributes-3
	public static array $booleanAttributes = [
		'allowfullscreen',
		'async',
		'autofocus',
		'autoplay',
		'checked',
		'controls',
		'default',
		'defer',
		'disabled',
		'download', // can also be used with a value
		'formnovalidate',
		'hidden', // not a boolean attribute, but can be used like one
		'inert',
		'ismap',
		'itemscope',
		'loop',
		'multiple',
		'muted',
		'nomodule',
		'novalidate',
		'open',
		'playsinline',
		'readonly',
		'required',
		'reversed',
		'selected',
		'shadowrootclonable',
		'shadowrootdelegatesfocus',
		'shadowrootserializable',
	];

	public function __construct(...$data)
	{
		$this->merge(...$data);
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

	protected static function createClassValue(string|null $value): AttributeValue
	{
		$value = !is_null($value) ? trim(preg_replace('/[\r\n\s]+/', ' ', $value)) : null;

		return new AttributeValue($value, MergeStrategy::APPEND, ' ');
	}

	protected static function createStyleValue(string|null $value): AttributeValue
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

	public function merge(...$data): static
	{
		if (count($data) === 1 && array_key_first($data) === 0) {
			// single array/object input
			$data = $data[0];
		}

		if (is_a($data, self::class)) {
			// argument was another instance of Attributes
			$data = $data->data;
		}

		if (is_string($data)) {
			$data = static::parseAttributesString($data);
		}

		foreach ($data as $name => $value) {
			$this->set($name, $value);
		}

		return $this;
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->data[$offset] ?? null;
	}

	public function offsetExists(mixed $offset): bool
	{
		return array_key_exists($offset, $this->data);
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->data[$offset]);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (is_null($offset)) {
			throw new Exception('Appending to an attributes list without supplying an attribute name is not supported.');
		}

		$this->set($offset, $value);
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
	public static function normalizeClassValue(array|string|null $classes): ?string
	{
		if (is_null($classes)) {
			return null;
		}

		$value = [];

		foreach (A::wrap($classes) as $key => $class) {
			if (is_numeric($key)) {
				$value[] = $class;
			} elseif ($class) {
				$value[] = $key;
			}
		}

		if (count($value) === 0) {
			return null;
		}

		return implode(' ', array_unique($value));
	}

	/**
	 * normalizes an array of class names to a string, e.g.
	 * array(['font-size: 1rem', 'color: red' => false]) => "font-size: 1rem;"
	 * array(['font-size: 1rem', 'color: red' => true]) => "font-size: 1rem; color: red"
	 */
	public static function normalizeStyleValue(array|string|null $styles): ?string
	{
		if (is_null($styles)) {
			return null;
		}

		$styles = A::wrap($styles);
		$value = [];

		foreach ($styles as $key => $property) {
			if (is_numeric($key)) {
				$value[] = $property;
			} elseif ($property) {
				$value[] = $key;
			}
		}

		if (count($value) === 0) {
			return null;
		}

		return implode('; ', array_map(fn($v) => trim($v, '; '), $value));
	}

	public function set(string $name, mixed $value): static
	{
		if (method_exists(static::class, $method = 'normalize' . ucfirst($name) . 'Value')) {
			$value = static::$method($value);
		}

		if (array_key_exists($name, $this->data)) {
			// Merge with existing attribute
			$this->data[$name] = $this->data[$name]->merge($value);
		} elseif (is_a($value, AttributeValue::class)) {
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

	public function toHtml(): string|null
	{
		return Html::attr(
			array_map(fn ($item) => $item->value(), $this->data),
			before: $this->before,
			after: $this->after
		);
	}

	public function toXml(): string|null
	{
		$attr = Xml::attr(
			array_map(fn ($item) => $item->value(), $this->data),
		);

		if ($attr) {
			return $this->before . $attr . $this->after;
		}

		return null;
	}

	public function __toString(): string
	{
		return $this->toHtml() ?? '';
	}

	protected static function parseAttributesString(string $str): array
	{
		$tagName = 'yolo';
		$errorsBefore = libxml_use_internal_errors();
		$attr = [];

		libxml_use_internal_errors(true);
		$dom = new DOMDocument();
		$dom->loadHTML("<{$tagName} {$str}>");
		$node = $dom->getElementsByTagName($tagName)->item(0);
		if ($node->hasAttributes()) {
			foreach ($node->attributes as $attribute) {
				$name = $attribute->nodeName;
				$value = $attribute->nodeValue;

				if (in_array($value, ['', $name]) && in_array($name, static::$booleanAttributes)) {
					// convert known boolean attributes to bool
					$value = true;
				}

				$attr[$name] = $value;
			}
		}

		libxml_use_internal_errors($errorsBefore);
		return $attr;
	}
}
