<?php

namespace App\Modules\Shared\Services;

use App\Models\BlogPost;
use App\Models\SeoKeyword;

/**
 * Blog SEO System Service
 * 
 * Generates blog content optimized for SEO:
 * - Keyword-focused articles
 * - Natural link insertion
 * - Readability scoring
 * - Internal linking suggestions
 */
class BlogSeoService
{
    protected array $contentTemplates = [
        'benefits' => 'Discover {count} surprising benefits of {keyword}',
        'guide' => 'Complete guide to {keyword}: Everything you need to know',
        'tips' => '{count} expert tips for {keyword} that actually work',
        'nutrition' => '{keyword} nutrition facts and health benefits',
        'recipe' => 'Easy {keyword} {recipe_type} recipe - Healthy & delicious',
        'comparison' => '{keyword} vs {comparison}: Which is better?',
        'myths' => '{count} common myths about {keyword} debunked',
    ];

    /**
     * Generate blog content outline for keyword
     */
    public function generateContentOutline(SeoKeyword $keyword, string $template = 'benefits'): array
    {
        $template = $template ?: array_rand($this->contentTemplates);
        $title = str_replace('{keyword}', $keyword->keyword, $this->contentTemplates[$template]);
        $title = str_replace('{count}', rand(5, 10), $title);

        return [
            'template' => $template,
            'title' => $title,
            'meta_description' => $this->generateMetaDescription($keyword, $title),
            'slug' => $this->generateSlug($keyword),
            'sections' => $this->generateSections($keyword, $template),
            'internal_links' => $this->suggestInternalLinks($keyword),
            'featured_image' => $this->generateImagePrompt($keyword),
            'estimated_read_time' => rand(3, 8) . ' min read',
            'target_word_count' => rand(2000, 3000),
        ];
    }

