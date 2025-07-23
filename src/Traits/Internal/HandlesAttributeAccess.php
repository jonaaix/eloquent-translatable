<?php

namespace Aaix\EloquentTranslatable\Traits\Internal;

trait HandlesAttributeAccess
{
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
}
