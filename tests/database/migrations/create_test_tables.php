<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
   {
      Schema::create('test_models', function (Blueprint $table) {
         $table->id();
         $table->string('name')->nullable();
         $table->timestamps();
      });

      Schema::create('test_model_translations', function (Blueprint $table) {
         $table->id();
         $table->foreignId('test_model_id')->constrained()->onDelete('cascade');
         $table->string('locale');
         $table->string('column_name');
         $table->text('translation')->nullable();
         $table->timestamps();

         $table->unique(['test_model_id', 'locale', 'column_name']);
      });
   }
};
