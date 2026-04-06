<?php

namespace Aaix\EloquentTranslatable\Tests\Models;

use Aaix\EloquentTranslatable\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
   use HasTranslations;

   public array $translatable = ['name', 'meta_keywords'];
   public array $allowJsonTranslationsFor = ['meta_keywords'];
   protected $guarded = [];
}
