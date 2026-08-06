<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('designation')->nullable();
            $table->unsignedSmallInteger('experience_years')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable(); // {twitter, linkedin, facebook, instagram}
            $table->json('skills')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
