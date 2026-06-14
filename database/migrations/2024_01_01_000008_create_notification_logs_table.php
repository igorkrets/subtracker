<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('channel', ['tg', 'webhook']);
            $table->text('message')->nullable();
            $table->timestamp('sent_at');
            $table->string('status')->default('sent');
            $table->date('sent_date');

            $table->unique(['service_id', 'sent_date', 'channel'], 'notif_dedup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
