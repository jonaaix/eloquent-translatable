---
title: 'Core Concepts'
sidebar_label: 'Core Concepts'
sidebar_position: 2
---

Understanding these few core concepts is key to using the package effectively.

## Non-Invasive: Works with Your Existing Schema

The package is designed to be non-invasive. It does not require any changes to your main model's table schema. The existing columns (like `name` or `description`) remain exactly as they are. This main column on the model's table automatically serves as the translation for your application's fallback locale (as defined in `config/app.php`). This makes it incredibly easy to integrate the package into existing projects that already have data.

**Your Existing `products` Table:**
```text
+----+----------+
| id | name     |
+----+----------+
| 1  | "Laptop" |
+----+----------+
```

**Newly Added `product_translations` Table:**
```text
+----+----------+--------+-------------+-------------+
| id | product_id | locale | column_name | translation |
+----+----------+--------+-------------+-------------+
|    |          |        |             |             |
+----+----------+--------+-------------+-------------+
```

## Dedicated Table for Performance

All translations, except for the fallback locale, are stored in a separate, dedicated `_translations` table. This table uses a composite unique key (`model_id`, `locale`, `column_name`) to ensure data integrity and is indexed for high-performance lookups. This normalized approach is the reason for the package's superior performance and scalability compared to JSON-based solutions.

**Populated `product_translations` Table:**
```text
+----+----------+--------+-------------+-------------+
| id | product_id | locale | column_name | translation |
+----+----------+--------+-------------+-------------+
| 1  | 1        | 'de'   | 'name'      | "Notebook"  |
| 2  | 1        | 'es'   | 'name'      | "Portátil"  |
+----+----------+--------+-------------+-------------+
```

## Transparent Attribute Access

The "magic" behind the trait is simple. When you access a translatable property like `$product->name`, the trait automatically checks the current application locale. If the locale is different from the fallback locale, the trait will attempt to fetch the correct translation from the dedicated translations table. If no translation is found for the current locale, it will gracefully fall back to the value stored directly in the main `products` table's `name` column. The result is a seamless developer experience where accessing translations feels exactly like accessing a standard Eloquent attribute.

```php
// App::setLocale('de');

$product = Product::find(1);

// 1. Trait checks current locale ('de').
// 2. It's not the fallback ('en').
// 3. Looks in `product_translations` for `product_id` 1, `locale` 'de'.
// 4. Found! Returns "Notebook".
echo $product->name; // Output: "Notebook"

// App::setLocale('fr');

// 1. Trait checks current locale ('fr').
// 2. It's not the fallback ('en').
// 3. Looks in `product_translations` for `product_id` 1, `locale` 'fr'.
// 4. Not found. Falls back to the main `products` table.
// 5. Returns "Laptop".
echo $product->name; // Output: "Laptop"
```
