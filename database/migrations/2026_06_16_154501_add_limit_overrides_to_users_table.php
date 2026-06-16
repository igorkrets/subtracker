<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('max_services')->nullable()->after('sort_preference');
            $table->unsignedInteger('max_notification_rules')->nullable()->after('max_services');
            $table->unsignedInteger('max_webhooks')->nullable()->after('max_notification_rules');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['max_services', 'max_notification_rules', 'max_webhooks']);
        });
    }
};
