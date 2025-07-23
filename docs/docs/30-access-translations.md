---
sidebar_position: 2
---

# Access Translations

This section covers all methods for reading translated values from your models. There are three distinct ways to access translations, depending on your needs.

## 1. Default Access (Property)

The easiest way to get a translated attribute is to access it directly as a property. This uses a fallback mechanism based on the **application's current locale**.

### Attribute Reading: Fallback Order

When you access an attribute directly like `$product->name`, the trait tries to find a translation in a specific, prioritized order:

1.  **Persistent Locale:** The locale set via `$model->setLocale()`.
2.  **Application Locale:** The current locale of your Laravel app (`App::getLocale()`).
3.  **Config Fallback Locale:** The locale defined in `config/translatable.php`.
4.  **Original Attribute:** The value from the model's original database column.

This ensures that a read access will always return the most relevant value available.

```php
App::setLocale('de');

$product = Product::find(1);

// Outputs the German translation, or falls back.
echo $product->name;
```

If no suitable translation is found, the package safely returns the original attribute value.

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

// Fetches the French translation for this one operation.
echo $product->getTranslation('name', Locale::FRENCH);
```

### The `inLocale()` Method (Fluent Alternative)

Alternatively, you can use the `inLocale()` method, which returns a proxy object to chain the attribute name.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// Also fetches the French translation, but in a more fluent way.
echo $product->inLocale(Locale::FRENCH)->name;
```

## 3. Translation Mode (Stateful)

For situations where you need to perform multiple read and write operations in a specific language, you can set a **persistent locale** for that instance using `setLocale()`.

Once set, all direct property access (`$product->name`) will read from this locale until it is reset.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// Put the model into a persistent German locale mode.
$product->setLocale(Locale::GERMAN);

// This will now consistently read the German translation.
echo $product->name; // Outputs the German name

// Always reset the locale when finished to revert to default behavior.
$product->resetLocale();
```

---

## 4. Accessing JSON Translations

For attributes that store structured data, you can configure them to be handled as translatable JSON objects.

### Configuration

To enable this behavior for an attribute (e.g., `options`), you must do three things in your model:

1.  Add the attribute to the main `$translatable` array.
2.  Add the attribute name to the `$allowJsonTranslationsFor` array.
3.  Cast the attribute to `array` or `json` in the `$casts` property.

**Note:** When an attribute is listed in `$allowJsonTranslationsFor`, the default multi-locale array assignment (Spatie-compatible API) is disabled for it. Assigning an array will always be treated as a single JSON translation for the current locale.

```php
// In your model
class Product extends Model 
{
    use HasTranslations;

    // 1. The attribute must be declared as translatable.
    public array $translatable = ['name', 'options'];

    // 2. Define the attribute as a JSON translation target.
    public array $allowJsonTranslationsFor = ['options'];

    // 3. Cast the attribute to automatically handle encoding/decoding.
    protected $casts = [
        'options' => 'array',
    ];
}
```

### Reading the Value

Once configured, accessing the attribute will automatically return a decoded PHP array. The translation is retrieved based on the standard fallback order (persistent locale, app locale, etc.).

```php
// Assuming the 'de' translation for 'options' is '{"color":"blau","size":"groß"}'
app()->setLocale('de');

$options = $product->options;

// Outputs: ['color' => 'blau', 'size' => 'groß']
print_r($options);
```

## 5. Eager-Loading Translations (Performance)

When you retrieve a collection of models, you often run into the "N+1 query problem," where one query is executed to get the models, and then N additional queries are executed to get the translations for each model.

To solve this, the package provides a `getWithTranslations()` macro that you can use instead of the standard `get()` method. This will eager-load all required translations in a single, efficient query, drastically improving performance.

```php
// Instead of this (N+1 problem):
// $products = Product::where('active', true)->get();

// Use this to eager-load translations:
$products = Product::where('active', true)->getWithTranslations();

foreach ($products as $product) {
    // No additional queries are executed here.
    echo $product->name;
}
```