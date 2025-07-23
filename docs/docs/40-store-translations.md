---
sidebar_position: 3
---

# Store Translations

This section covers all methods for writing translations. The package makes a clear distinction between **staging** a
translation (which requires a `save()` call) and **storing** it instantly.

## 1. Staging Translations (`set...`)

The `set...` methods **stage** a translation. They are only persisted to the database when you call `$model->save()`. This is
the recommended approach for grouping multiple changes in a single database transaction.

### Staging a Single Translation

```php
use Aaix\EloquentTranslatable\Enums\Locale;

$product->setTranslation('name', Locale::GERMAN, 'Ein deutscher Name');

// The translation is only staged. Now, we save it to the database.
$product->save();
```

### Staging Multiple Translations

```php
$product->setTranslations('name', [
   Locale::GERMAN => 'Deutscher Name',
   Locale::DUTCH => 'Nederlandse Naam',
]);

// Persist all staged translations.
$product->save();
```

## 2. Storing Translations Instantly (`store...`)

The `store...` methods write translations **directly and instantly** to the database. They require the model to exist and will
throw an exception otherwise.

### Storing a Single Translation

```php
// This writes directly to the database. No save() call is needed.
$product->storeTranslation('description', Locale::FRENCH, 'Une description en français');
```

### Storing Multiple Translations

```php
// Each translation is written to the database instantly.
$product->storeTranslations('description', [
   Locale::FRENCH => 'Une description en français',
   Locale::DUTCH => 'Een Nederlandse omschrijving',
]);
```

## 3. Translation Mode (Stateful)

For situations where you need to perform multiple read and write operations in a specific language, you can use `setLocale()` to activate a persistent **"translation mode"** for that model instance.

Once this mode is active, direct assignments (`$product->name = '...'`) will be staged as translations for the set locale.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// Activate translation mode for German
$product->setLocale(Locale::GERMAN);

// This assignment is now STAGED as a German translation
$product->name = 'Ein deutscher Name';
$product->save(); // Persists the German translation

// Always reset the mode when finished
$product->resetLocale();
```

---

## 4. Mass-Assignment (Spatie-Compatible API)

As a convenient alternative, you can assign a multi-locale array directly to a translatable attribute. This stages all translations and is compatible with the API of [`spatie/laravel-translatable`](https://github.com/spatie/laravel-translatable).

```php
$product = new Product();

// Assign a multi-locale array to the 'name' attribute
$product->name = [
   'de' => 'Mein tolles Produkt',
   'nl' => 'Mijn geweldige product',
];

// The translations are staged and saved in a single operation.
$product->save();
```

## 5. Storing JSON Translations

For attributes that store structured data, you can configure them to be handled as translatable JSON objects.

### Configuration

To enable this behavior for an attribute (e.g., `options`), you must do three things in your model:

1.  Add the attribute to the main `$translatable` array.
2.  Add the attribute name to the `$allowJsonTranslationsFor` array.
3.  Cast the attribute to `array` or `json` in the `$casts` property.

**Note:** When an attribute is listed in `$allowJsonTranslationsFor`, the default multi-locale array assignment (Spatie-compatible API) is disabled for it. Assigning an array will always be treated as a single JSON translation.

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

### Usage

To store a JSON translation, use the explicit `setTranslation()` method. If the attribute is configured for JSON translations, you can pass a PHP array directly—the package will automatically handle the JSON encoding.

This is the recommended approach as it is stateless, explicit, and avoids any unintended side effects.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// The keys ('color', 'size') remain the same across translations.
$options = ['color' => 'blau', 'size' => 'groß'];

// The package automatically encodes the array to JSON.
$product->setTranslation('options', Locale::GERMAN, $options);

$product->save(); // Persists the German translation
```