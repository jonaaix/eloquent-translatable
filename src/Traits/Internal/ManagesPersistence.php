<?php

namespace Aaix\EloquentTranslatable\Traits\Internal;

use Aaix\EloquentTranslatable\Enums\Locale;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
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

      $translations = $this->stagedTranslations;
      $this->stagedTranslations = [];
      foreach ($translations as $locale => $translationData) {
         foreach ($translationData as $key => $value) {
            $this->persistTranslation($key, $locale, $value);
         }
      }
   }

   /**
    * Loads all translations for this model instance from the database once.
    */
   protected function loadTranslationsOnce(): void
   {
      if ($this->loadedTranslations === null) {
         if (!$this->exists) {
            $this->loadedTranslations = new Collection();
            return;
         }
         $this->loadedTranslations = DB::table($this->getTranslationsTableName())
            ->where($this->getTranslationForeignKey(), $this->getKey())
            ->get();
      }
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

      $translations = DB::table($firstModel->getTranslationsTableName())
         ->whereIn($foreignKey, $modelIds)
         ->get()
         ->groupBy($foreignKey);

      $collection->each(function ($model) use ($translations, $foreignKey) {
         $modelTranslations = $translations->get($model->getKey(), new Collection());
         $model->setLoadedTranslations($modelTranslations);
      });
   }

   /**
    * Persists a single translation to the database.
    */
   private function persistTranslation(string $key, string|Locale $locale, ?string $value): void
   {
      $localeValue = $locale instanceof Locale ? $locale->value : $locale;

      DB::table($this->getTranslationsTableName())->updateOrInsert(
         [
            $this->getTranslationForeignKey() => $this->getKey(),
            'locale' => $localeValue,
            'column_name' => $key,
         ],
         ['translation' => $value],
      );

      $this->updateLoadedTranslation($key, $localeValue, $value);
   }

   /**
    * Updates or adds a single translation to the in-memory cache.
    */
   protected function updateLoadedTranslation(string $key, string $locale, ?string $value): void
   {
      $this->loadTranslationsOnce();
      $translation = $this->loadedTranslations->where('column_name', $key)->where('locale', $locale)->first();

      if ($translation) {
         $translation->translation = $value;
      } else {
         $this->loadedTranslations->push(
            (object) [
               $this->getTranslationForeignKey() => $this->getKey(),
               'locale' => $locale,
               'column_name' => $key,
               'translation' => $value,
            ],
         );
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
