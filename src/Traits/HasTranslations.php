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
         PROPERTIES TO BE DEFINED ON THE MODEL:
         =========================================
         public array $translatable = [];
         public array $allowJsonTranslationsFor = [];
         protected ?string $translationModel = null;
         protected ?string $translationTable = null;
         protected ?string $translationForeignKey = null;
   */

   /**
    * Scope a query to only include models that have a specific translation.
    */
   public function scopeWhereTranslation(Builder $query, string $column, mixed $value, ?string $locale = null): void
   {
      $locale = $locale ?? App::getLocale();
      $translationTable = $this->getTranslationsTableName();
      $modelTable = $this->getTable();

      $isAlreadyJoined = collect($query->getQuery()->joins)
         ->pluck('table')
         ->contains($translationTable);

      if (!$isAlreadyJoined) {
         $query->join($translationTable, $modelTable . '.' . $this->getKeyName(), '=', $translationTable . '.' . $this->getTranslationForeignKey())
            ->select($modelTable . '.*'); // Avoid column collisions
      }

      $query->where($translationTable . '.column_name', '=', $column)
         ->where($translationTable . '.locale', '=', $locale)
         ->where($translationTable . '.translation', '=', $value);
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
      return !empty($this->translationModel) ?
         $this->translationModel : get_class($this) . 'Translation';
   }

   public function getTranslationsTableName(): string
   {
      return !empty($this->translationTable) ?
         $this->translationTable : Str::singular($this->getTable()) . '_translations';
   }

   protected function getTranslationForeignKey(): string
   {
      return !empty($this->translationForeignKey)
         ? $this->translationForeignKey
         : Str::snake(class_basename($this)) . '_' . $this->getKeyName();
   }

   protected function resolveTranslatedValue(string $column, ?string $locale): ?string
   {
      $this->ensureTranslationsAreLoaded();

      $localesToCheck = array_unique(
         array_filter([$locale, $this->getActiveLocale(), App::getLocale(), Config::get('translatable.fallback_locale')]),
      );

      if (array_key_exists($column, $this->structuredTranslations ?? [])) {
         foreach ($localesToCheck as $currentLocale) {
            if (array_key_exists($currentLocale, $this->structuredTranslations[$column])) {
               return $this->structuredTranslations[$column][$currentLocale];
            }
         }
      }

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
