---
sidebar_position: 3
---

# Store Translations

This section covers all methods for writing translations. The package makes a clear distinction between **staging** a
translation (which requires a `save()` call) and **storing** it instantly.

## Setting Base Attributes

This package is designed to be predictable. Direct property assignment always affects the main model attribute, never a
translation. The application's locale has no effect on write operations.

You can create and update your models as you always have:

```php
// App::setLocale('de'); does NOT affect storing translations.

$product = new Product();
$product->name = 'My Base Product Name';
$product->save();

// Result: A new product is created with its base attributes.
// No translations have been created.
```

To add a translation, you must use one of the explicit `setTranslation()` or `storeTranslation()` methods.

## Staging Translations (`set...`)

The `set...` methods **stage** a translation. They are only persisted to the database when you call `$model->save()`. This is
useful for grouping multiple changes in a single database transaction.

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
   Locale::ENGLISH => 'English Name',
]);

// Persist all staged translations.
$product->save();
```

## Storing Translations Instantly (`store...`)

The `store...` methods write translations **directly and instantly** to the database. They require the model to exist and will
throw an exception otherwise.

### Storing a Single Translation

```php
// This writes directly to the database. No save() call is needed.
$product->storeTranslation('description', Locale::SPANISH, 'Una descripción en español');
```

### Storing Multiple Translations

```php
// Each translation is written to the database instantly.
$product->storeTranslations('description', [
   Locale::SPANISH => 'Una descripción en español',
   Locale::ITALIAN => 'Una descrizione in italiano',
]);
```

## Using the Persistent Locale (Translation Mode)

For situations where you need to perform multiple read and write operations in a specific language, you can use `setLocale()` to activate a persistent **"translation mode"** for that model instance.

Once this mode is active:

- **Writing:** Direct assignments (`$product->name = '...'`) will now be staged as translations for the set locale.
- **Reading:** Direct access (`$product->name`) will now read from the set locale.

**Important:** This mode remains active until you explicitly disable it. Always call `resetLocale()` when you are finished to prevent unintended side effects in other parts of your code.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// Activate translation mode for German
$product->setLocale(Locale::GERMAN);

// This assignment is now STAGED as a German translation
$product->name = 'Ein deutscher Name';
$product->description = 'Eine deutsche Beschreibung';
$product->keywords = 'deutsch, Produkt, Beispiel';
$product->save(); // Persists the German translation

// This will now also read the German translation
echo $product->name; // Outputs: 'Ein deutscher Name'

// Always reset the mode when finished
$product->resetLocale();
```
