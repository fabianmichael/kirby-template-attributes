<?php

use FabianMichael\TemplateAttributes\Attributes;

/**
 * Generates an attributes
 *
 * @param array|null $data A list of attributes as key/value array or an instance of \FabianMichael\TemplateAttributes\Attributes (useful for nested snippets)
 * @param string|null $before An optional string that will be prepended if the result is not empty
 * @param string|null $after An optional string that will be appended if the result is not empty
 * @return \FabianMichael\TemplateAttributes\Attributes
 */
function attributes(array|Attributes $data = []): Attributes
{
	return new Attributes($data);
}

function classes(array|string $classes = []): Attributes
{
	return attributes()->class($classes);
}
