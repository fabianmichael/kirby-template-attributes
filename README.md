## Better attribute handling for snippets and templates

Whenever a snippet or component is included into a template, one often wants to pass additional variables and other parameters. Vue.js and Laravel blade templates provide means for merging the HTML attributes of components with ones fromt he outside. This is an exploration in search of better HTML attribute handling for nested snippets and components.

**⚠️ This is still work-in-progress, use at your own risk ⚠️**

### Example

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
