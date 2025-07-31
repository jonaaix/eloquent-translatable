<?php

namespace Aaix\EloquentTranslatable\Traits\Internal;

use Aaix\EloquentTranslatable\Enums\Locale;
use Aaix\EloquentTranslatable\Exceptions\AttributeIsNotTranslatable;
use Aaix\EloquentTranslatable\Models\Translation;
use Aaix\EloquentTranslatable\TranslationProxy;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

trait ProvidesApi
{
   public function translations(): HasMany
   {
      $customModelName = $this->getTranslationModelName();
      if (class_exists($customModelName)) {
         return $this->hasMany($customModelName, $this->getTranslationForeignKey());
      }

      $instance = new Translation();
      $instance->setTable($this->getTranslationsTableName());

      $foreignKey = $this->getTranslationsTableName() . '.' . $this->getTranslationForeignKey();
      $localKey = $this->getKeyName();

      return new HasMany($instance->newQuery(), $this, $foreignKey, $localKey);
   }

   public function setLocale(string|Locale $locale): static
   {
      $this->activeLocale = $locale instanceof Locale ? $locale->value : $locale;
      return $this;
   }

   public function resetLocale(): static
   {
      $this->activeLocale = null;
      return $this;
   }

   public function inLocale(string|Locale $locale)
   {
      $localeValue = $locale instanceof Locale ? $locale->value : $locale;
      return new TranslationProxy($this, $localeValue);
   }

   public function getTranslation(string $column, string|Locale|null $locale = null): ?string
   {
      $locale = $locale instanceof Locale ? $locale->value : $locale;
      return $this->resolveTranslatedValue($column, $locale);
   }

   public function getTranslations(?string $key = null, ?array $allowedLocales = null): array
   {
      $this->ensureTranslationsAreLoaded();

      $columnsToProcess = $key ? [$key] : $this->translatable;
      $results = [];

      foreach ($columnsToProcess as $column) {
         if (!$this->isTranslatableColumn($column)) {
            continue;
         }

         $translations = [];
         // 1. Start with the fallback value.
         $fallbackLocale = Config::get('translatable.fallback_locale');
         $translations[$fallbackLocale] = $this->getOriginal($column);

         // 2. Layer on DB translations.
         if (isset($this->structuredTranslations[$column])) {
            $translations = array_merge($translations, $this->structuredTranslations[$column]);
         }

         // 3. Layer on staged translations.
         foreach ($this->stagedTranslations as $locale => $staged) {
            if (isset($staged[$column])) {
               $translations[$locale] = $staged[$column];
            }
         }

         // 4. Filter by allowed locales if provided.
         if ($allowedLocales) {
            $translations = array_intersect_key($translations, array_flip($allowedLocales));
         }

         $results[$column] = $translations;
      }

      // If only one column was requested, return its translations directly.
      if ($key !== null) {
         return $results[$key] ?? [];
      }

      return $results;
   }

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

   public function setTranslations(string $key, array $translations): static
   {
      foreach ($translations as $locale => $value) {
         $this->setTranslation($key, $locale, $value);
      }
      return $this;
   }

   public function storeTranslation(string $key, string|Locale $locale, ?string $value): static
   {
      if (!$this->exists) {
         throw new RuntimeException('Cannot store a translation for a model that does not exist.');
      }
      $this->persistTranslation($key, $locale, $value);
      return $this;
   }

   public function storeTranslations(string $key, array $translations): static
   {
      foreach ($translations as $locale => $value) {
         $this->storeTranslation($key, $locale, $value);
      }
      return $this;
   }
}
