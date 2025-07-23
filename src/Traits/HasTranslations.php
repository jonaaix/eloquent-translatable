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
     * Refreshes the translation cache by clearing and reloading from the database.
     */
    protected function refreshTranslations(): void
    {
        $this->loadedTranslations = null;
        $this->loadTranslationsOnce();
    }
}
