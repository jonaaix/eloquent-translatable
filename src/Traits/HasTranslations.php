<?php

namespace Aaix\EloquentTranslatable\Traits;

use Aaix\EloquentTranslatable\Traits\Internal\HandlesAttributeAccess;
use Aaix\EloquentTranslatable\Traits\Internal\ManagesPersistence;
use Aaix\EloquentTranslatable\Traits\Internal\ProvidesApi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait HasTranslations
{
   use HandlesAttributeAccess;
   use ManagesPersistence;
   use ProvidesApi;

   protected ?string $activeLocale = null;
   protected array $stagedTranslations = [];
   protected ?array $structuredTranslations = null;

   /*
      =========================================
      PROPERTIES TO BE DEFINED ON THE MODEL:
      =========================================
      public array $translatable = [];
      public array $allowJsonTranslationsFor = [];
      protected ?string $spatieReadable = null;

      protected ?string $translationModel = null;
      protected ?string $translationTable = null;
      protected ?string $translationForeignKey = null;
   */

   /**
    * Scope a query to only include models that have a specific translation.
    */
   public function scopeWhereTranslation(Builder $query, string $column, mixed $value, ?string $locale = null): void
   {
      $this->scopeWhereTranslationOperator($query, $column, '=', $value, $locale);
   }

   /**
    * Scope a query to only include models that have a translation containing a substring.
    */
   public function scopeWhereTranslationLike(Builder $query, string $column, mixed $value, ?string $locale = null): void
   {
      $this->scopeWhereTranslationOperator($query, $column, 'LIKE', $value, $locale);
   }

   /**
    * Centralized scope for querying translations with a specific operator.
    */
   private function scopeWhereTranslationOperator(
      Builder $query,
      string $column,
      string $operator,
      mixed $value,
      ?string $locale
   ): void {
      $locale = $locale ?? App::getLocale();

      $query->whereExists(function ($subQuery) use ($column, $operator, $value, $locale) {
         $subQuery
            ->select(\DB::raw(1))
            ->from($this->getTranslationsTableName())
            ->whereColumn($this->getTranslationForeignKey(), $this->getQualifiedKeyName())
            ->where('column_name', $column)
            ->where('locale', $locale)
            ->where('translation', $operator, $value);
      });
   }

   public static function bootHasTranslations(): void
   {
      static::saving(static function (Model $model) {
         if (array_key_exists('_translation_dirty_flag', $model->getAttributes())) {
            unset($model->attributes['_translation_dirty_flag']);
         }
      });
      static::saved(static function (Model $model) {
         $model->persistStagedTranslations();
      });
   }

   protected function getActiveLocale(): ?string
   {
      return $this->activeLocale;
   }

   protected function isJsonTranslation(string $key): bool
   {
      return property_exists($this, 'allowJsonTranslationsFor') && in_array($key, $this->allowJsonTranslationsFor, true);
   }

   protected function isTranslatableColumn(string $key): bool
   {
      if (!property_exists($this, 'translatable') || !is_array($this->translatable)) {
         return false;
      }
      if (in_array('*', $this->translatable, true)) {
         return true;
      }
      return in_array($key, $this->translatable, true);
   }

   protected function getTranslationModelName(): string
   {
      return !empty($this->translationModel) ? $this->translationModel : get_class($this) . 'Translation';
   }

   public function getTranslationsTableName(): string
   {
      return !empty($this->translationTable) ? $this->translationTable : Str::singular($this->getTable()) . '_translations';
   }

   protected function getTranslationForeignKey(): string
   {
      return !empty($this->translationForeignKey)
         ? $this->translationForeignKey
         : Str::snake(class_basename($this)) . '_' . $this->getKeyName();
   }

   protected function resolveTranslatedValue(string $column, ?string $locale): ?string
   {
      $localesToCheck = array_unique(
         array_filter([$locale, $this->getActiveLocale(), App::getLocale(), Config::get('translatable.fallback_locale')]),
      );

      foreach ($localesToCheck as $currentLocale) {
         // First, check the fast-lookup cache for an already loaded translation.
         if (isset($this->structuredTranslations[$column]) && array_key_exists($currentLocale, $this->structuredTranslations[$column])) {
            return $this->structuredTranslations[$column][$currentLocale];
         }

         // If not in the cache, perform a targeted, single-value query.
         if ($this->exists) {
            $translation = $this->fetchSingleTranslation($column, $currentLocale);

            // A translation was found (even if it's NULL). Cache and return it.
            if ($translation !== null) {
               $this->structuredTranslations[$column][$currentLocale] = $translation->translation;
               return $translation->translation;
            }
         }
      }

      // If no translation is found anywhere, fall back to the original attribute.
      return $this->getOriginal($column);
   }

   protected function refreshTranslations(): void
   {
      $this->unsetRelation('translations');
      $this->structuredTranslations = null; // Clear the structured cache
   }

   /**
    * Structures the loaded Eloquent translations into a fast, multi-dimensional array for quick lookups.
    */
   public function structureLoadedTranslations(): void
   {
      $this->structuredTranslations = [];

      if (!$this->relationLoaded('translations') || $this->translations === null) {
         return;
      }

      foreach ($this->translations as $translation) {
         $this->structuredTranslations[$translation->column_name][$translation->locale] = $translation->translation;
      }
   }
}
