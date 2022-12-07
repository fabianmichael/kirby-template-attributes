<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/helpers.php';

class AttributeValueTest extends TestCase
{
	public function testAttributesHelper(): void
	{
		$this->assertEquals((string)attributes(foo: 'bar', baz: 'qux'), 'baz="qux" foo="bar"');
	}

	public function testClassesHelper(): void
	{
		$this->assertEquals((string)classes('foo'), 'class="foo"');
		$this->assertEquals((string)classes('foo', 'bar'), 'class="foo bar"');
		$this->assertEquals((string)classes('foo', ['bar', 'baz']), 'class="foo bar baz"');
	}
}
