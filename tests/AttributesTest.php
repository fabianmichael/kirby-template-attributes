<?php

namespace FabianMichael\TemplateAttributes;

use PHPUnit\Framework\TestCase;

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
}

