---
title: 'Troubleshooting: Trait Conflicts'
sidebar_label: 'Troubleshooting'
---

# Using Multiple Traits with __get/__set

While powerful, using multiple traits that all override magic methods like `__get` or `__set` can lead to conflicts. PHP provides a clear way to solve this.

## The Problem: Fatal Error

If you use `HasTranslations` and another trait that also defines `__get` or `__set`, PHP will throw a fatal error because it doesn't know which method to use.

## The Solution: Composition in the Model

The best solution is not to choose one trait over the other, but to compose the behavior of both. This is done by renaming the methods from both traits using the `as` keyword and then creating your own `__get` or `__set` method in the model to act as a controller, calling the aliased trait methods as needed.

### Example Implementation

Here is an example of a `Product` model that uses both `HasTranslations` and a hypothetical `OtherPackage\HasSku` trait, both of which define `__get` and `__set`.

```php
<?php

namespace App\Models;

use Aaix\EloquentTranslatable\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use OtherPackage\HasSku;

class Product extends Model
{
    use HasTranslations {
        __get as getFromTranslations;
        __set as setInTranslations;
    }
    use HasSku {
        __get as getFromSkuGenerator;
        __set as setInSkuGenerator;
    }

    public array $translatable = ['name', 'description'];

    /**
     * Custom __get method to orchestrate trait methods.
     */
    public function __get($key)
    {
        // 1. Check if the key is a translatable attribute.
        if ($this->isTranslatableColumn($key)) {
            return $this->getFromTranslations($key);
        }

        // 2. Check if the key is the special 'sku' attribute.
        if ($key === 'sku') {
            return $this->getFromSkuGenerator($key);
        }

        // 3. Fallback to the default Eloquent behavior.
        return parent::__get($key);
    }

    /**
     * Custom __set method to orchestrate trait methods.
     */
    public function __set($key, $value)
    {
        // 1. Check if the key is a translatable attribute.
        if ($this->isTranslatableColumn($key)) {
            $this->setInTranslations($key, $value);
            return;
        }

        // 2. Check if the key is the special 'sku' attribute.
        if ($key === 'sku') {
            $this->setInSkuGenerator($key, $value);
            return;
        }

        // 3. Fallback to the default Eloquent behavior.
        parent::__set($key, $value);
    }
}
```