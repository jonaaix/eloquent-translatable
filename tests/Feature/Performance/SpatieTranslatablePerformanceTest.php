<?php

namespace Aaix\EloquentTranslatable\Tests\Feature\Performance;

use Aaix\EloquentTranslatable\Tests\Models\SpatieProduct;
use Illuminate\Support\Facades\DB;

class SpatieTranslatablePerformanceTest extends BasePerformanceTest
{
   public function setUp(): void
   {
      parent::setUp();
      $this->prepareDatabase();
   }

   protected function getDriverName(): string
   {
      return 'spatie/laravel-translatable';
   }

   protected function getModelClass(): string
   {
      return SpatieProduct::class;
   }

   protected function seedChunk(int $count, int $startIndex): void
   {
      $productsToInsert = [];
      $now = now();

      for ($i = $startIndex; $i < $startIndex + $count; $i++) {
         $nameTranslations = [];
         $descriptionTranslations = [];

         foreach ($this->locales as $locale) {
            $nameTranslations[$locale] = "Product {$i} name {$locale}";
            $descriptionTranslations[$locale] = "Product {$i} description {$locale}";
         }

         $productsToInsert[] = [
            'name' => json_encode($nameTranslations),
            'description' => json_encode($descriptionTranslations),
            'created_at' => $now,
            'updated_at' => $now,
         ];
      }

      foreach (array_chunk($productsToInsert, $this->chunkSize) as $chunk) {
         SpatieProduct::insert($chunk);
      }
   }

   protected function pruneChunk(int $count): void
   {
      SpatieProduct::query()->latest('id')->limit($count)->delete();
   }


   protected function getProduct(int $id): object
   {
      return SpatieProduct::find($id);
   }

   protected function getTranslatedName(object $product, string $locale): ?string
   {
      return $product->getTranslation('name', $locale);
   }

   protected function queryByName(string $name, string $locale): object
   {
      return SpatieProduct::where("name->{$locale}", $name)->first();
   }
}
