<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('grade', ['newbie', 'silver', 'gold', 'vip'])->default('newbie')->after('is_active');
            $table->unsignedBigInteger('points')->default(0)->after('grade');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('points');
            $table->unsignedSmallInteger('birth_year')->nullable()->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['grade', 'points', 'gender', 'birth_year']);
        });
    }
};
