<?php

namespace Aaix\EloquentTranslatable\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class AaixProductTranslation extends Model
{
   public $timestamps = false;

   protected $table = 'aaix_product_translations';

   protected $guarded = [];
}
