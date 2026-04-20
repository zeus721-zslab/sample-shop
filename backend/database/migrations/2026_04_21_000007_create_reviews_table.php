<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->tinyInteger('rating');                 // 1~5
            $table->string('title')->nullable();
            $table->text('content');
            $table->json('images')->nullable();
            $table->boolean('is_verified')->default(false); // 구매 확인 리뷰
            $table->boolean('is_best')->default(false);
            $table->timestamps();

            $table->index(['product_id', 'is_verified']);
            $table->unique(['user_id', 'order_item_id']);  // 주문 건당 1리뷰
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
