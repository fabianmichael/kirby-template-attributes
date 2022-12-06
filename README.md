# Kirby Template Attributes

**Better attribute API for snippets and templates**

This plugin brings [Vue.js](https://vuejs.org)/[Laravel-Blade](https://laravel.com/docs/9.x/blade#components)-like attribute composition to the templates of your [Kirby](https://getkirby.com) yproject. This is an exploration in search of better HTML attribute handling for nested snippets and components.

**⚠️ Work-in-progress, use at your own risk ⚠️**


## Requirements

- Kirby 3.8
- PHP 8.1 (because of [Enumeration](https://www.php.net/manual/en/language.types.enumerations.php) support)

## Installation

During this early development stage, installation only works via composer:

```
composer require fabianmichael/kirby-template-attributes
```

Alternatively, if you want to contribute to the development of this plugin, you can install it via submodule or clone this repository and use it as a local composer dependency.

## Usage

### Basic usage

The plugin provides 2 helpers functions as entry points:

```php
# site/snippets/button.php

<button <?= attributes([
  'role' => 'button',
  'aria-expanded' => 'false',
]) ?>>[…]</button>
```

```php
# site/snippets/button.php

<button <?= classes([
  'button',
  'button--red' => $color === 'red', // class will only appear in class attribute, if condition is true
]) ?>>[…]</button>
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
    'class' => 'absolute top-0 left-0', // classes are automatically appended to the existing attribute value
    'style' => '--bar: foo', // style attribute value is also appended to the original value
  ],
]) ?>
```

## Custom merge strategies

- Custom merge strategies for arbitrary attributes


## Before/After

```
->before()
->after()
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

$text = $text ?? 'Button text';
$size = $size ?? 'normal';
$theme = $theme ?? null;
$attr = $attr ?? [];

?>
<button <?= attributes([
    'role' => 'button',
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
