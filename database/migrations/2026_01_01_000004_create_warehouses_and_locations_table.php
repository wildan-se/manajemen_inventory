<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('warehouses', function (Blueprint $table) {
      $table->id();
      $table->string('code', 50)->unique();
      $table->string('name');
      $table->text('address')->nullable();
      $table->text('description')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('locations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
      $table->string('code', 50);
      $table->string('name');
      $table->text('description')->nullable();
      $table->timestamps();

      $table->unique(['warehouse_id', 'code']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('locations');
    Schema::dropIfExists('warehouses');
  }
};
