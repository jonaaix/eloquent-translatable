<?php

use Aaix\EloquentTranslatable\Tests\Models\TestModel;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('handles null and empty strings as valid translations', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'German Name')->save();

   // Set to null
   $model->setTranslation('name', 'de', null)->save();
   assertDatabaseHas('test_model_translations', ['locale' => 'de', 'translation' => null]);
   $this->assertNull($model->getTranslation('name', 'de'));

   // Set to empty string
   $model->setTranslation('name', 'de', '')->save();
   assertDatabaseHas('test_model_translations', ['locale' => 'de', 'translation' => '']);
   $this->assertEquals('', $model->getTranslation('name', 'de'));
});

it('overwrites existing translations correctly', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'Initial German Name')->save();

   // Overwrite using persistent locale mode
   $model->setLocale('de');
   $model->name = 'Overwritten German Name';
   $model->save();

   assertDatabaseHas('test_model_translations', ['translation' => 'Overwritten German Name']);
   assertDatabaseMissing('test_model_translations', ['translation' => 'Initial German Name']);
});

it('always returns the original value with getOriginal()', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model
      ->setTranslations('name', [
         'de' => 'German Name',
         'fr' => 'French Name',
      ])
      ->save();

   $model->setLocale('de');

   $this->assertEquals('Base Name', $model->getOriginal('name'));
});

it('does not save anything if model is not saved after staging', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'Unsaved German Name');

   // Model is not saved

   assertDatabaseMissing('test_model_translations', ['translation' => 'Unsaved German Name']);
});

it('handles chaotic sequence of operations correctly', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'German Name')->save(); // Initial state

   // Mix operations
   $model->setLocale('fr'); // Switch to French mode
   $model->name = 'French Name'; // Stage French translation
   $model->deleteTranslations('de'); // Delete the German one
   $model->setTranslation('name', 'es', 'Spanish Name'); // Stage Spanish translation

   $model->save();

   // Assert final state is correct
   assertDatabaseMissing('test_model_translations', ['locale' => 'de']);
   assertDatabaseHas('test_model_translations', ['locale' => 'fr', 'translation' => 'French Name']);
   assertDatabaseHas('test_model_translations', ['locale' => 'es', 'translation' => 'Spanish Name']);
   $this->assertEquals('Base Name', $model->getOriginal('name'));
});

it('uses a custom database connection if configured', function () {
   // 1. Configure a secondary in-memory SQLite database for translations
   config()->set('database.connections.testing_secondary', [
      'driver' => 'sqlite',
      'database' => ':memory:',
      'prefix' => '',
   ]);

   // 2. Point the translatable package to the new connection
   config()->set('translatable.database_connection', 'testing_secondary');

   // 3. Manually run migrations on both connections to ensure a clean state.
   $migrationPath = realpath(__DIR__ . '/../database/migrations/create_test_tables.php');

   // Migrate the default 'testing' connection
   \Illuminate\Support\Facades\Artisan::call('migrate', [
      '--database' => 'testing',
      '--path' => $migrationPath,
      '--realpath' => true,
   ]);

   // Migrate the secondary 'testing_secondary' connection
   \Illuminate\Support\Facades\Artisan::call('migrate', [
      '--database' => 'testing_secondary',
      '--path' => $migrationPath,
      '--realpath' => true,
   ]);

   // 4. Create a model and a translation
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'German Name on Secondary DB')->save();

   // 5. Assert the translation exists on the secondary connection
   assertDatabaseHas(
      'test_model_translations',
      [
         'translation' => 'German Name on Secondary DB',
      ],
      'testing_secondary',
   );

   // 6. Assert the translation does NOT exist on the default connection
   assertDatabaseMissing(
      'test_model_translations',
      [
         'translation' => 'German Name on Secondary DB',
      ],
      'testing',
   );

   // 7. Clean up config
   config()->set('translatable.database_connection', null);
});
