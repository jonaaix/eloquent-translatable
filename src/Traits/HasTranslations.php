<?php

namespace Aaix\EloquentTranslatable\Traits;

use Aaix\EloquentTranslatable\Traits\Internal\HandlesAttributeAccess;
use Aaix\EloquentTranslatable\Traits\Internal\ManagesPersistence;
use Aaix\EloquentTranslatable\Traits\Internal\ProvidesApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait HasTranslations
{
   use HandlesAttributeAccess;
   use ManagesPersistence;
   use ProvidesApi;

   /** Internal properties to manage translations and locales. */
   protected ?string $activeLocale = null;
   protected ?Collection $loadedTranslations = null;
   protected array $stagedTranslations = [];
   /*
         PROPERTIES TO BE DEFINED ON THE MODEL:
         =========================================
         public array $translatable = [];
         public array $allowJsonTranslationsFor = [];
         protected ?string $translationModel = null;
         protected ?string $translationTable = null;
         protected ?string $translationForeignKey = null;
    */

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

      static::deleting(static function (Model $model) {
         if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
            return;
         }
         $model->deleteTranslations();
      });
   }

   /**
    * Sets the loaded translations collection on the model instance.
    * Used by the eager loading mechanism.
    */
   public function setLoadedTranslations(Collection $translations): void
   {
      $this->loadedTranslations = $translations;
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
      $this->loadTranslationsOnce();
      $localesToCheck = array_unique(
         array_filter([$locale, $this->getActiveLocale(), App::getLocale(), Config::get('translatable.fallback_locale')]),
      );
      foreach ($localesToCheck as $currentLocale) {
         $translation = $this->loadedTranslations->where('column_name', $column)->where('locale', $currentLocale)->first();
         if ($translation !== null) {
            return $translation->translation;
         }
      }

      return $this->getOriginal($column);
   }

   protected function refreshTranslations(): void
   {
      $this->loadedTranslations = null;
      $this->loadTranslationsOnce();
   }
}
