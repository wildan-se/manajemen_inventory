<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('items', function (Blueprint $table) {
      $table->id();
      $table->string('code', 50)->unique();
      $table->string('name');
      $table->foreignId('category_id')->constrained()->restrictOnDelete();
      $table->foreignId('unit_id')->constrained()->restrictOnDelete();
      $table->text('description')->nullable();
      $table->decimal('min_stock', 15, 4)->default(0);
      $table->decimal('max_stock', 15, 4)->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('items');
  }
};
