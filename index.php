<?php

use FabianMichael\TemplateAttributes\Attributes;
use Kirby\Cms\App;
use Kirby\Template\Snippet;

@include_once __DIR__ . '/vendor/autoload.php';

App::plugin('fabianmichael/template-attributes', [
	'components' => [
		'snippet' => function (
			App $kirby,
			string|array|null $name,
			array $data = [],
			bool $slots = false,
		): Snippet|string {
			$attributes = new Attributes($data['attr'] ?? []);

			foreach (['id', 'class', 'style'] as $attribute) {
				if (isset($data[$attribute])) {
					$attributes->set($attribute, $data[$attribute]);
				}
			}

			return Snippet::factory($name, $data + [
				'attributes' => $attributes,
			], $slots);
		}
	],
]);
