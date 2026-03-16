<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SEO Cities Table - stores all target cities and districts
        Schema::create('seo_cities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->string('type')->comment('district, city, global'); // district, city, global
            $table->string('state')->nullable()->comment('For Indian cities');
            $table->string('country')->index();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('population')->nullable();
            $table->text('description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(50)->comment('1-100 for crawl priority');
            $table->timestamps();
            $table->index(['country', 'type']);
            $table->index(['is_active', 'type']);
        });

        // SEO Keywords Table - stores keyword universe (up to 250k)
        Schema::create('seo_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->index();
            $table->string('slug')->unique();
            $table->text('variations')->nullable()->comment('JSON array of keyword variations');
            $table->integer('search_volume')->nullable();
            $table->integer('keyword_difficulty')->nullable()->comment('0-100 scale');
            $table->text('intent')->nullable()->comment('commercial, informational, navigational, transactional');
            $table->text('description')->nullable();
            $table->integer('page_count')->default(0)->comment('Count of landing pages using this keyword');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(50)->comment('1-100 for generation priority');
            $table->timestamps();
            $table->index(['is_active', 'priority']);
        });

        // SEO City-Keyword Combinations Table
        Schema::create('seo_city_keyword', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_city_id')->constrained()->onDelete('cascade');
            $table->foreignId('seo_keyword_id')->constrained()->onDelete('cascade');
            $table->string('url_slug')->unique();
            $table->text('page_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->longText('content_outline')->nullable()->comment('JSON structured outline for content');
            $table->integer('content_word_count')->nullable();
            $table->boolean('has_faq')->default(false);
            $table->boolean('has_schema')->default(false);
            $table->boolean('is_indexed')->default(true)->index();
            $table->dateTime('last_indexed_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->unique(['seo_city_id', 'seo_keyword_id']);
            $table->index(['url_slug']);
        });

        // SEO Landing Pages Table - stores dynamically generated page content
        Schema::create('seo_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_city_keyword_id')->nullable()->constrained('seo_city_keyword')->onDelete('cascade');
            $table->string('type')->comment('city, keyword, category, product'); // city, keyword, combination
            $table->string('page_name');
            $table->string('url_path')->unique()->index();
            $table->string('page_title');
            $table->text('meta_description');
            $table->text('h1_heading');
            $table->longText('content')->nullable();
            $table->json('faq')->nullable()->comment('FAQ schema data');
            $table->json('breadcrumbs')->nullable()->comment('Breadcrumb schema');
            $table->json('internal_links')->nullable()->comment('Array of internal links');
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_indexed')->default(true)->index();
            $table->dateTime('indexed_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->index(['type', 'is_active']);
        });

        // SEO Internal Links Table - stores internal linking graph
        Schema::create('seo_internal_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_page_id')->constrained('seo_landing_pages')->onDelete('cascade');
            $table->foreignId('target_page_id')->constrained('seo_landing_pages')->onDelete('cascade');
            $table->string('anchor_text');
            $table->string('link_type')->comment('primary, related, breadcrumb'); // primary, related, breadcrumb
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['source_page_id', 'target_page_id', 'link_type'], 'seo_links_src_tgt_type_uq');
        });

        // SEO Location Detection Table - for near-me functionality
        Schema::create('seo_location_detections', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->index();
            $table->foreignId('seo_city_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->integer('accuracy')->nullable()->comment('Accuracy radius in km');
            $table->timestamps();
            $table->index(['ip_address', 'created_at']);
        });

        // SEO Breadcrumb Template Table
        Schema::create('seo_breadcrumb_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->string('pattern')->unique();
            $table->json('breadcrumbs')->comment('Array of breadcrumb items with patterns');
            $table->json('schema')->comment('JSON-LD schema template');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_breadcrumb_templates');
        Schema::dropIfExists('seo_location_detections');
        Schema::dropIfExists('seo_internal_links');
        Schema::dropIfExists('seo_landing_pages');
        Schema::dropIfExists('seo_city_keyword');
        Schema::dropIfExists('seo_keywords');
        Schema::dropIfExists('seo_cities');
    }
};
