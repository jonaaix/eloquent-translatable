<?php

namespace Aaix\EloquentTranslatable\Tests\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class AstrotomicProduct extends Model implements TranslatableContract
{
   use Translatable;

   protected $table = 'astrotomic_products';
   protected $guarded = [];

   public array $translatedAttributes = ['name', 'description'];
   public string $translationModel = AstrotomicProductTranslation::class;
   public string $translationForeignKey = 'astrotomic_product_id';
}
