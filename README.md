## Eloquent Translatable

A clean, performant, and developer-friendly trait for Laravel Eloquent models that require translation capabilities. This package is designed for simplicity and performance, using a dedicated translation table for each model.

## Features

- **Clean & Simple API:** An intuitive and fluent interface.
- **High Performance:** Uses direct database queries and avoids Eloquent overhead for translations.
- **Flexible:** Works with or without a dedicated translation model.
- **Secure by Default:** Requires explicit definition of translatable attributes.
- **Convenient:** Ships with a helpful Artisan command and a comprehensive `Locale` Enum.

### Why another translation package?

Frustrated by the complexity and poor implementation of existing solutions, this package was created with a focus on performance and a clean, predictable API. It avoids unnecessary overhead and provides a straightforward developer experience.

## Installation

Require the package using Composer:

```bash
composer require aaix/eloquent-translatable
```

## Documentation

For full usage instructions, including setup, basic and advanced usage, and customization options, please see our [**full documentation**](https://your-docusaurus-site.com/docs/getting-started).

A minimal usage example:

```php
use Aaix\EloquentTranslatable\Enums\Locale;

$product = Product::find(1);

echo $product->name; // Outputs: "Example Product"

// Set a translation
$product->forLocale(Locale::GERMAN, function ($product) {
   $product->name = 'Beispiel Produkt';
});

// Get a translation
echo $product->getTranslated('name', Locale::GERMAN); // Outputs: "Beispiel Produkt"
```
