<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('bot_key', 32);
            $table->bigInteger('chat_id');
            $table->unsignedBigInteger('id_cliente')->nullable();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->nullOnDelete();
            $table->text('state')->nullable();
            $table->timestamps();
            $table->unique(['bot_key', 'chat_id']);
        });
        Schema::create('telegram_updates', function (Blueprint $table) {
            $table->id();
            $table->string('bot_key', 32);
            $table->bigInteger('update_id');
            $table->json('outgoing');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['bot_key', 'update_id']);
        });
        Schema::create('appointment_day_locks', function (Blueprint $table) {
            $table->date('fecha')->primary();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_updates');
        Schema::dropIfExists('telegram_sessions');
        Schema::dropIfExists('appointment_day_locks');
    }
};
