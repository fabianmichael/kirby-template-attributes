<?php

namespace FabianMichael\TemplateAttributes;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AttributesTest extends TestCase
{
	public function testConstrucorArray(): void
	{
		$attr = new Attributes([
			'foo' => 'bar',
			'bar' => 'foo'
		]);

		$this->assertEquals((string)$attr, 'bar="foo" foo="bar"');
	}

	public function testConstrucorNamedArguments(): void
	{
		$attr = new Attributes(
			foo: 'bar',
			bar: 'foo',
			kebabCase: 'kebab'
		);

		$this->assertEquals((string)$attr, 'bar="foo" foo="bar" kebab-case="kebab"');
	}

	public function testAfter(): void
	{
		$attr = (new Attributes(foo: 'bar'))->after(' ');

		$this->assertEquals((string)$attr, 'foo="bar" ');
	}

	public function testBefore(): void
	{
		$attr = (new Attributes(foo: 'bar'))->before(' ');

		$this->assertEquals((string)$attr, ' foo="bar"');
	}

	public function testCreateClassValue(): void {
		$value = $this->_callProtectedStaticMethod('createClassValue', 'foo bar');

		$this->assertEquals((string)$value, 'foo bar');
	}

	public function testNormalizeClassValueFromNumericArray(): void {
		$value = $this->_callProtectedStaticMethod('normalizeClassValue', ['foo', 'bar']);

		$this->assertEquals((string)$value, 'foo bar');
	}

	public function testNormalizeClassValueFromConditionalArray(): void {
		$value = $this->_callProtectedStaticMethod('normalizeClassValue', [
			'foo' => true,
			'bar' => false,
			'baz'
		]);

		$this->assertEquals((string)$value, 'foo baz');
	}

	public function testMergeClassAttribute(): void {
		$attr = new Attributes(['class' => 'foo']);
		$attr = $attr->merge(['class' => 'bar']);
		$attr = $attr->class('baz');

		$this->assertEquals((string)$attr, 'class="foo bar baz"');
	}

	public function testRepeatedSetClassAttribute(): void {
		$attr = new Attributes(['class' => 'foo']);
		$attr = $attr->class('bar');

		$this->assertEquals((string)$attr, 'class="foo bar"');
	}

	public function testCreateStyleValue(): void {
		$value = $this->_callProtectedStaticMethod('createStyleValue', 'foo: bar; bar: foo;');

		$this->assertEquals((string)$value, 'foo: bar; bar: foo;');
	}

	public function testNormalizeStyleValue(): void {
		$value = $this->_callProtectedStaticMethod('normalizeStyleValue', [
		'foo: bar',
			'bar: foo',
		]);

		$this->assertEquals((string)$value, 'foo: bar; bar: foo');
	}

	public function testMergeStyleAttribute(): void {
		$attr = new Attributes(['style' => 'foo: bar']);
		$attr = $attr->merge(['style' => 'bar: foo']);
		$attr = $attr->style('baz: qux');

		$this->assertEquals((string)$attr, 'style="foo: bar; bar: foo; baz: qux"');
	}

	public function testGet(): void
	{
		$name = 'foo';
		$value = 'bar';
		$attr = (new Attributes([$name => $value]));

		$this->assertEquals($attr->get($name)->value(), $value);
	}

	public function testSet(): void
	{
		$attr = new Attributes();
		$attr = $attr->set('foo', 'bar');
		$attr = $attr->baz('qux');
		$attr = $attr->merge(['qux' => 'bar']);

		$this->assertEquals((string)$attr, 'baz="qux" foo="bar" qux="bar"');
	}

	public function testAppend(): void
	{
		$attr = new Attributes();
		$attr->set('foo', $attr->append('bar', '-'));
		$attr->foo('baz');

		$this->assertEquals($attr->get('foo')->value(), 'bar-baz');
	}

	public function testPrepend(): void
	{
		$attr = new Attributes();
		$attr->set('foo', $attr->prepend('bar', '-'));
		$attr->foo('baz');

		$this->assertEquals($attr->get('foo')->value(), 'baz-bar');
	}

	public function testProtect(): void
	{
		$attr = new Attributes();
		$attr->set('foo', $attr->protect('bar'));
		$attr->foo('baz');

		$this->assertEquals($attr->get('foo')->value(), 'bar');
	}

	protected static function _callProtectedStaticMethod(string $name, ...$args): mixed
	{
		$class = new ReflectionClass(Attributes::class);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invoke(null, ...$args);
	}
}
