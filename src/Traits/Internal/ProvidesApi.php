<?php

namespace Aaix\EloquentTranslatable\Traits\Internal;

use Aaix\EloquentTranslatable\Enums\Locale;
use Aaix\EloquentTranslatable\Exceptions\AttributeIsNotTranslatable;
use Aaix\EloquentTranslatable\TranslationProxy;
use Aaix\EloquentTranslatable\Models\Translation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

trait ProvidesApi
{
   /**
    * Get the optional Eloquent relationship to the translations.
    */
   public function translations(): HasMany
   {
      $customModelName = $this->getTranslationModelName();

      if (class_exists($customModelName)) {
         // If a custom model exists, use it as before.
         return $this->hasMany($customModelName, $this->getTranslationForeignKey());
      }

      // If no custom model exists, use our internal generic fallback model.
      // We manually construct the HasMany relationship.
      $instance = new Translation();
      $instance->setTable($this->getTranslationsTableName());

      $foreignKey = $this->getTranslationsTableName().'.'.$this->getTranslationForeignKey();
      $localKey = $this->getKeyName();

      return new HasMany($instance->newQuery(), $this, $foreignKey, $localKey);
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
      $this->ensureTranslationsAreLoaded();
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
}
