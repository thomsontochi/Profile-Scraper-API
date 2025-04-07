<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('name')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('cover_url')->nullable();
            $table->integer('posts_count')->default(0);
            $table->integer('photos_count')->default(0);
            $table->integer('videos_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('followers_count')->default(0);
            $table->json('social_links')->nullable();
            $table->timestamp('last_post_at')->nullable();
            $table->timestamp('last_scraped_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            
            // Full-text search index
            $table->fullText(['username', 'name', 'bio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
