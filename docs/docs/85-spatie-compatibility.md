---
sidebar_position: 85
title: Spatie Read Mode
---

# Spatie Read Mode Compatibility

While `aaix/eloquent-translatable` is designed with a performance-first architecture that differs significantly from `spatie/laravel-translatable`, we provide an optional compatibility layer for specific use cases, particularly for frontend components that expect Spatie's data structure.

## The Problem: UI Component Compatibility

Some UI packages, like `filament-translatable-tabs`, are built to work with `spatie/laravel-translatable`. They expect that accessing a translated attribute (e.g., `$model->name`) will return a JSON array of all translations, like `{'en': 'Name', 'de': 'Name'}`.

By default, `aaix/eloquent-translatable` returns a `string` for the current locale, which is more performant but incompatible with these components.

## The Solution: `spatieReadable` Property

To bridge this gap, you can use the `spatieReadable` property on your model. When you list a translatable attribute in this array, its read behavior changes to match Spatie's.

-   **Default Behavior**: `$model->name` returns a `string`.
-   **Spatie Read Mode**: If `name` is in `$spatieReadable`, `$model->name` returns an `array` of all translations.

### Example Implementation

```php
use Illuminate\Database\Eloquent\Model;
use Aaix\EloquentTranslatable\Traits\HasTranslations;

class Product extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    // Activate Spatie Read Mode for the 'description' attribute
    public array $spatieReadable = ['description'];

    protected $guarded = [];
}
```

Now, accessing these attributes will yield different results:

```php
$product = Product::first();

// Standard string access
echo $product->name; // Output: "My Product Name"

// Spatie-compatible array access
print_r($product->description);
// Output: ['en' => 'My English Description', 'de' => 'Meine deutsche Beschreibung']
```

### Important Trade-Offs

-   **Performance**: Enabling this mode for an attribute means all its translations are loaded from the database upon access. This is less performant than the default behavior but necessary for compatibility.
-   **Read-Only Change**: This feature only affects **reading** attributes. The Spatie-compatible **writing** behavior (`$model->name = ['en' => '...']`) remains unaffected and works for all translatable attributes.
