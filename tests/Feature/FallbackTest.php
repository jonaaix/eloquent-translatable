<?php

use Aaix\EloquentTranslatable\Tests\Models\TestModel;

it('returns original attribute when no translations exist', function () {
    $model = TestModel::create(['name' => 'Base Name']);
    $this->assertEquals('Base Name', $model->name);
});

it('prioritizes persistent locale over app locale and config fallback', function () {
    $model = TestModel::create(['name' => 'Base Name']);
    $model->setTranslations('name', [
        'de' => 'German Name',
        'fr' => 'French Name',
    ])->save();

    config(['translatable.fallback_locale' => 'fr']);
    app()->setLocale('fr');
    $model->setLocale('de');

    $this->assertEquals('German Name', $model->name);
});

it('prioritizes app locale over config fallback', function () {
    $model = TestModel::create(['name' => 'Base Name']);
    $model->setTranslations('name', [
        'de' => 'German Name',
        'fr' => 'French Name',
    ])->save();

    config(['translatable.fallback_locale' => 'de']);
    app()->setLocale('fr');

    $this->assertEquals('French Name', $model->name);
});

it('uses config fallback when app locale translation is missing', function () {
    $model = TestModel::create(['name' => 'Base Name']);
    $model->setTranslations('name', [
        'de' => 'German Name',
    ])->save();

    config(['translatable.fallback_locale' => 'de']);
    app()->setLocale('fr'); // French translation does not exist

    $this->assertEquals('German Name', $model->name);
});

it('falls back to original attribute when no other translation is available', function () {
    $model = TestModel::create(['name' => 'Base Name']);
    $model->setTranslation('name', 'de', 'German Name')->save();

    config(['translatable.fallback_locale' => 'es']); // Spanish does not exist
    app()->setLocale('fr'); // French does not exist

    $this->assertEquals('Base Name', $model->name);
});