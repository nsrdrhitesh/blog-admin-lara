<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();

            // Path only — never base64. e.g. blogs/2026/08/uuid.webp
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('medium_path')->nullable();
            $table->string('large_path')->nullable();
            $table->string('original_path')->nullable();
            $table->string('webp_path')->nullable();
            $table->string('avif_path')->nullable();

            $table->string('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->string('caption')->nullable();
            $table->text('description')->nullable();
            $table->string('credit')->nullable();
            $table->string('source_url')->nullable();

            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->string('mime_type')->nullable();
            $table->string('hash', 64)->nullable()->index(); // for duplicate detection

            $table->enum('image_type', ['featured', 'banner', 'inline', 'gallery'])->default('inline');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('lazy_load')->default(true);
            $table->unsignedInteger('version')->default(1);

            $table->timestamps();

            $table->index(['blog_id', 'image_type', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_images');
    }
};
