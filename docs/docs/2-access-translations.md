---
sidebar_position: 2
---

# Access Translations

This section covers all methods for reading translated values from your models. There are three distinct ways to access translations, depending on your needs.

## 1. Default Access (Property)

The easiest way to get a translated attribute is to access it directly as a property. This uses a fallback mechanism based on the **application's current locale**.

### Attribute Reading: Fallback Order

When you access an attribute directly like $product->name, the trait tries to find a translation in a specific, prioritized order:

1. **Persistent Locale:** The locale set via `$model->setLocale()`.
2. **Application Locale:** The current locale of your Laravel app (`App::getLocale()`).
3. **Config Fallback Locale:** The locale defined in `config/translatable.php`.
4. **Original Attribute:** The value from the model's original database column.

This ensures that a read access will always return the most relevant value available.

```php
App::setLocale('de');

$product = Product::find(1);

// Outputs the German translation, or falls back.
echo $product->name;
```

If no suitable translation is found in any of the configured locales, the package will safely return the original attribute from the model's database column.

```php
// Assuming:
// - The product's base name is 'Base Product Name'
// - No German translation exists
// - The config fallback is not set or its translation doesn't exist

App::setLocale('de');
$product = Product::find(1);

// Outputs: 'Base Product Name'
echo $product->name;
```

If you explicitly need to access the original attribute without any translation, you can use Laravel's `getOriginal()` method:

```php
echo $product->getOriginal('name'); // Outputs the original database value.
```

## 2. Specific Access (Stateless)

To get a translation for a specific language **without** changing the model's state, you have two options.

### The `getTranslation()` Method (Recommended)

This is the most explicit and direct way to fetch a single translation. It's clear, easy to read, and predictable.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// Fetches the English translation for this one operation.
echo $product->getTranslation('name', Locale::ENGLISH);
```

### The `inLocale()` Method (Fluent Alternative)

Alternatively, you can use the `inLocale()` method, which returns a proxy object to chain the attribute name. Please note that this approach may not provide IDE autocompletion for the attribute name.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// Also fetches the English translation, but in a more fluent way.
echo $product->inLocale(Locale::ENGLISH)->name;
```

## 3. Translation Mode (Stateful)

For situations where you need to perform multiple read and write operations in a specific language, you can set a **persistent locale** for that instance using `setLocale()`.

Once set, all direct property access (`$product->name`) will read from this locale until it is reset.

**Important:** This is a persistent state for the model instance. It's best practice to reset it with `resetLocale()` when you are done.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// Put the model into a persistent German locale mode.
$product->setLocale(Locale::GERMAN);

// This will now consistently read the German translation.
echo $product->name; // Outputs the German name
echo $product->description; // Outputs the German description

// Always reset the locale when finished to revert to default behavior.
$product->resetLocale();
```
