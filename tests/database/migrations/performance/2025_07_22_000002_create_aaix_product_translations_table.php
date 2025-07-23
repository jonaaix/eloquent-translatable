<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
   {
      if (!Schema::hasTable('aaix_product_translations')) {
         Schema::create('aaix_product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aaix_product_id')->constrained('aaix_products')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('column_name');
            $table->text('translation');
            $table->unique(['aaix_product_id', 'locale', 'column_name'], 'aaix_translations_unique');
         });
      }
   }

   public function down(): void
   {
      // Intentionally left empty to persist the table for performance tests.
   }
};
