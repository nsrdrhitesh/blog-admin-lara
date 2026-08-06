<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Polymorphic: attaches to Blog, Page, Category, etc.
        Schema::create('seos', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable'); // seoable_type, seoable_id

            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->default('index,follow');

            $table->string('focus_keyword')->nullable();
            $table->json('secondary_keywords')->nullable();

            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();

            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->string('twitter_card')->default('summary_large_image');

            $table->unsignedTinyInteger('seo_score')->nullable();
            $table->json('seo_suggestions')->nullable();

            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seos');
    }
};
