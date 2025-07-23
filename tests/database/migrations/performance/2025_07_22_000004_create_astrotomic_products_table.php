<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      if (!Schema::hasTable('astrotomic_products')) {
         Schema::create('astrotomic_products', function (Blueprint $table) {
            $table->id();
            // Astrotomic does not require fallback columns on the main table.
            $table->timestamps();
         });
      }

      if (!Schema::hasTable('astrotomic_product_translations')) {
         Schema::create('astrotomic_product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('astrotomic_product_id')->constrained('astrotomic_products')->onDelete('cascade');
            $table->string('locale')->index();

            // Translated attributes are actual columns.
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            // Provide a shorter, explicit name for the unique index.
            $table->unique(['astrotomic_product_id', 'locale'], 'astrotomic_prod_trans_locale_unique');
         });
      }
   }

   public function down(): void
   {
      // Intentionally left empty to persist the table for performance tests.
   }
};
