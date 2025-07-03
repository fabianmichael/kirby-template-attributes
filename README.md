# Kirby Template Attributes

**Better attribute API for snippets and templates**

This plugin brings [Vue.js](https://vuejs.org)/[Laravel-Blade](https://laravel.com/docs/9.x/blade#components)-like attribute composition to the templates of your [Kirby project](https://getkirby.com). This is an exploration in search of better HTML attribute handling for nested snippets and components.

## Requirements

- Kirby 4/5 (use version 1.x for Kirby 3 installations)
- PHP 8.3 (because of [Enumeration](https://www.php.net/manual/en/language.types.enumerations.php) support)

## Installation

The recommended is installation via composer.

```
composer require fabianmichael/kirby-template-attributes
```

Alternatively, you can also [download the plugin](https://github.com/fabianmichael/kirby-template-attributes/archive/main.zip) and install it manually by copying it to the `site/plugins/` folder of your website.

## Usage

### Basic usage

Use the `attributes()` helper for generating a string of attributes:

```php
<button <?= attributes([
  'role' => 'button',
  'aria-expanded' => 'false',
]) ?>>[…]</button>
```
You can also use named arguments if you prefer a leaner syntax. Be aware, that this only works as long as you don’t have dashes in your attribute names:

```php 
<img <?= attributes(
  class: 'icon',
  width: 16,
  height: 16,
  src: $image->url(),
	alt: 'The funniest donkey ever!',
) ?>>
```

Or if all you have is an attributes string, you can also feed the that to the `attributes()` helper:

```php
<?php

// get image dimensions as height="yyy" width="xxx" 
$src  = 'img.png';
$size = getimagesize($src)[3];

?>

<img <?= attributes($size)->merge([
	'src' => $src,
	'alt' => '',
]) ?>>
```

⚠️ If you need XML-compatible attributes, always call `$attributes->toXml()` instead of just echoing the `Attributes` object, because otherwise all attributes will be converted to lower-case.

In many cases, you need to set different classes. The `classes()` helper is a nice shortcut for improved readability:

```php
<button <?= classes([
  'button',
  'button--red' => $color === 'red', // class will only appear in class attribute, if condition is true
]) ?>>[…]</button>
```

The `classes()` helper is pretty flexible and also accepts multiple paramaters, each of those can eithe be a string or array (but please ensure to write readible code anyways):

```php
<button <?= classes('button', [
  'button--red' => $color === 'red',
], 'absolute', 'top-0 left-0') ?>>[…]</button>
```

## Merging attributes

```php
# site/snippets/button.php

<button <?= attributes([
  'class' => 'button',
  'role' => 'button',
  'aria-expanded' => 'false',
  'style' => '--foo: bar',
])->merge($attr ?? []) ?>>[…]</button>

# site/templates/default.php

<?php snippet('button', [
  'attr' => [
    'role' => 'unicorn', // attributes can be overridden
    'onclick' => 'alert("everyone likes alerts!!!")',
    'class' => 'absolute top-0 left-0
      md:left-4
      xl:left-8', // classes are automatically appended to the existing attribute value and surplus whitespace is trimmed
    'style' => '--bar: foo', // style attribute value is also appended to the original value
  ],
]) ?>
```

## Before/After

You can set `$before` and `$after`, just like for Kirby’s `Html::attr()` helper by using the corresponding methods:

```
attributes(class: 'foo')->before(' ');
attributes(class: 'foo')->after(' ');
```

## Examples

A button component exists as a snippet in `site/snippets/button.php`:

```php
<button class="button"><?= html($text ?? 'Button text') ?></button>
```

A common situation would be the requirement to add attributes when calling the `snippet('button')` helper class, e.g. `class`, `data-*`, `title`, `aria-*` etc. Developers cannot handly every possible attribute for each component. The previous `attributes()` helper could help here:

```php
<button <?= attributes($attr ?? []) ?> class="button"><?= html($text ?? 'Button text') ?></button>
```

This works better, but we still cannot extend the `class` attribute easily. Enter the new `attributes()` helper:

```php
<button <?= attributes([
    'class' => 'button',
])->merge($attr ?? []) ?>><?= html($text ?? 'Button text') ?></button>
```

Even shorter:

```php
<button <?= classes('button')->merge($attr ?? []) ?>><?= html($text ?? 'Button text') ?></button>
```

This becomes even cooler, because the classes can be assigned conditionally as an array:

```php
<?php

$text ??= 'Button text';
$size ??= 'normal';
$theme ??= null;
$attr ??= [];

?>
<button <?= attributes([
    'role' => 'button',
    'style' => [
      'font-size: 2rem;' => ($size === 'large'),
    ],
])->class([
    'button',
    "button--{$size}",
    "button--{$theme}" => $theme, // will only be merged, if $theme is trueish
])->merge($attr) ?>><?= html($text) ?></button>
```

This is already cool and makes working with attributes for snippets much easier, e.g. is we use the button in `site/snippets/menu.php`:

```php
<nav class="menu">
    […]
    <?php snippet('button', [
        'text' => 'Toggle Menu',
        'attr' => [
            'class' => 'menu__button',
            'aria-controls' => 'menu-popup',
            'aria-expanded' => false,
            'role' => 'teapot', // overrrides the default attribute
        ],
    ]) ?>
</nav>
```


## License

MIT (but you are highly encouraged to **[❤️ sponsor me](https://github.com/sponsors/fabianmichael)**, if this piece of software helps you to pay your bills).
