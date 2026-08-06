<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            // Core content
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content');

            // Media
            $table->string('featured_image')->nullable();
            $table->string('banner_image')->nullable();

            // Taxonomy / ownership
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // creator/editor

            // Publishing
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('scheduled_at')->nullable();

            // Flags
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_sticky')->default(false);
            $table->boolean('allow_comments')->default(true);

            // Reading metadata
            $table->unsignedSmallInteger('reading_time_minutes')->nullable();
            $table->unsignedInteger('word_count')->default(0);
            $table->string('language', 10)->default('en');

            // Geo/location context (subject matter of the article, not the visitor)
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Counters (denormalized, kept in sync via events/jobs)
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('share_count')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'is_trending', 'is_sticky']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
