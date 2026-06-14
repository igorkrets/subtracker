<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_presets', function (Blueprint $table) {
            $table->id();
            $table->string('type_slug');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon');
            $table->enum('icon_set', ['simple-icons', 'lucide', 'custom'])->default('lucide');
            $table->string('color', 7)->nullable();
            $table->string('default_url')->nullable();
            $table->json('aliases')->nullable();
            $table->enum('region', ['global', 'ru', 'eu', 'us'])->nullable();
            $table->boolean('is_popular')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_presets');
    }
};
