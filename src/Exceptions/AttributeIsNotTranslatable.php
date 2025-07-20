<?php

namespace Aaix\EloquentTranslatable\Exceptions;

use Exception;

class AttributeIsNotTranslatable extends Exception
{
   public static function make(string $key, object $model): self
   {
      $class = get_class($model);
      return new self("Attribute `{$key}` is not translatable on model `{$class}`. Please add it to the `\$translatable` array.");
   }
}
