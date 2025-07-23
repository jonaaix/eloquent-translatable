---
sidebar_position: 8
---

# Customization

Tailor the package to your specific needs by overriding default settings.

## Defining Translatable Attributes

For security and clarity, you must define which model attributes are translatable by adding a public `$translatable` array property to your model.

```php
// In your model
public array $translatable = ['name', 'description'];
```

For special cases, you can also configure attributes to be handled as [translatable JSON objects](./30-access-translations.md#4-accessing-json-translations). For example, by defining the following property in your model:

```php
public array $allowJsonTranslationsFor = ['options'];
```

### Allow All Attributes

If you need to make all attributes of a model translatable, you can do so by using a wildcard `*` in the `$translatable` array.

```php
// In your model
public array $translatable = ['*']; // Use with caution
```

## Custom Table and Key Names

You can override the default table and foreign key names by setting these protected properties in your model:

```php
// In your model

// The name of your custom translations table.
protected ?string $translationTable = 'my_product_translations';

// The name of the foreign key in the translations table.
protected ?string $translationForeignKey = 'my_product_id';
```

## Custom Translation Model

If you are using the optional [Eloquent Relationship](./80-eloquent-relationship.md), you can specify the fully qualified class name (FQCN) of your translation model. This is useful if your model does not follow the standard `YourModelTranslation` naming convention.

```php
// In your model

// The FQCN of the translation model.
protected ?string $translationModel = \App\Models\Translations\ProductTranslationCustom::class;
```