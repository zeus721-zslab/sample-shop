<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');               // 주문 시점 상품명 스냅샷
            $table->unsignedBigInteger('unit_price');     // 주문 시점 단가
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('subtotal');
            $table->json('options')->nullable();           // 선택 옵션 스냅샷
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
