<?php

namespace Aaix\EloquentTranslatable\Tests\Models;

use Aaix\EloquentTranslatable\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'content'];

    protected $guarded = [];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
