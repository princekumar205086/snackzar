<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('cluster')->nullable()->after('category');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->json('table_of_contents')->nullable()->after('content');
            $table->json('faq_items')->nullable()->after('table_of_contents');
            $table->string('canonical_url')->nullable()->after('meta_description');
            $table->json('article_schema')->nullable()->after('canonical_url');
            $table->json('breadcrumb_schema')->nullable()->after('article_schema');
            $table->json('faq_schema')->nullable()->after('breadcrumb_schema');
            $table->unsignedSmallInteger('content_word_count')->default(0)->after('views_count');

            $table->index(['cluster', 'status']);
            $table->index(['is_featured', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['cluster', 'status']);
            $table->dropIndex(['is_featured', 'published_at']);

            $table->dropColumn([
                'cluster',
                'is_featured',
                'table_of_contents',
                'faq_items',
                'canonical_url',
                'article_schema',
                'breadcrumb_schema',
                'faq_schema',
                'content_word_count',
            ]);
        });
    }
};
