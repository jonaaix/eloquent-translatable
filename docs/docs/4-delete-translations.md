---
sidebar_position: 4
---

# Delete Translations

The `deleteTranslations()` method provides a flexible way to remove translations from the database.

## 1. Deleting a Single Locale's Translations

To remove all translations for a specific language, pass the locale to the method.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// Deletes all German translations for this product.
$product->deleteTranslations(Locale::GERMAN);
```

## 2. Deleting Multiple Locales' Translations

You can pass an array of locales to remove translations for multiple languages at once.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// Deletes all Dutch and French translations for this product.
$product->deleteTranslations([Locale::DUTCH, 'fr']);
```

## 3. Deleting All Translations

If you call the method with no arguments, it will delete **all** translations associated with that model instance across all locales.

```php
// Deletes every translation for this product.
$product->deleteTranslations();
```
