<?php

namespace Aaix\EloquentTranslatable\Tests\Feature\Performance;

use Aaix\EloquentTranslatable\Tests\Models\AstrotomicProduct;
use Aaix\EloquentTranslatable\Tests\Models\AstrotomicProductTranslation;

class AstrotomicTranslatablePerformanceTest extends BasePerformanceTest
{
   public function setUp(): void
   {
      parent::setUp();
      $this->prepareDatabase();
   }

   protected function getDriverName(): string
   {
      return 'astrotomic/laravel-translatable';
   }

   protected function getModelClass(): string
   {
      return AstrotomicProduct::class;
   }

   protected function seedChunk(int $count, int $startIndex): void
   {
      $products = [];
      $now = now();
      for ($i = $startIndex; $i < $startIndex + $count; $i++) {
         $products[] = [
            'created_at' => $now,
            'updated_at' => $now,
         ];
      }
      AstrotomicProduct::insert($products);

      // Get last inserted IDs to build translations
      $lastInsertedProducts = AstrotomicProduct::query()->latest('id')->limit($count)->get();

      $allTranslations = [];
      foreach ($lastInsertedProducts as $product) {
         foreach ($this->locales as $locale) {
            $allTranslations[] = [
               'astrotomic_product_id' => $product->id,
               'locale' => $locale,
               'name' => "Product {$product->id} name {$locale}",
               'description' => "Product {$product->id} description {$locale}",
            ];
         }
      }

      foreach (array_chunk($allTranslations, $this->chunkSize) as $chunk) {
         AstrotomicProductTranslation::insert($chunk);
      }
   }

   protected function pruneChunk(int $count): void
   {
      AstrotomicProduct::query()->latest('id')->limit($count)->delete();
   }

   protected function getProduct(int $id): object
   {
      return AstrotomicProduct::find($id);
   }

   protected function getTranslatedName(object $product, string $locale): ?string
   {
      return $product->translate($locale)->name;
   }

   protected function queryByName(string $name, string $locale): object
   {
      return AstrotomicProduct::whereTranslation('name', $name, $locale)->first();
   }
}
