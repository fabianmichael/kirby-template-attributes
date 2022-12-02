<?php

use FabianMichael\TemplateAttributes\Attributes;

/**
 * Generates an attributes
 *
 * @return \FabianMichael\TemplateAttributes\Attributes
 */
function attributes(...$args): Attributes
{
	return new Attributes(...$args);
}

function classes(array|string $classes = []): Attributes
{
	return attributes()->class($classes);
}
