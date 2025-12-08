<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('GRN-YYYYMMDD-XXXXX');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('supplier_id');
            $table->string('status')->default('draft')->comment('draft, pending_inspection, approved, rejected');
            $table->date('received_date');
            $table->string('delivery_note_no')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->unsignedBigInteger('received_by');
            $table->unsignedBigInteger('inspected_by')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->string('inspection_status')->nullable()->comment('passed, failed, partial');
            $table->text('inspection_notes')->nullable();
            $table->text('notes')->nullable();
            $table->json('extra_attributes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('restrict');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('inspected_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['branch_id', 'warehouse_id', 'status']);
            $table->index(['purchase_id', 'status']);
            $table->index('received_date');
        });

        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grn_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('purchase_item_id')->nullable();
            $table->decimal('qty_ordered', 18, 4);
            $table->decimal('qty_received', 18, 4);
            $table->decimal('qty_rejected', 18, 4)->default(0);
            $table->decimal('qty_accepted', 18, 4)->default(0);
            $table->string('uom')->nullable();
            $table->decimal('unit_cost', 18, 4);
            $table->string('quality_status')->nullable()->comment('good, damaged, defective, expired');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->text('serial_numbers')->nullable();
            $table->text('notes')->nullable();
            $table->json('extra_attributes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('grn_id')->references('id')->on('goods_received_notes')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('purchase_item_id')->references('id')->on('purchase_items')->onDelete('set null');
            $table->foreign('batch_id')->references('id')->on('inventory_batches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('grn_id');
            $table->index(['product_id', 'quality_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('goods_received_notes');
    }
};
