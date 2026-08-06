<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Generative Engine Optimization metadata — one per Blog/Page.
        Schema::create('geo_metas', function (Blueprint $table) {
            $table->id();
            $table->morphs('geoable');

            $table->text('ai_summary')->nullable();
            $table->string('short_ai_summary', 500)->nullable();
            $table->json('key_takeaways')->nullable();
            $table->json('highlights')->nullable();

            $table->json('references')->nullable();   // [{title, url}]
            $table->json('citation_urls')->nullable();

            $table->json('entities')->nullable();      // {people:[], companies:[], products:[], locations:[], events:[], topics:[]}

            $table->foreignId('reviewer_author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->date('reviewed_date')->nullable();
            $table->date('content_updated_date')->nullable();

            $table->json('evidence_links')->nullable();
            $table->text('experience_signal')->nullable();
            $table->text('expertise_signal')->nullable();
            $table->text('authority_signal')->nullable();
            $table->text('trust_signal')->nullable();

            $table->json('pros')->nullable();
            $table->json('cons')->nullable();

            $table->boolean('speakable_enabled')->default(false);
            $table->json('speakable_selectors')->nullable();

            $table->timestamps();

            $table->unique(['geoable_type', 'geoable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_metas');
    }
};
