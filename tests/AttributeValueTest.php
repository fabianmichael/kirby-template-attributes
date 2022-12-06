<?php

namespace FabianMichael\TemplateAttributes;

use Exception;
use PHPUnit\Framework\TestCase;

class AttributeValueTest extends TestCase
{
	public function testValue(): void
	{
		$this->assertTrue((new AttributeValue('string'))->value() === 'string');
		$this->assertTrue((new AttributeValue(123))->value() === 123);
		$this->assertTrue((new AttributeValue(123.456))->value() === 123.456);
		$this->assertTrue((new AttributeValue(true))->value() === true);
		$this->assertTrue((new AttributeValue(false))->value() === false);
		$this->assertTrue((new AttributeValue(null))->value() === null);
	}

	public function testToString(): void
	{
		$this->assertEquals((string)new AttributeValue('string'), 'string');
		$this->assertEquals((string)new AttributeValue(123), 123);
		$this->assertEquals((string)new AttributeValue(123.456), 123.456);
		$this->assertEquals((string)new AttributeValue(true), true);
		$this->assertEquals((string)new AttributeValue(false), false);
		$this->assertEquals((string)new AttributeValue(null), null);
	}

	public function testMergeReplace(): void
	{
		$this->assertEquals((new AttributeValue('foo'))->merge('bar')->value(), 'bar');
	}

	public function testMergeAppend(): void
	{
		$this->assertEquals((new AttributeValue('foo', MergeStrategy::APPEND))->merge('bar')->value(), 'foo bar');
		$this->assertEquals((new AttributeValue('foo', MergeStrategy::APPEND, ','))->merge('bar')->value(), 'foo,bar');
		$this->assertEquals((new AttributeValue('foo', MergeStrategy::APPEND, ', '))->merge('bar')->value(), 'foo, bar');
	}

	public function testMergePrepend(): void
	{
		$this->assertEquals((new AttributeValue('foo', MergeStrategy::PREPEND))->merge('bar')->value(), 'bar foo');
		$this->assertEquals((new AttributeValue('foo', MergeStrategy::PREPEND, ','))->merge('bar')->value(), 'bar,foo');
		$this->assertEquals((new AttributeValue('foo', MergeStrategy::PREPEND, ', '))->merge('bar')->value(), 'bar, foo');
	}

	public function testMergeProtect(): void
	{
		$this->assertEquals((new AttributeValue('foo', MergeStrategy::PROTECT))->merge('bar')->value(), 'foo');
	}

	public function testIncompatibleMergeStrategy(): void
	{
		$this->expectException(Exception::class);

		$foo = new AttributeValue('foo', MergeStrategy::APPEND);
		$bar = new AttributeValue('bar', MergeStrategy::PREPEND);
		$foo->merge($bar);
	}

	public function testIncompatibleSeparator(): void
	{
		$this->expectException(Exception::class);

		$foo = new AttributeValue('foo', MergeStrategy::APPEND, ' ');
		$bar = new AttributeValue('bar', MergeStrategy::REPLACE, ', ');
		$foo->merge($bar);
	}
}
