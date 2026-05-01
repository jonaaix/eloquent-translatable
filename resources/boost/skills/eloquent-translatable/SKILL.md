---
name: eloquent-translatable
description: High-performance translations for Laravel Eloquent models via a dedicated translations table (not JSON columns).
---

# Eloquent Translatable

Translations live in a dedicated `{model}_translations` table with a composite index (`{model}_id`, `locale`, `column_name`). The original model column always holds the **fallback-locale** value (from `config/app.php`) — never repurpose it.

## When to use

Working with `aaix/eloquent-translatable`: making a model translatable, generating translation tables, reading/writing/querying translations, configuring custom tables, JSON translation attributes, or a separate translation DB.

## Setup

```bash
php artisan make:translation-table Product
php artisan migrate
```

```php
use Aaix\EloquentTranslatable\Traits\HasTranslations;

class Product extends Model
{
    use HasTranslations;
    public array $translatable = ['name', 'description']; // or ['*']
}
```

## Read

Fallback chain: persistent locale (`setLocale()`) → app locale → config fallback → original column.

```php
$product->name;                                  // uses fallback chain
$product->getTranslation('name', Locale::FRENCH); // explicit, stateless (preferred)
$product->inLocale(Locale::FRENCH)->name;         // fluent, stateless
$product->getOriginal('name');                    // bypass translation entirely

$product->getTranslations('name');                // ['en'=>..., 'de'=>...] (Spatie-compat)
$product->getTranslations();                      // all attrs, nested
```

**Avoid N+1:** use `Product::query()->getWithTranslations()` instead of `->get()` when iterating translations.

## Write

Prefer **staging + save** over instant writes to batch DB queries.

```php
// Staged — persisted on save()
$product->setTranslation('name', Locale::GERMAN, 'Name');
$product->setTranslations('name', [Locale::GERMAN => 'Name', Locale::DUTCH => 'Naam']);
$product->name = ['de' => 'Name', 'nl' => 'Naam']; // Spatie-compat mass assignment
$product->save();

// Instant — model must already exist
$product->storeTranslation('name', Locale::GERMAN, 'Name');
$product->storeTranslations('name', [...]);
```

Stateful mode (always pair with `resetLocale()`):

```php
$product->setLocale(Locale::GERMAN);
$product->name = 'Name'; // staged as German
$product->save();
$product->resetLocale();
```

## Delete / Query

```php
$product->deleteTranslations(Locale::GERMAN);    // one locale
$product->deleteTranslations(['nl', 'fr']);       // multiple
$product->deleteTranslations();                    // all

Product::whereTranslation('name', 'Wert', 'de')->get(); // indexed JOIN
```

## Locale enum

Use `Aaix\EloquentTranslatable\Enums\Locale` (string-backed, ~50 cases) instead of raw strings. Custom string-backed enums work too (e.g., for `de-AT`); methods accept any string-backed enum or raw string.

## JSON translation attributes

```php
public array $translatable = ['name', 'options'];
public array $allowJsonTranslationsFor = ['options'];
protected $casts = ['options' => 'array'];
```

`setTranslation('options', $locale, $array)` encodes JSON automatically; reads decode automatically. **Note:** Spatie-style multi-locale array assignment is disabled for JSON attributes — array assignment is treated as a single JSON value for the current locale.

## Customization (model properties)

```php
protected ?string $translationTable      = 'my_product_translations';
protected ?string $translationForeignKey = 'my_product_id';
protected ?string $translationModel      = ProductTranslationCustom::class; // for translations() relation
```

## Separate DB connection

Set `'database_connection' => 'translation_db'` in `config/eloquent-translatable.php` (or `null` for default). Keep translation migrations in a dedicated folder and run isolated:

```bash
php artisan migrate --database=translation_db --path=database/migrations/translations
```

## Optional `translations()` relationship

By default translations bypass Eloquent for performance. Opt in by creating a `{Model}Translation` model with `$fillable = ['locale', 'column_name', 'translation']` and `$timestamps = false`. Then `Product::with('translations')` works.

## Trait conflicts

If another trait also overrides `__get`/`__set`, alias both with `as` and orchestrate via custom `__get`/`__set` in the model — use `$this->isTranslatableColumn($key)` to route to the translations trait. Don't drop one trait.

## Best practices

- Prefer staging (`set...` + `save()`) over instant (`store...`).
- Use the `Locale` enum, not raw strings.
- Use `getWithTranslations()` for collections to avoid N+1.
- Always `resetLocale()` after `setLocale()` translation mode.
- Use `whereTranslation()` for queries by translated value.
