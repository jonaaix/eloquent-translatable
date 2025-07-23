<?php

use Aaix\EloquentTranslatable\Tests\Models\Comment;
use Aaix\EloquentTranslatable\Tests\Models\Post;
use Illuminate\Support\Facades\App;

test('it does not interfere with accessing relationships', function () {
   $post = Post::create([]);
   Comment::create(['post_id' => $post->id]);

   $retrievedPost = Post::find($post->id);

   expect($retrievedPost->comments)->toHaveCount(1);
});

test('it allows accessing translations on both parent and related models', function () {
   App::setLocale('de');

   $post = Post::create(['title' => 'Deutscher Titel']);
   $comment = $post->comments()->create(['text' => 'Deutscher Kommentar']);

   $retrievedPost = Post::with('comments')->find($post->id);

   expect($retrievedPost->title)
      ->toBe('Deutscher Titel')
      ->and($retrievedPost->comments->first()->text)
      ->toBe('Deutscher Kommentar');
});

test('it handles creating relationships with translated properties correctly', function () {
   $post = Post::create([]);

   $post->setLocale('es')->title = 'Título en español';
   $post->save();

   $comment = $post->comments()->create([
      'text' => 'Texto base en inglés',
   ]);

   $comment->setLocale('es')->text = 'Comentario en español';
   $comment->save();

   $this->assertDatabaseHas('post_translations', [
      'post_id' => $post->id,
      'locale' => 'es',
      'column_name' => 'title',
      'translation' => 'Título en español',
   ]);

   $this->assertDatabaseHas('comment_translations', [
      'comment_id' => $comment->id,
      'locale' => 'es',
      'column_name' => 'text',
      'translation' => 'Comentario en español',
   ]);
});
