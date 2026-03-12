<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('scope', ['public', 'individual', 'bulk', 'enterprise'])->default('public')->after('code');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('scope');
            $table->unsignedInteger('max_uses_per_user')->default(1)->after('max_uses');
            $table->string('label')->nullable()->after('description');
            $table->string('prefix')->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (Schema::hasColumn('coupons', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn(['scope', 'user_id', 'max_uses_per_user', 'label', 'prefix']);
            }
        });
    }
};
