<?php

namespace Aaix\EloquentTranslatable;

use Aaix\EloquentTranslatable\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class TranslationProxy
{
   /** @var Model|HasTranslations */
   protected Model $model;
   protected string $locale;

   public function __construct(Model $model, string $locale)
   {
      $this->model = $model;
      $this->locale = $locale;
   }

   /**
    * Magic getter to retrieve a translation for the given key.
    */
   public function __get(string $key): ?string
   {
      return $this->model->getTranslation($key, $this->locale);
   }
}
