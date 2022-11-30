<?php

namespace FabianMichael\TemplateAttributes;

enum MergeStrategy {
	case REPLACE;
	case APPEND;
	case PREPEND;
}
