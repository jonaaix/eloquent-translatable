<?php

use Aaix\EloquentTranslatable\Tests\Models\TestModel;

use function Pest\Laravel\assertDatabaseHas;

it('saves translation correctly when updating in persistent locale mode', function () {
    // This tests the critical bug where a translation was silently
    // discarded because the model was not marked as dirty.
    $model = TestModel::create(['name' => 'Base Name']);

    $model->setLocale('de');
    $model->name = 'German Name';
    $model->save();

    // Assert the base model was NOT changed.
    assertDatabaseHas('test_models', [
        'id' => 1,
        'name' => 'Base Name',
    ]);

    // Assert the German translation was created.
    assertDatabaseHas('test_model_translations', [
        'test_model_id' => 1,
        'locale' => 'de',
        'column_name' => 'name',
        'translation' => 'German Name',
    ]);
});
