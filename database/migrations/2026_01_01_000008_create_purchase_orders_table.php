<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('purchase_orders', function (Blueprint $table) {
      $table->id();
      $table->string('po_number', 50)->unique();
      $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
      $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
      $table->enum('status', ['draft', 'approved', 'partially_received', 'received', 'cancelled'])->default('draft');
      $table->date('order_date');
      $table->date('expected_date')->nullable();
      $table->text('notes')->nullable();
      $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamp('approved_at')->nullable();
      $table->foreignId('user_id')->constrained()->restrictOnDelete();
      $table->timestamps();
    });

    Schema::create('purchase_order_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
      $table->foreignId('item_id')->constrained()->restrictOnDelete();
      $table->decimal('quantity_ordered', 15, 4);
      $table->decimal('quantity_received', 15, 4)->default(0);
      $table->decimal('unit_price', 15, 2)->default(0);
      $table->text('notes')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('purchase_order_items');
    Schema::dropIfExists('purchase_orders');
  }
};
