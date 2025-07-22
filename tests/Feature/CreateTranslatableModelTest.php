<?php

use Aaix\EloquentTranslatable\Tests\Models\TestModel;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('saves base attribute correctly when creating a model', function () {
   $model = new TestModel();
   $model->name = 'Base Name';
   $model->save();

   assertDatabaseHas('test_models', [
      'id' => 1,
      'name' => 'Base Name',
   ]);
});

it('handles creation correctly when in persistent locale mode to prevent data misdirection', function () {
   // This tests the critical bug where a base attribute was incorrectly
   // saved as a translation, and the base attribute itself was set to null.
   $model = new TestModel();
   $model->name = 'Base Name';
   $model->setLocale('de'); // Activate persistent locale mode
   $model->save();

   // Assert the base model was saved correctly.
   assertDatabaseHas('test_models', [
      'id' => 1,
      'name' => 'Base Name',
   ]);

   // Assert NO translation was created.
   assertDatabaseMissing('test_model_translations', [
      'test_model_id' => 1,
   ]);
});
