<?php

namespace Aaix\EloquentTranslatable\Tests\Models;

use Aaix\EloquentTranslatable\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
   use HasTranslations;

   public array $translatable = ['text'];

   protected $guarded = [];

   public function post(): BelongsTo
   {
      return $this->belongsTo(Post::class);
   }
}
