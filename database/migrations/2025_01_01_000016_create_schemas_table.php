<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stores generated/overridden JSON-LD blocks per schemable entity.
        Schema::create('schemas', function (Blueprint $table) {
            $table->id();
            $table->morphs('schemable');
            $table->enum('type', [
                'article', 'breadcrumb', 'faq', 'organization',
                'person', 'search_action', 'image', 'video', 'howto', 'speakable',
            ]);
            $table->json('data'); // raw JSON-LD payload
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['schemable_type', 'schemable_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schemas');
    }
};
