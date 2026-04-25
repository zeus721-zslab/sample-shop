<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // orders: created_at (StatsController 일별 매출, 시간대별 집계)
        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at', 'idx_orders_created_at');
        });

        // order_items: product_id (RecommendationController 협업 필터링)
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('product_id', 'idx_order_items_product_id');
        });

        // reviews: user_id (MyController 내 리뷰 조회)
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('user_id', 'idx_reviews_user_id');
        });

        // point_histories: user_id (PointController 이력 조회)
        Schema::table('point_histories', function (Blueprint $table) {
            $table->index('user_id', 'idx_point_histories_user_id');
        });

        // chat_messages: room_id + sender_type + is_read (미읽음 카운트)
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index(['room_id', 'sender_type', 'is_read'], 'idx_chat_messages_unread');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_created_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_product_id');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_user_id');
        });

        Schema::table('point_histories', function (Blueprint $table) {
            $table->dropIndex('idx_point_histories_user_id');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('idx_chat_messages_unread');
        });
    }
};
