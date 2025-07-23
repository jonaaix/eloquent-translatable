<?php

use Aaix\EloquentTranslatable\Tests\Models\TestModel;

use function Pest\Laravel\assertDatabaseHas;

it('stages and saves a single translation via setTranslation()', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'German Name');
   $model->save();

   assertDatabaseHas('test_model_translations', [
      'test_model_id' => 1,
      'locale' => 'de',
      'translation' => 'German Name',
   ]);
});

it('stages and saves multiple translations via setTranslations()', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslations('name', [
      'de' => 'German Name',
      'fr' => 'French Name',
   ]);
   $model->save();

   assertDatabaseHas('test_model_translations', ['locale' => 'de', 'translation' => 'German Name']);
   assertDatabaseHas('test_model_translations', ['locale' => 'fr', 'translation' => 'French Name']);
});

it('stores a single translation instantly via storeTranslation()', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->storeTranslation('name', 'de', 'German Name');

   assertDatabaseHas('test_model_translations', [
      'test_model_id' => 1,
      'locale' => 'de',
      'translation' => 'German Name',
   ]);
});

it('stores multiple translations instantly via storeTranslations()', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->storeTranslations('name', [
      'de' => 'German Name',
      'fr' => 'French Name',
   ]);

   assertDatabaseHas('test_model_translations', ['locale' => 'de', 'translation' => 'German Name']);
   assertDatabaseHas('test_model_translations', ['locale' => 'fr', 'translation' => 'French Name']);
});

it('stages translations via multi-locale array assignment (Spatie-style)', function () {
   $model = new TestModel();

   $model->name = [
      'de' => 'German Name',
      'fr' => 'French Name',
   ];

   $model->save();

   assertDatabaseHas('test_model_translations', ['locale' => 'de', 'translation' => 'German Name']);
   assertDatabaseHas('test_model_translations', ['locale' => 'fr', 'translation' => 'French Name']);
});

it('prioritizes multi-locale array assignment over active translation mode', function () {
   $model = new TestModel();

   // Put the model into a persistent locale mode.
   $model->setLocale('es');

   // Assign a multi-locale array. This should take precedence over the Spanish locale mode.
   $model->name = [
      'de' => 'German Name',
      'fr' => 'French Name',
   ];

   $model->save();

   // Assert that the array's locales were saved.
   assertDatabaseHas('test_model_translations', ['locale' => 'de', 'translation' => 'German Name']);
   assertDatabaseHas('test_model_translations', ['locale' => 'fr', 'translation' => 'French Name']);

   // Assert that the persistent locale was ignored for this assignment.
   $this->assertDatabaseMissing('test_model_translations', ['locale' => 'es']);
});
