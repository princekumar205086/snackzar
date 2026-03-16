<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostMeta;
use App\Models\User;
use App\Modules\Shared\Services\BlogContentGenerationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogSeoContentSeeder extends Seeder
{
    public function run(): void
    {
        $generator = app(BlogContentGenerationService::class);
        $articles = $generator->generateArticles(320);

        $authorId = User::query()->where('email', 'admin@snackzar.com')->value('id')
            ?? User::query()->value('id');

        if (!$authorId) {
            $this->command?->warn('No users found. Create a user/admin before running BlogSeoContentSeeder.');
            return;
        }

        DB::transaction(function () use ($articles, $authorId) {
            foreach ($articles as $article) {
                $blogPost = BlogPost::query()->updateOrCreate(
                    ['slug' => $article['slug']],
                    [
                        'author_id' => $authorId,
                        'title' => $article['title'],
                        'excerpt' => $article['excerpt'],
                        'content' => $article['content'],
                        'featured_image' => $article['featured_image'],
                        'category' => $article['category'],
                        'cluster' => $article['cluster'],
                        'tags' => $article['tags'],
                        'status' => $article['status'],
                        'is_featured' => $article['is_featured'],
                        'meta_title' => $article['meta_title'],
                        'meta_description' => $article['meta_description'],
                        'meta_keywords' => $article['meta_keywords'],
                        'canonical_url' => $article['canonical_url'],
                        'article_schema' => $article['article_schema'],
                        'breadcrumb_schema' => $article['breadcrumb_schema'],
                        'faq_schema' => $article['faq_schema'],
                        'table_of_contents' => $article['table_of_contents'],
                        'faq_items' => $article['faq_items'],
                        'published_at' => $article['published_at'],
                        'views_count' => $article['views_count'],
                        'content_word_count' => $article['content_word_count'],
                    ]
                );

                $post = Post::query()->updateOrCreate(
                    ['slug' => $article['slug']],
                    [
                        'author_id' => $authorId,
                        'blog_post_id' => $blogPost->id,
                        'title' => $article['title'],
                        'category' => $article['category'],
                        'cluster' => $article['cluster'],
                        'excerpt' => $article['excerpt'],
                        'content' => $article['content'],
                        'featured_image' => $article['featured_image'],
                        'image_source' => $article['image_source'],
                        'image_alt' => $article['image_alt'],
                        'table_of_contents' => $article['table_of_contents'],
                        'faq_items' => $article['faq_items'],
                        'is_featured' => $article['is_featured'],
                        'status' => $article['status'],
                        'published_at' => $article['published_at'],
                        'content_word_count' => $article['content_word_count'],
                    ]
                );

                PostImage::query()->updateOrCreate(
                    [
                        'post_id' => $post->id,
                        'sort_order' => 1,
                    ],
                    [
                        'source' => $article['image_source'],
                        'url' => $article['featured_image'],
                        'alt_text' => $article['image_alt'],
                        'is_featured' => true,
                    ]
                );

                PostMeta::query()->updateOrCreate(
                    ['post_id' => $post->id],
                    [
                        'meta_title' => $article['meta_title'],
                        'meta_description' => $article['meta_description'],
                        'canonical_url' => $article['canonical_url'],
                        'article_schema' => $article['article_schema'],
                        'breadcrumb_schema' => $article['breadcrumb_schema'],
                        'faq_schema' => $article['faq_schema'],
                    ]
                );
            }
        });

        $this->command?->info('Blog SEO seeding complete: ' . count($articles) . ' articles generated and synced.');
    }
}
