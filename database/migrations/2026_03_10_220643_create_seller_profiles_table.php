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
        Schema::create('seller_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->text('business_address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('upi_id')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('pending_payout', 12, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_profiles');
    }
};
