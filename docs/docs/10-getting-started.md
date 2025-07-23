---
sidebar_position: 1
---

# Getting Started

This guide will walk you through the installation and initial setup of the Eloquent Translatable package.

## Installation

1. Require the package using Composer.

   ```bash
   composer require aaix/eloquent-translatable
   ```

2. Publish the configuration file (optional).

   ```bash
   php artisan vendor:publish --provider="Aaix\EloquentTranslatable\TranslatableServiceProvider" --tag="translatable-config"
   ```

   This will create a `config/translatable.php` file where you can set a global fallback locale.

## Setup

### 1. Create the Translations Table

For each model you want to make translatable (e.g., `Product`), run the provided Artisan command. It will generate the necessary
migration file.

```bash
php artisan make:translation-table Product
```

Then, run the migration:

```bash
php artisan migrate
```

### 2. Prepare Your Model

Add the `HasTranslations` trait to your model and define which attributes are translatable in the `$translatable` array.

```php
<?php

namespace App\Models;

use Aaix\EloquentTranslatable\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
   use HasTranslations;

   public array $translatable = ['name', 'description'];
}
```
