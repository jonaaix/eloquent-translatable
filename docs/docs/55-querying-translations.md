---
sidebar_position: 55
---

# Querying By Translated Value

You can easily query for models by a specific translated attribute value using the `whereTranslation` scope. This is particularly useful for finding records based on localized content, for example, when implementing a search feature.

The scope adds a performant `JOIN` to the query and ensures that only results matching the translation in the specified locale are returned.

### Basic Usage

To find all models where a translation matches a specific value, provide the column name, the value, and the locale.

```php
use App\Models\Product;

// Find all products where the German name is "Mein tolles Produkt"
$products = Product::whereTranslation('name', 'Mein tolles Produkt', 'de')->get();
```
