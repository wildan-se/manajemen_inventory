<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('stock_movements', function (Blueprint $table) {
      $table->id();
      $table->string('reference_number', 50)->unique();
      $table->enum('type', [
        'goods_receipt',
        'material_issue',
        'stock_transfer',
        'production_output',
        'sales_dispatch',
        'stock_adjustment',
        'stock_opname',
      ]);
      $table->foreignId('item_id')->constrained()->restrictOnDelete();
      $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
      $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
      $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
      $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();
      $table->decimal('quantity', 15, 4);
      $table->decimal('quantity_before', 15, 4)->default(0);
      $table->decimal('quantity_after', 15, 4)->default(0);
      $table->string('reference_document')->nullable();
      $table->text('notes')->nullable();
      $table->foreignId('user_id')->constrained()->restrictOnDelete();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('stock_movements');
  }
};
