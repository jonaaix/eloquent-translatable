<?php

use Aaix\EloquentTranslatable\Tests\Models\TestModel;

use function Pest\Laravel\assertDatabaseHas;

it('accesses translation via property based on app locale', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'German Name');
   $model->save();

   app()->setLocale('de');

   $this->assertEquals('German Name', $model->name);
});

it('accesses translation via getTranslation() method', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'German Name');
   $model->save();

   $this->assertEquals('German Name', $model->getTranslation('name', 'de'));
});

it('accesses translation via inLocale() helper', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'German Name');
   $model->save();

   $this->assertEquals('German Name', $model->inLocale('de')->name);
});

it('accesses translation via persistent locale mode', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'German Name');
   $model->save();

   $model->setLocale('de');

   $this->assertEquals('German Name', $model->name);

   $model->resetLocale();

   $this->assertEquals('Base Name', $model->name);
});

it('gets all translations when getTranslations is called without arguments', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'German Name');

   $expected = [
      'name' => [
         'en' => 'Base Name',
         'de' => 'German Name',
      ],
   ];

   $this->assertEquals($expected, $model->getTranslations());
});

it('gets all translations for a single attribute', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslation('name', 'de', 'German Name');

   $expected = [
      'en' => 'Base Name',
      'de' => 'German Name',
   ];

   $this->assertEquals($expected, $model->getTranslations('name'));
});

it('gets filtered translations for a single attribute', function () {
   $model = TestModel::create(['name' => 'Base Name']);
   $model->setTranslations('name', [
      'de' => 'German Name',
      'fr' => 'French Name',
   ]);

   $expected = [
      'en' => 'Base Name',
      'fr' => 'French Name',
   ];

   $this->assertEquals($expected, $model->getTranslations('name', ['en', 'fr']));
});
