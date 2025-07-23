<?php

namespace Aaix\EloquentTranslatable\Tests\Models;

use Aaix\EloquentTranslatable\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

use Aaix\EloquentTranslatable\Tests\Models\EloquentProductTranslation;

class EloquentProduct extends Model
{
   use HasTranslations;

   protected $table = 'aaix_products';

   protected $guarded = [];

   public array $translatable = ['name', 'description'];

   protected ?string $translationModel = EloquentProductTranslation::class;

   protected ?string $translationForeignKey = 'aaix_product_id';

   public function getTranslationsTableName(): string
   {
      return 'aaix_product_translations';
   }
}
