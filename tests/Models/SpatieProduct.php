<?php

namespace Aaix\EloquentTranslatable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SpatieProduct extends Model
{
    use HasTranslations;

    protected $table = 'spatie_products';

    protected $guarded = [];

    public array $translatable = ['name', 'description'];
}
