<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('final_amount')->default(0)->after('discount_amount');
            $table->string('coupon_code')->nullable()->after('payment_id');
            $table->json('payment_raw')->nullable()->after('coupon_code');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('product_image')->nullable()->after('product_name');
            $table->unsignedBigInteger('total_price')->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['final_amount', 'coupon_code', 'payment_raw']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['product_image', 'total_price']);
        });
    }
};
