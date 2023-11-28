<?php

namespace FabianMichael\TemplateAttributes;

use Exception;
use PHPUnit\Framework\Error\Deprecated;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AttributesTest extends TestCase
{
	public function testConstructorArray(): void
	{
		$attr = new Attributes([
			'foo' => 'bar',
			'bar' => 'foo'
		]);

		$this->assertSame((string)$attr, 'bar="foo" foo="bar"');
	}

	public function testConstrucorNamedArguments(): void
	{
		$attr = new Attributes(
			foo: 'bar',
			bar: 'foo',
		);

		$this->assertSame((string)$attr, 'bar="foo" foo="bar"');
	}

	public function testConversionToLowerCase(): void
	{
		$attr = new Attributes(
			fooBar: 'baz',
		);

		$this->assertSame((string)$attr, 'foobar="baz"');
	}

	public function testToHtml(): void
	{
		$attr = new Attributes([
			'fooBar' => 'baz',
			'standalone'
		]);

		$this->assertSame($attr->toHtml(), 'foobar="baz" standalone');
	}

	public function testEmptyInputArray(): void
	{
		$attr = new Attributes();

		$this->assertSame($attr->toHtml(), null);
		$this->assertSame($attr->toXml(), null);
		$this->assertSame((string)$attr, '');
	}

	public function testEmptyAttributes(): void
	{
		$attr = new Attributes(['foo' => '']);
		$this->assertSame((string)$attr, 'foo=""');
		$this->assertSame((string)$attr->toXml(), 'foo=""');

		$attr = new Attributes(['foo']);
		$this->assertSame((string)$attr, 'foo');
		$this->assertSame((string)$attr->toXml(), 'foo="foo"');

		$attr = new Attributes(['foo' => null, 'bar' => false]);
		$this->assertSame((string)$attr, '');
	}

	public function testSingleSpaceAttribute(): void
	{
		// should trigger e deprecated error
		@(string)(new Attributes(['foo' => ' ']));
		$this->assertTrue(error_get_last() !== null);
	}

	public function testToXml(): void
	{
		$attr = new Attributes([
			'fooBar' => 'baz',
			'standalone'
		]);

		$this->assertSame($attr->toXml(), 'fooBar="baz" standalone="standalone"');
	}

	public function testAfter(): void
	{
		$attr = (new Attributes(foo: 'bar'))->after(' ');

		$this->assertSame((string)$attr, 'foo="bar" ');
	}

	public function testBefore(): void
	{
		$attr = (new Attributes(foo: 'bar'))->before(' ');

		$this->assertSame((string)$attr, ' foo="bar"');
	}

	public function testCreateClassValue(): void
	{
		$value = $this->_callProtectedStaticMethod('createClassValue', 'foo bar');

		$this->assertSame((string)$value, 'foo bar');
	}

	public function testNullClassValue(): void
	{
		$attributes = new Attributes(class: null);
		$this->assertSame($attributes->get('class')->value(), null);
		$this->assertSame((string)$attributes, '');
	}

	public function testCreateClassValueWhiteSurplusWhiteSpace(): void
	{
		$value = $this->_callProtectedStaticMethod('createClassValue', 'foo
			bar    baz
			qux
			');

		$this->assertSame((string)$value, 'foo bar baz qux');
	}

	public function testNormalizeClassValueFromNumericArray(): void
	{
		$value = $this->_callProtectedStaticMethod('normalizeClassValue', ['foo', 'bar']);

		$this->assertSame((string)$value, 'foo bar');
	}

	public function testNormalizeClassValueFromConditionalArray(): void
	{
		$value = $this->_callProtectedStaticMethod('normalizeClassValue', [
			'foo' => true,
			'bar' => false,
			'baz'
		]);

		$this->assertSame((string)$value, 'foo baz');
	}

	public function testMerge(): void
	{
		$attr = new Attributes(foo: 'bar');
		$attr = $attr->merge(bar: 'foo');

		$this->assertSame((string)$attr, 'bar="foo" foo="bar"');
	}

	public function testMergeClassAttribute(): void
	{
		$attr = new Attributes(['class' => 'foo']);
		$attr = $attr->merge(['class' => 'bar']);
		$attr = $attr->class('baz');

		$this->assertSame((string)$attr, 'class="foo bar baz"');
	}

	public function testRepeatedSetClassAttribute(): void
	{
		$attr = new Attributes(['class' => 'foo']);
		$attr = $attr->class('bar');

		$this->assertSame((string)$attr, 'class="foo bar"');
	}

	public function testCreateStyleValue(): void
	{
		$value = $this->_callProtectedStaticMethod('createStyleValue', 'foo: bar; bar: foo;');

		$this->assertSame((string)$value, 'foo: bar; bar: foo;');
	}

	public function testNullStyleValue(): void
	{
		$attributes = new Attributes(style: null);
		$this->assertSame($attributes->get('style')->value(), null);
	}

	public function testNormalizeStyleValue(): void
	{
		$value = $this->_callProtectedStaticMethod('normalizeStyleValue', [
			'foo: bar',
			'bar: foo',
		]);

		$this->assertSame((string)$value, 'foo: bar; bar: foo');
	}

	public function testMergeStyleAttribute(): void
	{
		$attr = new Attributes(['style' => 'foo: bar']);
		$attr = $attr->merge(['style' => 'bar: foo']);
		$attr = $attr->style('baz: qux');

		$this->assertSame((string)$attr, 'style="foo: bar; bar: foo; baz: qux"');
	}

	public function testGet(): void
	{
		$name = 'foo';
		$value = 'bar';
		$attr = (new Attributes([$name => $value]));

		$this->assertSame($attr->get($name)->value(), $value);
	}

	public function testSet(): void
	{
		$attr = new Attributes();
		$attr = $attr->set('foo', 'bar');
		$attr = $attr->baz('qux');
		$attr = $attr->merge(['qux' => 'bar']);

		$this->assertSame((string)$attr, 'baz="qux" foo="bar" qux="bar"');
	}

	public function testAppend(): void
	{
		$attr = new Attributes();
		$attr->set('foo', $attr->append('bar', '-'));
		$attr->foo('baz');

		$this->assertSame($attr->get('foo')->value(), 'bar-baz');
	}

	public function testPrepend(): void
	{
		$attr = new Attributes();
		$attr->set('foo', $attr->prepend('bar', '-'));
		$attr->foo('baz');

		$this->assertSame($attr->get('foo')->value(), 'baz-bar');
	}

	public function testProtect(): void
	{
		$attr = new Attributes();
		$attr->set('foo', $attr->protect('bar'));
		$attr->foo('baz');

		$this->assertSame($attr->get('foo')->value(), 'bar');
	}

	public function testInvoke(): void
	{
		$attr = (new Attributes())(foo: 'bar')(baz: 'qux');
		$this->assertSame((string)$attr, 'baz="qux" foo="bar"');
	}

	public function testOffsetGet(): void
	{
		$attr = new Attributes(foo: 'bar');
		$this->assertSame((string)$attr['foo'], 'bar');
	}

	public function testOffsetGetNonExisting(): void
	{
		$attr = new Attributes(foo: 'bar');
		$this->assertNull($attr['baz']);
	}

	public function testOffsetSet(): void
	{
		$attr = new Attributes();
		$attr['foo'] = 'bar';
		$this->assertSame((string)$attr, 'foo="bar"');
	}


	public function testOffsetSetInvalidKey(): void
	{
		$this->expectException(Exception::class);
		$attr = new Attributes();
		$attr[] = 'foo';
	}

	public function testOffsetExists(): void
	{
		$attr = new Attributes(foo: 'bar');
		$this->assertTrue(isset($attr['foo']));
	}

	public function testOffsetUnset(): void
	{
		$attr = new Attributes(foo: 'bar');
		unset($attr['foo']);
		$this->assertSame((string)$attr, '');
	}


	protected static function _callProtectedStaticMethod(string $name, ...$args): mixed
	{
		$class = new ReflectionClass(Attributes::class);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invoke(null, ...$args);
	}
}
