<?php

use Aaix\EloquentTranslatable\Tests\Models\TestModel;

use function Pest\Laravel\assertDatabaseHas;

it('stores and retrieves JSON translations correctly via storeTranslation()', function () {
   $model = TestModel::create(['name' => 'Test']);

   $model->storeTranslation('meta_keywords', 'de', ['keyword1', 'keyword2']);

   $result = $model->getTranslation('meta_keywords', 'de');

   expect($result)->toBeArray();
   expect($result)->toBe(['keyword1', 'keyword2']);
});

it('stores and retrieves JSON translations correctly via setTranslation()', function () {
   $model = TestModel::create(['name' => 'Test']);
   $model->setTranslation('meta_keywords', 'de', ['keyword1', 'keyword2']);
   $model->save();

   $result = $model->getTranslation('meta_keywords', 'de');

   expect($result)->toBeArray();
   expect($result)->toBe(['keyword1', 'keyword2']);
});

it('returns JSON translations as arrays via attribute access', function () {
   $model = TestModel::create(['name' => 'Test']);
   $model->storeTranslation('meta_keywords', 'de', ['keyword1', 'keyword2']);

   $model->setLocale('de');

   expect($model->meta_keywords)->toBeArray();
   expect($model->meta_keywords)->toBe(['keyword1', 'keyword2']);
});

it('returns null for missing JSON translations', function () {
   $model = TestModel::create(['name' => 'Test']);

   $result = $model->getTranslation('meta_keywords', 'de');

   expect($result)->toBeNull();
});
