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

it('gets all translations as an array', function () {
    $model = TestModel::create(['name' => 'Base Name']);
    $model->setTranslation('name', 'de', 'German Name');
    $model->setTranslation('name', 'fr', 'French Name');

    $expected = [
        'name' => [
            config('translatable.fallback_locale') => 'Base Name',
            'de' => 'German Name',
            'fr' => 'French Name',
        ],
    ];

    $this->assertEquals($expected, $model->getTranslationsAsArray());
});

it('gets translations for a single attribute', function () {
    $model = TestModel::create(['name' => 'Base Name']);
    $model->setTranslation('name', 'de', 'German Name');

    $expected = [
        config('translatable.fallback_locale') => 'Base Name',
        'de' => 'German Name',
    ];

    $this->assertEquals($expected, $model->getTranslationsAsArray('name'));
});

it('gets translations for multiple attributes', function () {
    $model = new class extends TestModel {
        protected $table = 'test_models';
        public array $translatable = ['name', 'description'];
    };

    $instance = $model->create([
        'name' => 'Base Name',
        'description' => 'Base Description',
    ]);

    $instance->setTranslation('name', 'de', 'German Name');
    $instance->setTranslation('description', 'de', 'German Description');

    $expected = [
        'name' => [
            config('translatable.fallback_locale') => 'Base Name',
            'de' => 'German Name',
        ],
        'description' => [
            config('translatable.fallback_locale') => 'Base Description',
            'de' => 'German Description',
        ],
    ];

    $this->assertEquals($expected, $instance->getTranslationsAsArray(['name', 'description']));
});

it('returns an empty array for a non-translatable attribute', function () {
    $model = TestModel::create(['name' => 'Base Name']);
    $this->assertEquals([], $model->getTranslationsAsArray('non_translatable_attribute'));
});