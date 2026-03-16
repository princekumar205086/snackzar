<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('blog_post_id')->nullable()->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->string('cluster')->index();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('image_source')->nullable();
            $table->string('image_alt')->nullable();
            $table->json('table_of_contents')->nullable();
            $table->json('faq_items')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('content_word_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['cluster', 'is_featured']);
        });

        Schema::create('post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('source');
            $table->string('url');
            $table->string('alt_text');
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['post_id', 'is_featured']);
        });

        Schema::create('post_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('meta_title');
            $table->text('meta_description');
            $table->string('canonical_url');
            $table->json('article_schema')->nullable();
            $table->json('breadcrumb_schema')->nullable();
            $table->json('faq_schema')->nullable();
            $table->timestamps();

            $table->unique('post_id');
            $table->index('canonical_url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_meta');
        Schema::dropIfExists('post_images');
        Schema::dropIfExists('posts');
    }
};
