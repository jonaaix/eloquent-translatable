<?php

namespace Aaix\EloquentTranslatable\Traits\Internal;

use Aaix\EloquentTranslatable\Enums\Locale;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
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
               $foreignKey => $modelId,
               'locale' => $locale,
               'column_name' => $key,
               'translation' => $value,
            ];
         }
      }

      if (empty($allStaged)) {
         return;
      }

      $this->translationQuery()->upsert($allStaged, [$foreignKey, 'locale', 'column_name'], ['translation']);

      // Clear staged translations and internal caches directly without a refresh.
      // The cache will be repopulated on the next read request if needed.
      $this->stagedTranslations = [];
      $this->structuredTranslations = null;
      $this->unsetRelation('translations');
   }

   /**
    * Ensure translations are loaded, either from an existing Eloquent relation or via a raw query.
    * This populates the structured cache for fast lookups.
    */
   protected function ensureTranslationsAreLoaded(): void
   {
      // If the fast cache is already populated, we're done.
      if ($this->structuredTranslations !== null) {
         return;
      }

      // If the developer has already eager-loaded the Eloquent relationship,
      // use that as the source of truth to build our cache, avoiding a new query.
      if ($this->relationLoaded('translations')) {
         $this->structureLoadedTranslations();
         return;
      }

      // If the model doesn't exist, initialize an empty cache.
      if (!$this->exists) {
         $this->structuredTranslations = [];
         return;
      }

      // As a last resort, fetch translations via a fast, raw query.
      $translations = $this->translationQuery()
         ->where($this->getTranslationForeignKey(), $this->getKey())
         ->get(['locale', 'column_name', 'translation']);

      $structured = [];
      foreach ($translations as $translation) {
         $structured[$translation->column_name][$translation->locale] = $translation->translation;
      }
      $this->structuredTranslations = $structured;
   }

   /**
    * Eager-loads translations for a collection using a raw query for maximum performance.
    */
   public static function loadTranslationsForCollection(EloquentCollection $collection): void
   {
      if ($collection->isEmpty()) {
         return;
      }

      $firstModel = $collection->first();

      // Respect if the developer already eager-loaded the 'translations' relation.
      if ($firstModel->relationLoaded('translations')) {
         $collection->each(function ($model) {
            if ($model->relationLoaded('translations')) {
               $model->structureLoadedTranslations();
            }
         });
         return;
      }

      $foreignKey = $firstModel->getTranslationForeignKey();
      $modelIds = $collection->pluck($firstModel->getKeyName())->all();

      $allTranslations = DB::connection($firstModel->getTranslationConnectionName())
         ->table($firstModel->getTranslationsTableName())
         ->whereIn($foreignKey, $modelIds)
         ->get(['locale', 'column_name', 'translation', $foreignKey]);

      $groupedTranslations = $allTranslations->groupBy($foreignKey);

      $collection->each(function ($model) use ($groupedTranslations, $foreignKey) {
         $modelTranslations = $groupedTranslations->get($model->getKey());
         $structured = [];

         if ($modelTranslations) {
            foreach ($modelTranslations as $translation) {
               $structured[$translation->column_name][$translation->locale] = $translation->translation;
            }
         }

         // Manually set the structured cache and mark the Eloquent relation as loaded (with an empty collection)
         // to prevent any N+1 issues if the relation is accessed later.
         $model->structuredTranslations = $structured;
         $model->setRelation('translations', new \Illuminate\Database\Eloquent\Collection());
      });
   }

   /**
    * Persists a single translation directly to the database using an efficient upsert operation.
    */
   private function persistTranslation(string $key, string|Locale $locale, ?string $value): void
   {
      $localeValue = $locale instanceof Locale ? $locale->value : $locale;
      $foreignKey = $this->getTranslationForeignKey();

      $this->translationQuery()->upsert(
         [
            [
               $foreignKey => $this->getKey(),
               'locale' => $localeValue,
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

      $translation = $this->translations->where('column_name', $key)->where('locale', $locale)->first();

      if ($translation) {
         $translation->translation = $value;
      } else {
         $modelClass = $this->getTranslationModelName();
         if (class_exists($modelClass)) {
            $newTranslation = new $modelClass([
               'locale' => $locale,
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
      $query = $this->translationQuery()->where($this->getTranslationForeignKey(), $this->getKey());
      if ($locales) {
         $query->whereIn('locale', $locales);
      }
      $query->delete();
      $this->refreshTranslations();
   }

   /**
    * Get the database connection name for translations.
    */
   protected function getTranslationConnectionName(): ?string
   {
      return config('translatable.database_connection');
   }

   /**
    * Get a query builder instance for the translations table.
    */
   protected function translationQuery(): Builder
   {
      return DB::connection($this->getTranslationConnectionName())->table($this->getTranslationsTableName());
   }

   /**
    * Fetches and returns a single translation value directly from the database.
    */
   protected function fetchSingleTranslation(string $column, string $locale): ?string
   {
      return $this->translationQuery()
         ->where($this->getTranslationForeignKey(), $this->getKey())
         ->where('column_name', $column)
         ->where('locale', $locale)
         ->value('translation');
   }
}
