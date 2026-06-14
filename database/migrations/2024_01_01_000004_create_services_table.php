<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');
            $table->string('type_slug')->nullable();
            $table->foreignId('catalog_preset_id')->nullable()->constrained('catalog_presets')->onDelete('set null');
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('ip')->nullable();
            $table->string('icon')->nullable();
            $table->enum('icon_set', ['simple-icons', 'lucide', 'custom'])->default('lucide');
            $table->string('color', 7)->nullable();
            $table->date('expires_at')->nullable();
            $table->date('renewed_at')->nullable();
            $table->enum('billing_cycle', ['one_time', 'monthly', 'quarterly', 'semiannual', 'yearly', 'custom'])->nullable();
            $table->integer('billing_interval_days')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->boolean('is_trial')->default(false);
            $table->date('trial_ends_at')->nullable();
            $table->date('last_paid_at')->nullable();
            $table->json('notify_days')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->char('currency', 3)->default('RUB');
            $table->string('provider_name')->nullable();
            $table->string('provider_url')->nullable();
            $table->boolean('notifications_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'expires_at']);
            $table->index(['user_id', 'group_id', 'sort_order']);
            $table->index('type_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
