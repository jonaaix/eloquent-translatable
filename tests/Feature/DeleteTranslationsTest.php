<?php

use Aaix\EloquentTranslatable\Tests\Models\TestModel;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('deletes a single locale translation', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslations('name', ['de' => 'German Name', 'fr' => 'French Name'])->save();

   $model->deleteTranslations('de');

   assertDatabaseMissing('test_model_translations', ['locale' => 'de']);
   assertDatabaseHas('test_model_translations', ['locale' => 'fr']);
});

it('deletes multiple locale translations', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslations('name', ['de' => 'German Name', 'fr' => 'French Name', 'es' => 'Spanish Name'])->save();

   $model->deleteTranslations(['de', 'fr']);

   assertDatabaseMissing('test_model_translations', ['locale' => 'de']);
   assertDatabaseMissing('test_model_translations', ['locale' => 'fr']);
   assertDatabaseHas('test_model_translations', ['locale' => 'es']);
});

it('deletes all translations', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslations('name', ['de' => 'German Name', 'fr' => 'French Name'])->save();

   $model->deleteTranslations();

   assertDatabaseMissing('test_model_translations', ['test_model_id' => 1]);
});
