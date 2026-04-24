<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->enum('type', ['1to1', 'group'])->default('1to1');
            $table->string('name', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['type', 'is_active'], 'idx_type_active');
        });

        Schema::create('chat_participants', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('room_id');
            $table->string('user_id', 100);
            $table->enum('user_type', ['user', 'admin'])->default('user');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('last_read_at')->nullable();

            $table->unique(['room_id', 'user_id', 'user_type'], 'uq_room_user');
            $table->index(['user_id', 'user_type'], 'idx_user');
            $table->foreign('room_id')->references('id')->on('chat_rooms')->cascadeOnDelete();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('room_id');
            $table->string('sender_id', 100);
            $table->enum('sender_type', ['user', 'admin'])->default('user');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['room_id', 'created_at'], 'idx_room_created');
            $table->index(['room_id', 'sender_type', 'is_read'], 'idx_unread');
            $table->foreign('room_id')->references('id')->on('chat_rooms')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_participants');
        Schema::dropIfExists('chat_rooms');
    }
};
