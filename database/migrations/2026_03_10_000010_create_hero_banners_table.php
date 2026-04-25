<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_banners', function (Blueprint $table) {
            $table->id();
            $table->string('banner_img');
            $table->string('badge_txt', 100)->nullable();
            $table->string('title');
            $table->string('subtitle', 500)->nullable();
            $table->string('button_txt', 100)->nullable();
            $table->string('button_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active', 'idx_hero_banners_active');
            $table->index('sort_order', 'idx_hero_banners_sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_banners');
    }
};
