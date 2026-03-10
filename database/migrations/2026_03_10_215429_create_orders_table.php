<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'shipped',
                'out_for_delivery', 'delivered', 'cancelled', 'returned', 'refunded'
            ])->default('pending');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_charge', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('coupon_code')->nullable();
            $table->text('notes')->nullable();
            $table->json('shipping_address')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('shiprocket_order_id')->nullable();
            $table->string('shiprocket_shipment_id')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
