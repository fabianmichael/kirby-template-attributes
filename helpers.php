<?php

use FabianMichael\TemplateAttributes\Attributes;
use Kirby\Toolkit\A;

/**
 * Generates an attribuets object for further manipulation or echoing as string
 *
 * @param array|Attributes $data A list of attributes as key/value array, an instance of \FabianMichael\TemplateAttributes\Attributes (useful for nested snippets) or a list of named arguments
 * @return \FabianMichael\TemplateAttributes\Attributes
 */
function attributes(...$args): Attributes
{
	return new Attributes(...$args);
}

/**
 * Shortcut for creating an attributes object andsetting the class attribute.
 *
 * @return \FabianMichael\TemplateAttributes\Attributes
 */
function classes(...$classes): Attributes
{
	// flatten array inputs
	$classes = array_reduce(
		$classes,
		fn ($carry, $item) => array_merge($carry, A::wrap($item)),
		[]
	);

	return attributes()->class($classes);
}
