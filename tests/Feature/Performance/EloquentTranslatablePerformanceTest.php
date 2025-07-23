<?php

namespace Aaix\EloquentTranslatable\Tests\Feature\Performance;

use Aaix\EloquentTranslatable\Tests\Models\EloquentProduct;
use Illuminate\Support\Facades\DB;

class EloquentTranslatablePerformanceTest extends BasePerformanceTest
{
   public function setUp(): void
   {
      parent::setUp();
      $this->prepareDatabase();
   }

   protected function getDriverName(): string
   {
      return 'aaix/eloquent-translatable';
   }

   protected function getModelClass(): string
   {
      return EloquentProduct::class;
   }

   protected function seedChunk(int $count, int $startIndex): void
   {
      $products = [];
      for ($i = $startIndex; $i < $startIndex + $count; $i++) {
         $products[] = EloquentProduct::create([
            'name' => "Product {$i} name en",
            'description' => "Product {$i} description en",
         ]);
      }

      $allTranslations = [];
      foreach ($products as $product) {
         foreach ($this->locales as $locale) {
            $allTranslations[] = [
               'aaix_product_id' => $product->id,
               'locale' => $locale,
               'column_name' => 'name',
               'translation' => "Product {$product->id} name {$locale}",
            ];
            $allTranslations[] = [
               'aaix_product_id' => $product->id,
               'locale' => $locale,
               'column_name' => 'description',
               'translation' => "Product {$product->id} description {$locale}",
            ];
         }
      }

      foreach (array_chunk($allTranslations, $this->chunkSize) as $chunk) {
         DB::table('aaix_product_translations')->insert($chunk);
      }
   }

   protected function pruneChunk(int $count): void
   {
      EloquentProduct::query()->latest('id')->limit($count)->delete();
   }

   protected function getProduct(int $id): object
   {
      return EloquentProduct::find($id);
   }

   protected function getTranslatedName(object $product, string $locale): ?string
   {
      return $product->getTranslation('name', $locale);
   }

   protected function queryByName(string $name, string $locale): object
   {
      return EloquentProduct::whereHas('translations', function ($query) use ($name, $locale) {
         $query->where('column_name', 'name')
            ->where('translation', $name)
            ->where('locale', $locale);
      })->first();
   }

   protected function eagerLoadProducts(int $count): void
   {
      $products = EloquentProduct::with('translations')->limit($count)->get();
      foreach ($products as $product) {
         $this->assertNotNull($product->getTranslation('name', 'de'));
      }
   }

   protected function createWithOneTranslation(): void
   {
      $product = EloquentProduct::create([
         'name' => 'Test',
         'description' => 'Test Description',
      ]);
      $product->storeTranslation('name', 'de', 'Test DE');
      $product->delete();
   }

   protected function createWithAllTranslations(): void
   {
      $product = EloquentProduct::create([
         'name' => 'Test',
         'description' => 'Test Description',
      ]);
      $translations = [];
      foreach ($this->locales as $locale) {
         $translations[$locale] = "Test {$locale}";
      }
      $product->storeTranslations('name', $translations);
      $product->delete();
   }

   protected function updateOneTranslation(): void
   {
      $product = EloquentProduct::find(1);
      $product->storeTranslation('name', 'de', 'Updated Test DE');
   }
}
