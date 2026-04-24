<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_configs', function (Blueprint $table) {
            $table->id();
            $table->enum('grade', ['newbie', 'silver', 'gold', 'vip'])->unique();
            $table->unsignedBigInteger('min_amount')->default(0);   // 최근 12개월 구매 확정 금액 기준
            $table->decimal('point_rate', 4, 2)->default(0.00);     // 적립률 (%)
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_configs');
    }
};
