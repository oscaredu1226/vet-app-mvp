<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_reservation_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('chat_id')->index();
            $table->string('payload_hash', 64);
            $table->text('payload');
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at');
            $table->unsignedBigInteger('id_evento')->nullable();
            $table->foreign('id_evento')->references('id_evento')->on('eventos')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('bot_reservation_requests'); }
};
