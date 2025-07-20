---
sidebar_position: 9
---

# Eloquent Relationship (Optional)

This package is **performance-first by default**. It intentionally avoids using Eloquent models for translation operations, relying instead on optimized, direct database queries. This approach significantly reduces memory usage and execution time, especially when handling a large number of translations.

However, if you need the full power of Eloquent for your translations—for instance, to perform complex queries or use eager loading—you can create a translation model and use the `translations()` relationship.

## 1. Create the Translation Model

First, create a model for your translations. For a `Product` model, this would be `ProductTranslation`.

**`app/Models/ProductTranslation.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTranslation extends Model
{
   public $timestamps = false;
   protected $fillable = ['locale', 'column_name', 'translation'];

   public function product(): BelongsTo
   {
      return $this->belongsTo(Product::class);
   }
}
```

## 2. Use the Relationship

Once the model exists, you can use the `translations()` relationship like any other Eloquent relation.

```php
use App\Models\Product;

// Eager load all translations for a collection of products
$products = Product::with('translations')->get();

foreach ($products as $product) {
   // The translations are already loaded and won't cause extra queries
   $englishName = $product->translate('name', 'en');
}

// You can also query the relationship directly
$product = Product::find(1);
$germanTranslation = $product->translations()->where('locale', 'de')->first();
```
