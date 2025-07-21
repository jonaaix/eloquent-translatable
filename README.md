<p align="center">
  <a href="https://github.com/jonaaix/eloquent-translatable">
    <img src="https://raw.githubusercontent.com/jonaaix/eloquent-translatable/main/docs/static/img/logo2.png" alt="Laravel Eloquent Translatable Logo" width="200">
  </a>
</p>

<h1 align="center">Laravel Eloquent Translatable</h1>

<p align="center">
High performance, developer-first translations for Laravel models.
</p>

<p align="center">
  <a href="https://packagist.org/packages/aaix/eloquent-translatable"><img src="https://img.shields.io/packagist/v/aaix/eloquent-translatable.svg?style=flat-square" alt="Latest Version on Packagist"></a>
  <a href="https://packagist.org/packages/aaix/eloquent-translatable"><img src="https://img.shields.io/packagist/dt/aaix/eloquent-translatable.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="https://github.com/jonaaix/eloquent-translatable/actions/workflows/tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/jonaaix/eloquent-translatable/tests.yml?branch=main&label=tests&style=flat-square" alt="GitHub Actions"></a>
  <a href="https://github.com/jonaaix/eloquent-translatable/blob/main/LICENSE.md"><img src="https://img.shields.io/packagist/l/aaix/eloquent-translatable.svg?style=flat-square" alt="License"></a>
</p>

---

**Eloquent Translatable** is a Laravel package built for raw performance and a clean, focused developer experience. It uses direct, indexed database queries instead of relying on JSON columns or complex Eloquent model hydration, making it significantly faster and more memory-efficient than other solutions.

## Key Features

- **🚀 Performance-First:** Designed for speed at scale. No Eloquent overhead, no JSON parsing.
- **✨ Intuitive API:** A clean, fluent, and predictable interface.
- **🔒 Secure by Default:** Explicitly define which attributes are translatable.
- **⚙️ Artisan Command:** Scaffold translation migrations with a single command.
- **🛡️ Enum-Powered:** Ships with a `Locale` enum for type-safe, readable code.

## Documentation

For the full documentation, please visit our **[documentation website](https://jonaaix.github.io/eloquent-translatable/docs/getting-started)**.

## Installation

You can install the package via Composer:

```bash
composer require aaix/eloquent-translatable
```

## Quick Example

1.  **Prepare your model:**

    ```php
    // app/Models/Product.php
    namespace App\Models;

    use Aaix\EloquentTranslatable\Traits\HasTranslations;
    use Illuminate\Database\Eloquent\Model;

    class Product extends Model
    {
        use HasTranslations;

        public array $translatable = ['name', 'description'];
    }
    ```

2.  **Store and access translations:**

    ```php
    use Aaix\EloquentTranslatable\Enums\Locale;

    $product = Product::create(['name' => 'My awesome product']);

    // Store a translation
    $product->storeTranslation('name', Locale::GERMAN, 'Mein tolles Produkt');

    // Access it (will fall back to the app's locale)
    app()->setLocale('de');
    echo $product->name; // Output: Mein tolles Produkt

    // Or get a specific locale
    echo $product->getTranslation('name', Locale::GERMAN); // Output: Mein tolles Produkt
    ```

## Testing

To run the package's test suite, clone the repository and run:

```bash
composer install
composer test
```

## Contributing

Contributions are welcome! Please see the [contributing guide](https://github.com/jonaaix/eloquent-translatable/blob/main/.github/CONTRIBUTING.md) for more details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.