<?php

namespace Aaix\EloquentTranslatable\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class AstrotomicProductTranslation extends Model
{
   protected $table = 'astrotomic_product_translations';
   public $timestamps = false;
   protected $fillable = ['name', 'description'];
}
