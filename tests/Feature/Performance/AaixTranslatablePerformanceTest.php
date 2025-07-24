<?php

namespace Aaix\EloquentTranslatable\Tests\Feature\Performance;

use Aaix\EloquentTranslatable\Tests\Models\AaixProduct;
use Illuminate\Support\Facades\DB;

class AaixTranslatablePerformanceTest extends BasePerformanceTest
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
      return AaixProduct::class;
   }

   protected function seedChunk(int $count, int $startIndex): void
   {
      $productsToInsert = [];
      $now = now();
      for ($i = $startIndex; $i < $startIndex + $count; $i++) {
         $productsToInsert[] = [
            'name' => "Product {$i} name en",
            'description' => $this->getFaker()->paragraphs(5, true),
            'created_at' => $now,
            'updated_at' => $now,
         ];
      }
      // Bulk insert products
      AaixProduct::insert($productsToInsert);

      // Get the IDs of the newly inserted products
      $lastInsertedIds = AaixProduct::query()->latest('id')->limit($count)->pluck('id');

      $allTranslations = [];
      foreach ($lastInsertedIds as $productId) {
         foreach ($this->locales as $locale) {
            $allTranslations[] = [
               'aaix_product_id' => $productId,
               'locale' => $locale,
               'column_name' => 'name',
               'translation' => "Product {$productId} name {$locale}",
            ];
            $allTranslations[] = [
               'aaix_product_id' => $productId,
               'locale' => $locale,
               'column_name' => 'description',
               'translation' => $this->getFaker()->paragraphs(5, true),
            ];
         }
      }

      // Bulk insert all translations in chunks
      foreach (array_chunk($allTranslations, $this->chunkSize) as $chunk) {
         DB::table('aaix_product_translations')->insert($chunk);
      }
   }

   protected function pruneChunk(int $count): void
   {
      AaixProduct::query()->latest('id')->limit($count)->delete();
   }

   protected function getProduct(int $id): object
   {
      return AaixProduct::find($id);
   }

   protected function getTranslatedName(object $product, string $locale): ?string
   {
      return $product->getTranslation('name', $locale);
   }

   protected function queryByName(string $name, string $locale): ?object
   {
      return AaixProduct::whereTranslation('name', $name, $locale)->first();
   }

   protected function eagerLoadProducts(int $count): void
   {
      $products = AaixProduct::limit($count)->getWithTranslations();
      foreach ($products as $product) {
         $this->assertNotNull($product->getTranslation('name', 'de'));
      }
   }

   protected function createWithOneTranslation(): void
   {
      $product = AaixProduct::create([
         'name' => 'Test',
         'description' => 'Test Description',
      ]);

      // Revert to storeTranslation to test the newly optimized single-write path
      $product->storeTranslation('name', 'de', 'Test DE');

      $product->delete();
   }

   protected function createWithAllTranslations(): void
   {
      $product = AaixProduct::create([
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
      $product = AaixProduct::find(1);
      $product->storeTranslation('name', 'de', 'Updated Test DE');
   }
}
