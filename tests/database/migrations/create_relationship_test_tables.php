<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->text('content')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('post_translations')) {
            Schema::create('post_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained()->cascadeOnDelete();
                $table->string('locale');
                $table->string('column_name');
                $table->text('translation')->nullable();
                $table->unique(['post_id', 'locale', 'column_name']);
            });
        }

        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained()->cascadeOnDelete();
                $table->text('text')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('comment_translations')) {
            Schema::create('comment_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
                $table->string('locale');
                $table->string('column_name');
                $table->text('translation')->nullable();
                $table->unique(['comment_id', 'locale', 'column_name']);
            });
        }
    }
};