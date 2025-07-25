---
sidebar_position: 8
---

# Separate Translation Database

For advanced use cases, such as improving data isolation or scaling, you can configure the package to store all translations in a
separate database. This keeps your main application database clean and allows you to manage translation data independently.

## 1. Configure the Connection

First, define a new database connection in your `config/database.php` file. For this example, we'll name it `translation_db`.

```php
// config/database.php

'connections' => [

    // ... your other connections

    'translation_db' => [
        'driver' => 'mysql',
        'host' => env('TRANSLATION_DB_HOST', '127.0.0.1'),
        'port' => env('TRANSLATION_DB_PORT', '3306'),
        'database' => env('TRANSLATION_DB_DATABASE', 'forge'),
        'username' => env('TRANSLATION_DB_USERNAME', 'forge'),
        'password' => env('TRANSLATION_DB_PASSWORD', ''),
        // ... other options
    ],

],
```

Next, instruct the package to use this new connection by setting the `database_connection` option in `config/translatable.php`.

```php
// config/translatable.php

return [
    // ... other options

    'database_connection' => 'translation_db',
];
```

If this value is `null`, the application's default database connection will be used.

## 2. Isolate and Run Migrations

This is a critical step. To ensure that only translation-related migrations are run on your separate database, it is best practice to keep them in a dedicated folder.

### Recommended Workflow

1.  **Create a dedicated folder** for your translation migrations, for example, `database/migrations/translations`.

2.  **Generate the migration** for your model using the provided Artisan command.

    ```bash
    php artisan make:translation-table Product
    ```

3.  **Move the newly generated migration file** into your `database/migrations/translations` folder.

4.  **Run the migration** on the correct database using the `--database` and `--path` flags. This ensures that only the migrations in your dedicated folder are executed against the translation database.

    ```bash
    php artisan migrate --database=translation_db --path=database/migrations/translations
    ```

This approach provides a clean, safe, and organized way to manage your database schema, preventing accidental changes to your main application database.
