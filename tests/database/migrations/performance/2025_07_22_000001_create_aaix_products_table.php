<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
   {
      if (!Schema::hasTable('aaix_products')) {
         Schema::create('aaix_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->timestamps();
         });
      }
   }

   public function down(): void
   {
      // Intentionally left empty to persist the table for performance tests.
   }
};
