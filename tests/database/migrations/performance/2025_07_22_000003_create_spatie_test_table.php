<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      if (!Schema::hasTable('spatie_products')) {
         Schema::create('spatie_products', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description');
            $table->timestamps();
         });
      }
   }

   public function down(): void
   {
      // Intentionally left empty to persist the table for performance tests.
   }
};
