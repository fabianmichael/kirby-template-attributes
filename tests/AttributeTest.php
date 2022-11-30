<?php

namespace FabianMichael\TemplateAttributes;

use Exception;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
	public function testValue(): void
	{
		$this->assertTrue((new Attribute('string'))->value() === 'string');
		$this->assertTrue((new Attribute(123))->value() === 123);
		$this->assertTrue((new Attribute(123.456))->value() === 123.456);
		$this->assertTrue((new Attribute(true))->value() === true);
		$this->assertTrue((new Attribute(false))->value() === false);
		$this->assertTrue((new Attribute(null))->value() === null);
	}

	public function testToString(): void
	{
		$this->assertEquals((string)new Attribute('string'), 'string');
		$this->assertEquals((string)new Attribute(123), 123);
		$this->assertEquals((string)new Attribute(123.456), 123.456);
		$this->assertEquals((string)new Attribute(true), true);
		$this->assertEquals((string)new Attribute(false), false);
		$this->assertEquals((string)new Attribute(null), null);
	}

	public function testMergeReplace(): void
	{
		$this->assertEquals((new Attribute('foo'))->merge('bar')->value(), 'bar');
	}

	public function testMergeAppend(): void
	{
		$this->assertEquals((new Attribute('foo', MergeStrategy::APPEND))->merge('bar')->value(), 'foo bar');
		$this->assertEquals((new Attribute('foo', MergeStrategy::APPEND, ','))->merge('bar')->value(), 'foo,bar');
		$this->assertEquals((new Attribute('foo', MergeStrategy::APPEND, ', '))->merge('bar')->value(), 'foo, bar');
	}

	public function testMergePrepend(): void
	{
		$this->assertEquals((new Attribute('foo', MergeStrategy::PREPEND))->merge('bar')->value(), 'bar foo');
		$this->assertEquals((new Attribute('foo', MergeStrategy::PREPEND, ','))->merge('bar')->value(), 'bar,foo');
		$this->assertEquals((new Attribute('foo', MergeStrategy::PREPEND, ', '))->merge('bar')->value(), 'bar, foo');
	}

	public function testIncompatibleMergeStrategy(): void
	{
		$this->expectException(Exception::class);

		$foo = new Attribute('foo', MergeStrategy::APPEND);
		$bar = new Attribute('bar', MergeStrategy::PREPEND);
		$foo->merge($bar);
	}

	public function testIncompatibleSeparator(): void
	{
		$this->expectException(Exception::class);

		$foo = new Attribute('foo', MergeStrategy::APPEND, ' ');
		$bar = new Attribute('bar', MergeStrategy::REPLACE, ', ');
		$foo->merge($bar);
	}
}

