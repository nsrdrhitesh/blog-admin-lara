<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('disk')->default('public');
            $table->string('path');           // media/2026/08/uuid.webp — path only, never base64
            $table->string('webp_path')->nullable();
            $table->string('avif_path')->nullable();
            $table->string('thumbnail_path')->nullable();

            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('hash', 64)->nullable()->index();

            $table->string('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->string('caption')->nullable();
            $table->string('credit')->nullable();
            $table->string('source_url')->nullable();

            $table->unsignedInteger('reuse_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['folder_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