    /**
     * Generate meta description for blog post
     */
    protected function generateMetaDescription(SeoKeyword $keyword, string $title): string
    {
        $descriptions = [
            "Learn about {$keyword->keyword}. {$title}. Expert tips and insights.",
            "Everything you need to know about {$keyword->keyword}. Read our comprehensive guide.",
            "{$title}. Discover practical advice and benefits of {$keyword->keyword}.",
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Generate SEO-friendly slug
     */
    protected function generateSlug(SeoKeyword $keyword): string
    {
        return str()->slug($keyword->keyword) . '-guide';
    }

    /**
     * Generate section outlines
     */
    protected function generateSections(SeoKeyword $keyword, string $template): array
    {
        $baseSection = [
            [
                'h2' => 'Introduction',
                'content_hints' => 'Hook, relevance, what reader will learn',
            ],
            [
                'h2' => "What is {$keyword->keyword}?",
                'content_hints' => 'Definition, history, why it matters',
            ],
        ];

        $bodySection = match ($template) {
            'benefits' => [
                [
                    'h2' => 'Key Benefits',
                    'h3s' => ['Health boost', 'Energy improvement', 'Better digestion', 'Weight management'],
                ],
                [
                    'h2' => 'Scientific Evidence',
                    'content_hints' => 'Research studies, data, expert opinions',
                ],
            ],
            'guide' => [
                [
                    'h2' => 'Getting Started',
                    'content_hints' => 'Step-by-step how-to section',
                ],
                [
                    'h2' => 'Common Mistakes',
                    'content_hints' => 'What to avoid, best practices',
                ],
                [
                    'h2' => 'Pro Tips',
                    'content_hints' => 'Advanced techniques, insider knowledge',
                ],
            ],
            'nutrition' => [
                [
                    'h2' => 'Nutritional Content',
                    'content_hints' => 'Breakdown: proteins, fats, carbs, vitamins',
                ],
                [
                    'h2' => 'Health Benefits',
                    'content_hints' => 'How each nutrient helps your body',
                ],
                [
                    'h2' => 'Daily Recommended Intake',
                    'content_hints' => 'Portion sizes and frequency',
                ],
            ],
            default => [
                [
                    'h2' => 'Main Points',
                    'content_hints' => 'Core information and details',
                ],
            ],
        };

        $conclusionSection = [
            [
                'h2' => 'Conclusion',
                'content_hints' => 'Summary, key takeaways, call-to-action',
            ],
        ];

        return array_merge($baseSection, $bodySection, $conclusionSection);
    }

    /**
     * Suggest internal links for the blog post
     */
    protected function suggestInternalLinks(SeoKeyword $keyword): array
    {
        return [
            [
                'type' => 'product',
                'text' => "Shop {$keyword->keyword}",
                'url' => route('products.index') . '?search=' . urlencode($keyword->keyword),
            ],
            [
                'type' => 'related_blog',
                'text' => 'More health tips',
                'url' => route('blog.index'),
            ],
            [
                'type' => 'category',
                'text' => 'Healthy snacks',
                'url' => route('category.show', 'healthy-snacks'),
            ],
        ];
    }

    /**
     * Generate image prompt for featured image
     */
    protected function generateImagePrompt(SeoKeyword $keyword): string
    {
        return "Professional product photography of {$keyword->keyword}, clean white background, natural lighting, styled for food blog, 1200x800px";
    }

    /**
     * Calculate readability score
     */
    public function calculateReadability(BlogPost $post): array
    {
        $content = $post->content;
        $words = str_word_count(strip_tags($content));
        $sentences = count(preg_split('/[.!?]+/', $content)) - 1;
        $paragraphs = count(preg_split('/<p>/', $content)) - 1;

        // Flesch-Kincaid Grade Level
        $grade = 0.39 * ($words / max($sentences, 1)) + 
                11.8 * (str_word_count(preg_replace('/\b\w{1,2}\b/', '', $content)) / max($words, 1)) - 15.59;

        return [
            'word_count' => $words,
            'reading_time_minutes' => ceil($words / 200),
            'average_sentence_length' => round($words / max($sentences, 1), 1),
            'flesch_kincaid_grade' => max(0, round($grade, 1)),
            'readability_score' => $this->getReadabilityScore($grade),
            'metrics' => [
                'sentences' => $sentences,
                'paragraphs' => $paragraphs,
                'average_paragraph_words' => round($words / max($paragraphs, 1), 1),
            ],
        ];
    }

    /**
     * Get readability score label
     */
    protected function getReadabilityScore(float $grade): string
    {
        return match (true) {
            $grade < 6 => 'Very Easy (Grade 5)',
            $grade < 9 => 'Easy (Grade 6-8)',
            $grade < 12 => 'Normal (Grade 9-12)',
            $grade < 14 => 'Fairly Difficult (College)',
            default => 'Difficult (Graduate)',
        };
    }

    /**
     * Get SEO score for blog post
     */
    public function calculateSeoScore(BlogPost $post): array
    {
        $score = 0;
        $details = [];

        // Title optimization (0-10 points)
        if (strlen($post->title) >= 30 && strlen($post->title) <= 60) {
            $score += 10;
            $details['title'] = '✓ Optimal length (30-60 chars)';
        } else {
            $details['title'] = '✗ Title should be 30-60 characters';
        }

        // Meta description (0-10 points)
        if (strlen($post->seoMeta?->meta_description ?? '') >= 120 && 
            strlen($post->seoMeta?->meta_description ?? '') <= 160) {
            $score += 10;
            $details['description'] = '✓ Optimal length (120-160 chars)';
        } else {
            $details['description'] = '✗ Description should be 120-160 characters';
        }

        // Content length (0-15 points)
        $wordCount = str_word_count(strip_tags($post->content));
        if ($wordCount >= 1500) {
            $score += 15;
            $details['content_length'] = "✓ Good length ({$wordCount} words)";
        } elseif ($wordCount >= 1000) {
            $score += 10;
            $details['content_length'] = "⚠ Could be longer ({$wordCount} words, target 1500+)";
        } else {
            $details['content_length'] = "✗ Too short ({$wordCount} words, target 1500+)";
        }

        // Headings (0-15 points)
        $h2Count = substr_count($post->content, '<h2>');
        $h3Count = substr_count($post->content, '<h3>');
        if ($h2Count >= 3 && $h3Count >= 3) {
            $score += 15;
            $details['headings'] = "✓ Good heading structure ({$h2Count} H2, {$h3Count} H3)";
        } elseif ($h2Count >= 2) {
            $score += 10;
            $details['headings'] = "⚠ Could improve heading structure";
        } else {
            $details['headings'] = "✗ Add more headings for structure";
        }

        // Keyword usage (0-20 points)
        if ($post->seoMeta && $post->seoMeta->meta_keywords) {
            $keywords = explode(',', $post->seoMeta->meta_keywords);
            $keywordDensity = 0;
            
            foreach ($keywords as $keyword) {
                $count = substr_count(strtolower($post->content), strtolower(trim($keyword)));
                $keywordDensity += $count;
            }
            
            if ($keywordDensity >= 5 && $keywordDensity <= 20) {
                $score += 20;
                $details['keywords'] = "✓ Good keyword density ({$keywordDensity} mentions)";
            } else {
                $score += 10;
                $details['keywords'] = "⚠ Keyword density could be optimized";
            }
        }

        // Internal links (0-10 points)
        $internalLinks = substr_count($post->content, 'href="');
        if ($internalLinks >= 3) {
            $score += 10;
            $details['internal_links'] = "✓ Good number of internal links ({$internalLinks})";
        } elseif ($internalLinks >= 1) {
            $score += 5;
            $details['internal_links'] = "⚠ Add more internal links";
        } else {
            $details['internal_links'] = "✗ Add internal links for better SEO";
        }

        // Featured image (0-10 points)
        if ($post->featured_image) {
            $score += 10;
            $details['featured_image'] = "✓ Featured image present";
        } else {
            $details['featured_image'] = "✗ Add a featured image";
        }

        // Canonical URL (0-10 points)
        if ($post->seoMeta?->canonical_url) {
            $score += 10;
            $details['canonical'] = "✓ Canonical URL set";
        } else {
            $details['canonical'] = "✗ Set canonical URL";
        }

        return [
            'score' => round(($score / 100) * 100),
            'details' => $details,
            'recommendations' => $this->getSeoRecommendations($details),
        ];
    }

    /**
     * Generate SEO recommendations
     */
    protected function getSeoRecommendations(array $details): array
    {
        $recommendations = [];
        
        foreach ($details as $key => $value) {
            if (str_starts_with($value, '✗')) {
                $recommendations[] = $value;
            } elseif (str_starts_with($value, '⚠')) {
                $recommendations[] = $value;
            }
        }
        
        return $recommendations;
    }
}
