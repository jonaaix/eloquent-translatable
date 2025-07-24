<?php

namespace Aaix\EloquentTranslatable\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @internal This model is used as a fallback for translations when a custom translation model is not defined.
 */
class Translation extends Model
{
   public $timestamps = false;

   protected $guarded = [];
}
