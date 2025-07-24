<?php

namespace Aaix\EloquentTranslatable\Traits\Internal;

use Aaix\EloquentTranslatable\Enums\Locale;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

trait ManagesPersistence
{
   /**
    * Persists all staged translations to the database.
    */
   public function persistStagedTranslations(): void
   {
      if (empty($this->stagedTranslations)) {
         return;
      }

      $foreignKey = $this->getTranslationForeignKey();
      $modelId = $this->getKey();
      $allStaged = [];

      foreach ($this->stagedTranslations as $locale => $translationData) {
         foreach ($translationData as $key => $value) {
            $allStaged[] = [
               $foreignKey    => $modelId,
               'locale'      => $locale,
               'column_name' => $key,
               'translation' => $value,
            ];
         }
      }

      if (empty($allStaged)) {
         return;
      }

      // A single upsert operation is significantly more performant than selecting and then partitioning.
      DB::table($this->getTranslationsTableName())->upsert(
         $allStaged,
         [$foreignKey, 'locale', 'column_name'],
         ['translation'],
      );

      $this->stagedTranslations = [];
      $this->refreshTranslations();
   }

   /**
    * Loads all translations for this model instance from the database once.
    */
   protected function loadTranslationsOnce(): void
   {
      if ($this->relationLoaded('translations')) {
         return;
      }

      if (!$this->exists) {
         $this->setRelation('translations', new \Illuminate\Database\Eloquent\Collection());
         return;
      }

      // Use the relationship to load translations, then structure them for fast access.
      $this->load('translations');
      $this->structureLoadedTranslations();
   }

   /**
    * Eager-loads translations for a collection of models.
    */
   public static function loadTranslationsForCollection(EloquentCollection $collection): void
   {
      if ($collection->isEmpty()) {
         return;
      }

      $firstModel = $collection->first();
      $foreignKey = $firstModel->getTranslationForeignKey();
      $modelIds = $collection->pluck($firstModel->getKeyName())->all();

      $translations = $firstModel->translations()
         ->getModel()
         ->newQuery()
         ->whereIn($foreignKey, $modelIds)
         ->get()
         ->groupBy($foreignKey);

      // Associate the loaded translations and structure them on each model.
      $collection->each(function ($model) use ($translations, $foreignKey) {
         $modelTranslations = $translations->get($model->getKey(), new \Illuminate\Database\Eloquent\Collection());
         $model->setRelation('translations', $modelTranslations);
         $model->structureLoadedTranslations();
      });
   }

   /**
    * Persists a single translation directly to the database using an efficient upsert operation.
    */
   private function persistTranslation(string $key, string|Locale $locale, ?string $value): void
   {
      $localeValue = $locale instanceof Locale ? $locale->value : $locale;
      $foreignKey = $this->getTranslationForeignKey();

      DB::table($this->getTranslationsTableName())->upsert(
         [
            [
               $foreignKey    => $this->getKey(),
               'locale'      => $localeValue,
               'column_name' => $key,
               'translation' => $value,
            ],
         ],
         [$foreignKey, 'locale', 'column_name'],
         ['translation'],
      );

      $this->updateLoadedTranslation($key, $localeValue, $value);
   }


   /**
    * Updates or adds a single translation to the in-memory cache without triggering a db read.
    */
   protected function updateLoadedTranslation(string $key, string $locale, ?string $value): void
   {
      // If the cache has never been loaded, just initialize it and add the new value.
      // This avoids a costly database read-back after an instant save.
      if ($this->structuredTranslations === null) {
         $this->structuredTranslations = [];
      }
      $this->structuredTranslations[$key][$locale] = $value;

      // If the full Eloquent relationship happens to be loaded, keep it in sync too for consistency.
      if (!$this->relationLoaded('translations')) {
         return;
      }

      $translation = $this->translations
         ->where('column_name', $key)
         ->where('locale', $locale)
         ->first();

      if ($translation) {
         $translation->translation = $value;
      } else {
         $modelClass = $this->getTranslationModelName();
         if (class_exists($modelClass)) {
            $newTranslation = new $modelClass([
               'locale'      => $locale,
               'column_name' => $key,
               'translation' => $value,
            ]);
            $this->translations->push($newTranslation);
         }
      }
   }

   /**
    * Deletes translations for the current model instance.
    */
   public function deleteTranslations(string|array|null $locales = null): void
   {
      if ($locales) {
         $locales = array_map(static function ($locale) {
            return $locale instanceof Locale ? $locale->value : $locale;
         }, (array) $locales);
      }
      $query = DB::table($this->getTranslationsTableName())->where($this->getTranslationForeignKey(), $this->getKey());
      if ($locales) {
         $query->whereIn('locale', $locales);
      }
      $query->delete();
      $this->refreshTranslations();
   }
}
