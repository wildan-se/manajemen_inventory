<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('stock_opnames', function (Blueprint $table) {
      $table->id();
      $table->string('reference_number', 50)->unique();
      $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
      $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
      $table->date('counted_at');
      $table->text('notes')->nullable();
      $table->foreignId('user_id')->constrained()->restrictOnDelete();
      $table->timestamps();
    });

    Schema::create('stock_opname_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('stock_opname_id')->constrained()->cascadeOnDelete();
      $table->foreignId('item_id')->constrained()->restrictOnDelete();
      $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
      $table->decimal('system_quantity', 15, 4)->default(0);
      $table->decimal('physical_quantity', 15, 4)->default(0);
      $table->decimal('discrepancy', 15, 4)->default(0)->comment('physical - system');
      $table->text('notes')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('stock_opname_items');
    Schema::dropIfExists('stock_opnames');
  }
};
