<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('role')->default('customer')->after('phone'); // customer | seller | admin
            $table->string('shop_name')->nullable()->after('role');       // marketplace: seller shop name
            $table->text('shop_description')->nullable()->after('shop_name');
            $table->boolean('is_active')->default(true)->after('shop_description');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'shop_name', 'shop_description', 'is_active']);
        });
    }
};
