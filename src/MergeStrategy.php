<?php

namespace FabianMichael\TemplateAttributes;

enum MergeStrategy
{
	case REPLACE;
	case PROTECT;
	case APPEND;
	case PREPEND;
}
