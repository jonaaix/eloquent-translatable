<?php

namespace Aaix\EloquentTranslatable\Tests\Feature;

use Aaix\EloquentTranslatable\Tests\Models\TestModel;
use Aaix\EloquentTranslatable\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SpatieReadModeTest extends TestCase
{
   use RefreshDatabase;

   protected TestModel $testModel;

   public function setUp(): void
   {
      parent::setUp();

      $this->testModel = new class extends TestModel {
         protected $table = 'test_models';
         protected $translationForeignKey = 'test_model_id';
         public array $translatable = ['name', 'description'];
         public array $spatieReadable = ['description'];
      };
   }

   #[Test]
   public function it_returns_a_string_for_a_normal_translatable_attribute()
   {
      $model = $this->testModel::create([
         'name' => 'Default Name',
         'description' => 'Default Description',
      ]);

      $model->setTranslation('name', 'en', 'English Name');
      $model->setTranslation('name', 'de', 'Deutscher Name');

      app()->setLocale('en');
      $this->assertSame('English Name', $model->name);

      app()->setLocale('de');
      $this->assertSame('Deutscher Name', $model->name);
   }

   #[Test]
   public function it_returns_an_array_for_a_spatie_readable_attribute()
   {
      $model = $this->testModel::create([
         'description' => 'Default Description',
      ]);

      $model->setTranslation('description', 'en', 'English Description');
      $model->setTranslation('description', 'de', 'Deutsche Beschreibung');

      $expected = [
         config('translatable.fallback_locale') => 'Default Description',
         'en' => 'English Description',
         'de' => 'Deutsche Beschreibung',
      ];

      $this->assertEquals($expected, $model->description);
   }

   #[Test]
   public function the_returned_array_includes_the_correct_fallback_value()
   {
      $model = $this->testModel::create([
         'description' => 'My Fallback',
      ]);

      $model->setTranslation('description', 'de', 'German Description');

      $expected = [
         config('translatable.fallback_locale') => 'My Fallback',
         'de' => 'German Description',
      ];

      $this->assertEquals($expected, $model->description);
   }

   #[Test]
   public function spatie_compatible_writing_is_not_affected()
   {
      $model = $this->testModel::create([
         'name' => 'Initial Name',
         'description' => 'Initial Description',
      ]);

      $model->description = [
         'en' => 'New English',
         'de' => 'Neue Deutsche',
      ];
      $model->save();

      $expected = [
         config('translatable.fallback_locale') => 'Initial Description',
         'en' => 'New English',
         'de' => 'Neue Deutsche',
      ];
      $this->assertEquals($expected, $model->description);
   }
}
