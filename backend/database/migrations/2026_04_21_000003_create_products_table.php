<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete(); // NULL = single mode
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('detail')->nullable();
            $table->unsignedBigInteger('price');           // 원 단위 정수
            $table->unsignedBigInteger('sale_price')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->string('status')->default('active');   // active | inactive | soldout
            $table->json('images')->nullable();            // ["url1","url2",...]
            $table->json('options')->nullable();           // {"color":["red","blue"],"size":["S","M","L"]}
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('order_count')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
