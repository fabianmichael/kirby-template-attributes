<?php

use FabianMichael\TemplateAttributes\Attributes;

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
function classes(array|string $classes = []): Attributes
{
	return attributes()->class($classes);
}
