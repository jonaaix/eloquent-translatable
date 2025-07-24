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
      EloquentProduct::insert($productsToInsert);

      // Get the IDs of the newly inserted products
      $lastInsertedIds = EloquentProduct::query()->latest('id')->limit($count)->pluck('id');

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

   protected function queryByName(string $name, string $locale): ?object
   {
      $productData = DB::table('aaix_products')
         ->join('aaix_product_translations', 'aaix_products.id', '=', 'aaix_product_translations.aaix_product_id')
         ->where('aaix_product_translations.column_name', 'name')
         ->where('aaix_product_translations.translation', $name)
         ->where('aaix_product_translations.locale', $locale)
         ->select('aaix_products.*')
         ->first();

      if (!$productData) {
         return null;
      }

      return EloquentProduct::hydrate([$productData])[0];
   }

   protected function eagerLoadProducts(int $count): void
   {
      $products = EloquentProduct::limit($count)->getWithTranslations();
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
