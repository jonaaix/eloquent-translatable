<?php

namespace Aaix\EloquentTranslatable\Traits;

use Aaix\EloquentTranslatable\Enums\Locale;
use Aaix\EloquentTranslatable\Exceptions\AttributeIsNotTranslatable;
use Aaix\EloquentTranslatable\TranslationProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

trait HasTranslations
{
   /** Internal properties to manage translations and locales. */
   protected ?string $activeLocale = null;
   protected ?Collection $loadedTranslations = null;
   protected array $stagedTranslations = [];

   /*
      PROPERTIES TO BE DEFINED ON THE MODEL:
      =========================================
      // Defines which attributes are translatable. For security, this is required.
      // To allow all attributes, use `['*']`.
      public array $translatable = [];

      // Optional: Defines attributes that should be treated as single JSON-encoded translations.
      public array $allowJsonTranslationsFor = [];

      // Optional: The FQCN of the translation model for the Eloquent relationship.
      protected ?string $translationModel = null;

      // Optional: The name of the translations table if it doesn't follow convention.
      protected ?string $translationTable = null;

      // Optional: The name of the foreign key if it doesn't follow convention.
      protected ?string $translationForeignKey = null;
    */

   /**
    * Boot the trait and register model event listeners.
    */
   public static function bootHasTranslations(): void
   {
      static::saving(static function (Model $model) {
         // If a translation has been set using the persistent locale mode,
         // a temporary dirty flag is added to trigger the `save` method.
         // We must remove this flag before the model is actually saved to
         // prevent it from being written to the database.
         if (array_key_exists('_translation_dirty_flag', $model->getAttributes())) {
            unset($model->attributes['_translation_dirty_flag']);
         }
      });

      // Persist all staged translations after the main model has been saved.
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
    * Get the optional Eloquent relationship to the translations.
    */
   public function translations(): HasMany
   {
      $modelClass = $this->getTranslationModelName();
      if (!class_exists($modelClass)) {
         throw new RuntimeException(
            "The translation model '{$modelClass}' does not exist. Please create it to use the translations() relationship.",
         );
      }
      return $this->hasMany($modelClass, $this->getTranslationForeignKey());
   }

   /**
    * Get a translated attribute based on the persistent or application locale.
    */
   public function __get($key)
   {
      // First, check if the attribute is even meant to be translatable.
      // If not, do not interfere and let Eloquent handle it normally.
      // This is crucial for relationships (like ->variations) to work correctly.
      if (!$this->isTranslatableColumn($key)) {
         return parent::__get($key);
      }

      $value = $this->resolveTranslatedValue($key, $this->getActiveLocale());

      if ($this->isJsonTranslation($key)) {
         if (is_string($value) && ($json = json_decode($value, true)) !== null) {
            return $json;
         }
      }

      return $value;
   }

   /**
    * Check if a translated attribute is set.
    */
   public function __isset($key)
   {
      // First, check if the attribute is even meant to be translatable.
      // If not, let Eloquent handle the check (e.g., for relationships).
      if (!$this->isTranslatableColumn($key)) {
         return parent::__isset($key);
      }

      // If it is translatable, check if a value (translation or fallback) exists.
      return $this->getTranslation($key) !== null;
   }

   /**
    * Override the default setAttribute method to handle assignments when a persistent locale is active.
    */
   public function setAttribute($key, $value)
   {
      if (!$this->isTranslatableColumn($key)) {
         return parent::setAttribute($key, $value);
      }

      // Handle multi-locale array assignment (Spatie-compatible), but ignore for JSON attributes.
      if (is_array($value) && !$this->isJsonTranslation($key)) {
         $this->setTranslations($key, $value);

         // Mark the model as dirty to trigger save() using a temporary flag.
         $this->attributes['_translation_dirty_flag'] = true;

         return $this;
      }

      // If a persistent locale is active ("translation mode"), handle assignments as translations.
      if ($this->getActiveLocale()) {
         $valueToStore = $this->isJsonTranslation($key) ? json_encode($value, JSON_THROW_ON_ERROR) : $value;
         $this->setTranslation($key, $this->getActiveLocale(), $valueToStore);
         $this->attributes['_translation_dirty_flag'] = true;

         // For JSON attributes, also set the value on the parent model. This allows immediate
         // access to the casted array value even before the model is saved.
         if ($this->isJsonTranslation($key)) {
            return parent::setAttribute($key, $value);
         }

         return $this;
      }

      // Fallback to default Eloquent behavior. This handles setting the base attribute
      // or a JSON attribute when not in translation mode.
      return parent::setAttribute($key, $value);
   }

   /**
    * Sets a persistent locale for both read and write operations on this instance.
    */
   public function setLocale(string|Locale $locale): static
   {
      $this->activeLocale = $locale instanceof Locale ? $locale->value : $locale;
      return $this;
   }

   /**
    * Resets the persistent locale.
    */
   public function resetLocale(): static
   {
      $this->activeLocale = null;
      return $this;
   }

   /**
    * Fetches a translated value for a specific locale in a stateless way.
    * This is a one-time operation and does not change the model's active locale.
    */
   public function inLocale(string|Locale $locale)
   {
      $localeValue = $locale instanceof Locale ? $locale->value : $locale;
      return new TranslationProxy($this, $localeValue);
   }

   /**
    * Fetches the translated value of an attribute for a specific language.
    * This is the explicit public method for retrieving a single translation.
    */
   public function getTranslation(string $column, string|Locale|null $locale = null): ?string
   {
      $locale = $locale instanceof Locale ? $locale->value : $locale;
      return $this->resolveTranslatedValue($column, $locale);
   }

   /**
    * Gets a collection of all translations for this model.
    */
   public function getTranslations(?string $columnName = null): Collection
   {
      $this->loadTranslationsOnce();
      if ($columnName) {
         return $this->loadedTranslations->where('column_name', $columnName);
      }
      return $this->loadedTranslations;
   }

   /**
    * Stages a single translation to be saved on the next `save()` call.
    */
   public function setTranslation(string $key, string|Locale $locale, mixed $value): static
   {
      if (!$this->isTranslatableColumn($key)) {
         throw AttributeIsNotTranslatable::make($key, $this);
      }

      $valueToStore = $this->isJsonTranslation($key) && is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : $value;

      if (!is_string($valueToStore) && $valueToStore !== null) {
         throw new \InvalidArgumentException(
            "Translation value for key '{$key}' must be a string, or an array for JSON-castable attributes.",
         );
      }

      $localeValue = $locale instanceof Locale ? $locale->value : $locale;
      $this->stagedTranslations[$localeValue][$key] = $valueToStore;
      $this->updateLoadedTranslation($key, $localeValue, $valueToStore);

      return $this;
   }

   /**
    * Stages multiple translations for a single attribute to be saved on the next `save()` call.
    */
   public function setTranslations(string $key, array $translations): static
   {
      foreach ($translations as $locale => $value) {
         $this->setTranslation($key, $locale, $value);
      }
      return $this;
   }

   /**
    * Stores a single translation directly in the database.
    * Throws an exception if the model does not exist.
    */
   public function storeTranslation(string $key, string|Locale $locale, ?string $value): static
   {
      if (!$this->exists) {
         throw new RuntimeException('Cannot store a translation for a model that does not exist.');
      }
      $this->persistTranslation($key, $locale, $value);
      return $this;
   }

   /**
    * Stores multiple translations for a single attribute directly in the database.
    */
   public function storeTranslations(string $key, array $translations): static
   {
      foreach ($translations as $locale => $value) {
         $this->storeTranslation($key, $locale, $value);
      }
      return $this;
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
    * Resolves the translated value for a given column and locale, applying fallbacks.
    */
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

   /**
    * Gets the currently active persistent locale for this model instance.
    */
   protected function getActiveLocale(): ?string
   {
      return $this->activeLocale;
   }

   /**
    * Checks if a given key is defined as a JSON translation.
    * This is used to determine how to handle the value when setting or getting translations.
    */
   protected function isJsonTranslation(string $key): bool
   {
      return property_exists($this, 'allowJsonTranslationsFor') && in_array($key, $this->allowJsonTranslationsFor, true);
   }

   /**
    * Determines if a given column is defined as translatable.
    */
   protected function isTranslatableColumn(string $key): bool
   {
      // If the $translatable property is not defined in the model, nothing is translatable for safety.
      if (!property_exists($this, 'translatable') || !is_array($this->translatable)) {
         return false;
      }

      // Check for the wildcard character to allow all attributes.
      if (in_array('*', $this->translatable, true)) {
         return true;
      }

      // Otherwise, check if the specific key is in the allow-list.
      return in_array($key, $this->translatable, true);
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
    * Refreshes the translation cache by clearing and reloading from the database.
    */
   protected function refreshTranslations(): void
   {
      $this->loadedTranslations = null;
      $this->loadTranslationsOnce();
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
}
