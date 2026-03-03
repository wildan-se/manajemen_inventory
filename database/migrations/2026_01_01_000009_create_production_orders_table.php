<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('production_orders', function (Blueprint $table) {
      $table->id();
      $table->string('wo_number', 50)->unique();
      $table->string('title');
      $table->text('description')->nullable();
      $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
      $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
      $table->date('planned_start')->nullable();
      $table->date('planned_end')->nullable();
      $table->date('actual_start')->nullable();
      $table->date('actual_end')->nullable();
      $table->foreignId('user_id')->constrained()->restrictOnDelete();
      $table->timestamps();
    });

    Schema::create('production_order_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
      $table->foreignId('item_id')->constrained()->restrictOnDelete();
      $table->decimal('quantity', 15, 4);
      $table->enum('type', ['input', 'output']);
      $table->text('notes')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('production_order_items');
    Schema::dropIfExists('production_orders');
  }
};
