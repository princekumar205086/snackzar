<?php

namespace App\Modules\Shared\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BlogContentGenerationService
{
    public const TARGET_COUNT = 320;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function generateArticles(int $targetCount = self::TARGET_COUNT): array
    {
        $clusters = $this->clusterBlueprints();
        $articles = [];
        $usedSlugs = [];
        $publishDate = now()->subDays(320);

        foreach ($clusters as $clusterName => $cluster) {
            $itemsPerCluster = (int) $cluster['count'];

            for ($i = 0; $i < $itemsPerCluster; $i++) {
                $keyword = $cluster['keywords'][$i % count($cluster['keywords'])];
                $angle = $cluster['angles'][$i % count($cluster['angles'])];
                $cityHint = $this->cityHints()[$i % count($this->cityHints())];

                $title = $this->buildTitle($clusterName, $keyword, $angle, $cityHint, $i);
                $slug = $this->uniqueSlug(Str::slug($title), $usedSlugs);
                $canonicalUrl = rtrim(config('app.url', 'https://snackzar.com'), '/') . '/blog/' . $slug;
                $toc = $this->buildToc($keyword, $angle);
                $faqItems = $this->buildFaqItems($keyword, $cityHint);
                $image = $this->pickOpenSourceImage($keyword, $clusterName, $i);
                $internalLinks = $this->internalLinks($keyword, $clusterName, $cityHint);
                $content = $this->buildContent($title, $keyword, $angle, $clusterName, $cityHint, $toc, $faqItems, $internalLinks);

                $wordCount = str_word_count(strip_tags($content));
                $metaTitle = $this->metaTitle($title);
                $metaDescription = $this->metaDescription($keyword, $clusterName, $cityHint);
                $featured = $this->isFeaturedArticle($title, $clusterName, $i);

                $articles[] = [
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $this->excerpt($keyword, $clusterName, $cityHint),
                    'content' => $content,
                    'category' => $cluster['category'],
                    'cluster' => $clusterName,
                    'tags' => $this->tags($keyword, $clusterName, $cityHint),
                    'featured_image' => $image['url'],
                    'image_source' => $image['source'],
                    'image_alt' => $image['alt'],
                    'table_of_contents' => $toc,
                    'faq_items' => $faqItems,
                    'meta_title' => $metaTitle,
                    'meta_description' => $metaDescription,
                    'meta_keywords' => implode(', ', $this->metaKeywords($keyword, $clusterName, $cityHint)),
                    'canonical_url' => $canonicalUrl,
                    'article_schema' => $this->articleSchema($title, $metaDescription, $canonicalUrl, $image['url'], $publishDate->toAtomString()),
                    'breadcrumb_schema' => $this->breadcrumbSchema($title, $canonicalUrl),
                    'faq_schema' => $this->faqSchema($faqItems),
                    'is_featured' => $featured,
                    'status' => 'published',
                    'published_at' => $publishDate->copy(),
                    'views_count' => random_int(40, 1200),
                    'content_word_count' => $wordCount,
                ];

                $publishDate->addDay();
            }
        }

        if (count($articles) > $targetCount) {
            return array_slice($articles, 0, $targetCount);
        }

        return $articles;
    }

    private function clusterBlueprints(): array
    {
        return [
            'Makhana Benefits' => [
                'count' => 40,
                'category' => 'Makhana Benefits',
                'keywords' => [
                    'makhana benefits', 'fox nuts benefits', 'lotus seeds benefits', 'makhana for heart health',
                    'makhana for diabetes', 'makhana for kids', 'makhana for seniors', 'anti aging snacks',
                ],
                'angles' => ['complete guide', 'science backed analysis', 'daily routine', 'expert tips', 'myth vs fact'],
            ],
            'Makhana Nutrition' => [
                'count' => 40,
                'category' => 'Makhana Nutrition',
                'keywords' => [
                    'makhana nutrition facts', 'fox nuts calories', 'protein in makhana', 'fiber rich snacks',
                    'makhana micronutrients', 'makhana vs nuts nutrition', 'makhana glycemic index', 'lotus seeds minerals',
                ],
                'angles' => ['nutrition breakdown', 'comparison table', 'meal planning', 'dietitian perspective', 'evidence summary'],
            ],
            'Healthy Snacks' => [
                'count' => 40,
                'category' => 'Healthy Snacks',
                'keywords' => [
                    'healthy snacks in delhi', 'healthy evening snacks', 'low calorie snacks india', 'best healthy snacks in india',
                    'healthy snacks for office', 'healthy snacks for students', 'healthy snacks for travel', 'snack alternatives to chips',
                ],
                'angles' => ['buyer guide', 'city guide', 'beginner plan', 'budget friendly options', 'top list'],
            ],
            'Indian Traditional Snacks' => [
                'count' => 40,
                'category' => 'Indian Traditional Snacks',
                'keywords' => [
                    'traditional indian snacks', 'bihari snacks', 'regional indian namkeen', 'festive indian snacks',
                    'roasted indian snacks', 'homemade indian snacks', 'best snacks in patna', 'indian tea time snacks',
                ],
                'angles' => ['culture story', 'heritage guide', 'regional comparison', 'festival edition', 'authentic picks'],
            ],
            'Weight Loss Snacks' => [
                'count' => 40,
                'category' => 'Weight Loss Snacks',
                'keywords' => [
                    'is makhana good for weight loss', 'weight loss snacks india', 'high fiber snacks for weight loss',
                    'night snacks for weight loss', 'low carb snacks india', 'satiety snacks', 'portion control snacks', 'metabolism friendly snacks',
                ],
                'angles' => ['7 day plan', 'practical guide', 'coach advice', 'mistakes to avoid', 'top picks'],
            ],
            'Protein Snacks' => [
                'count' => 40,
                'category' => 'Protein Snacks',
                'keywords' => [
                    'protein snacks india', 'high protein vegetarian snacks', 'post workout snacks',
                    'protein rich fox nuts', 'snacks for gym beginners', 'muscle recovery snacks', 'plant protein snacks', 'protein snack combos',
                ],
                'angles' => ['sports nutrition', 'comparison guide', 'daily meal integration', 'athlete checklist', 'evidence based tips'],
            ],
            'Bihar Food Culture' => [
                'count' => 40,
                'category' => 'Bihar Food Culture',
                'keywords' => [
                    'bihar food culture', 'mithila cuisine', 'makhana in bihar economy', 'traditional bihari snacks',
                    'patna street snacks', 'festival foods of bihar', 'rural food heritage bihar', 'bihar culinary history',
                ],
                'angles' => ['culture deep dive', 'history timeline', 'farmer perspective', 'city food trail', 'heritage spotlight'],
            ],
            'Makhana Recipes' => [
                'count' => 40,
                'category' => 'Makhana Recipes',
                'keywords' => [
                    'makhana recipes', 'roasted makhana recipe', 'makhana chaat recipe', 'makhana kheer recipe',
                    'savory fox nuts recipes', 'healthy fox nuts recipe', 'kids makhana recipe', 'quick evening makhana snacks',
                ],
                'angles' => ['step by step recipe', 'quick recipe', 'family recipe', 'festival recipe', 'healthy recipe'],
            ],
        ];
    }

    private function buildTitle(string $cluster, string $keyword, string $angle, string $cityHint, int $index): string
    {
        $titlePatterns = [
            'Makhana Benefits' => [
                '10 Health Benefits of {keyword} for Indian Families',
                'How {keyword} Supports Better Energy and Digestion',
                '{keyword}: Complete {angle} for Daily Snacking',
                'Is {keyword} Worth Adding to Your Diet in {city}?',
            ],
            'Makhana Nutrition' => [
                '{keyword}: Full Nutrition Profile with Serving Guide',
                'Calories, Protein and Fiber in {keyword}: A Practical Breakdown',
                '{keyword} Explained: {angle} for Smarter Snacking',
                'Nutritionist View: {keyword} for Busy Professionals in {city}',
            ],
            'Healthy Snacks' => [
                'Best {keyword}: What to Buy and Why',
                '{keyword} Checklist: {angle} for Better Eating',
                'Top Healthy Snack Ideas for Work, Travel and Home in {city}',
                'Healthy Snacking in India: {keyword} You Can Trust',
            ],
            'Indian Traditional Snacks' => [
                '{keyword}: Regional Favorites and Modern Healthy Swaps',
                'From Patna to Delhi: {keyword} You Should Try',
                'A Practical {angle} to {keyword}',
                'How {keyword} Keeps Indian Food Heritage Alive in {city}',
            ],
            'Weight Loss Snacks' => [
                '{keyword}: A Practical {angle} That Actually Works',
                'Can {keyword} Help with Weight Management? Expert View',
                'Smart Portion Guide for {keyword} in Busy City Life',
                'Weight Loss Snacks for Indians: Why {keyword} Stands Out',
            ],
            'Protein Snacks' => [
                '{keyword}: Complete {angle} for Active Lifestyles',
                'Protein Snack Planning with {keyword} for Indian Diets',
                'Post Workout Choices: Is {keyword} a Good Fit?',
                'Best Protein Snack Strategies in {city} with {keyword}',
            ],
            'Bihar Food Culture' => [
                '{keyword}: Stories, Traditions and Local Pride',
                'Understanding {keyword} Through a {angle}',
                'How Bihar Built a Legacy Around {keyword}',
                '{keyword} in {city}: Culture, Taste and Identity',
            ],
            'Makhana Recipes' => [
                '{keyword}: Easy {angle} for Weekday Meals',
                'How to Make {keyword} at Home Without Fuss',
                '{keyword} for Families: Simple Steps and Flavor Tips',
                'Quick {keyword} for Healthy Evening Snacking in {city}',
            ],
        ];

        $pattern = Arr::random($titlePatterns[$cluster]);
        $title = str_replace(['{keyword}', '{angle}', '{city}'], [$this->titleCase($keyword), $this->titleCase($angle), $cityHint], $pattern);

        if (($index % 9) === 0) {
            $title .= ' | Snackzar';
        }

        return trim($title);
    }

    private function buildToc(string $keyword, string $angle): array
    {
        return [
            ['id' => 'overview', 'label' => 'Overview'],
            ['id' => 'what-is', 'label' => 'What Is ' . $this->titleCase($keyword) . '?'],
            ['id' => 'why-important', 'label' => 'Why It Matters'],
            ['id' => 'nutrition', 'label' => 'Nutrition and Practical Value'],
            ['id' => 'planning', 'label' => 'How to Use It in Daily Meals'],
            ['id' => 'mistakes', 'label' => 'Common Mistakes to Avoid'],
            ['id' => 'city-choices', 'label' => 'Buying Tips for Indian Cities'],
            ['id' => 'faq', 'label' => 'Frequently Asked Questions'],
            ['id' => 'conclusion', 'label' => 'Conclusion'],
        ];
    }

    private function buildFaqItems(string $keyword, string $cityHint): array
    {
        return [
            [
                'question' => 'Is ' . $keyword . ' good for daily snacking?',
                'answer' => 'Yes, when eaten in sensible portions with low oil and moderate seasoning, it works well as a daily snack option.',
            ],
            [
                'question' => 'How much ' . $keyword . ' should I eat in one serving?',
                'answer' => 'A practical serving for most adults is around 25-35 grams, depending on activity level and total meal plan.',
            ],
            [
                'question' => 'Where can I buy quality makhana in ' . $cityHint . '?',
                'answer' => 'Check trusted online stores with freshness date, clear ingredient list, and transparent sourcing details from Bihar.',
            ],
            [
                'question' => 'Can children and seniors consume ' . $keyword . '?',
                'answer' => 'Generally yes. Choose light seasoning and age-appropriate portions, especially for younger children and older adults.',
            ],
            [
                'question' => 'How should I store ' . $keyword . ' to keep it crunchy?',
                'answer' => 'Store in an airtight container away from moisture and direct heat; consume quickly after opening for best taste.',
            ],
        ];
    }

    private function buildContent(
        string $title,
        string $keyword,
        string $angle,
        string $cluster,
        string $cityHint,
        array $toc,
        array $faqItems,
        array $internalLinks
    ): string {
        $tocHtml = $this->tocHtml($toc);
        $faqHtml = $this->faqHtml($faqItems);
        $linkHtml = $this->internalLinksHtml($internalLinks);

        $sections = [
            '<h2 id="overview">Overview</h2>' . $this->paragraphSet($keyword, $cluster, $cityHint, 3),
            '<h2 id="what-is">What Is ' . e($this->titleCase($keyword)) . '?</h2>' . $this->paragraphSet($keyword, $cluster, $cityHint, 3),
            '<h2 id="why-important">Why It Matters for Indian Consumers</h2>' . $this->paragraphSet($keyword, $cluster, $cityHint, 3),
            '<h2 id="nutrition">Nutrition and Practical Value</h2>' . $this->paragraphSet($keyword, $cluster, $cityHint, 3) . $this->nutritionTable(),
            '<h2 id="planning">How to Use It in Daily Meal Planning</h2>' . $this->paragraphSet($keyword, $cluster, $cityHint, 3),
            '<h2 id="mistakes">Common Mistakes to Avoid</h2>' . $this->mistakesList($keyword),
            '<h2 id="city-choices">Buying Tips for ' . e($cityHint) . ' and Other Cities</h2>' . $this->paragraphSet($keyword, $cluster, $cityHint, 3),
            '<h2 id="faq">Frequently Asked Questions</h2>' . $faqHtml,
            '<h2 id="internal-links">Useful Internal Resources</h2>' . $linkHtml,
            '<h2 id="conclusion">Conclusion</h2>' . $this->conclusionParagraphs($keyword, $angle, $cluster, $cityHint),
        ];

        return '<h1>' . e($title) . '</h1>'
            . '<p>This detailed guide from Snackzar explains ' . e($keyword) . ' with practical, India-focused advice for everyday eating decisions.</p>'
            . $tocHtml
            . implode('', $sections);
    }

    private function paragraphSet(string $keyword, string $cluster, string $cityHint, int $count): string
    {
        $paragraphs = [];
        for ($i = 0; $i < $count; $i++) {
            $paragraphs[] = '<p>' . e($this->longParagraph($keyword, $cluster, $cityHint, $i)) . '</p>';
        }

        return implode('', $paragraphs);
    }

    private function longParagraph(string $keyword, string $cluster, string $cityHint, int $index): string
    {
        $openers = [
            'Indian snack habits are evolving quickly, and consumers now compare labels, ingredients, and satiety value before buying.',
            'For shoppers in growing cities, convenience matters, but nutritional quality and ingredient transparency matter even more.',
            'When discussing healthy snacking, context is important: age, activity level, meal timing, and total diet all influence outcomes.',
        ];

        $details = [
            'In the context of ' . $cluster . ', ' . $keyword . ' can be planned as a mid-morning or evening option because it balances taste with better portion control.',
            'Families in ' . $cityHint . ' and similar markets often choose snacks based on shelf stability, roasting quality, and whether seasoning is too salty or oily.',
            'A practical approach is to pair this snack with hydration, fruit, or protein-rich meals so hunger remains stable across the day.',
            'From an SEO perspective, people search with intent such as price, nutrition facts, recipes, and city-based availability; this guide addresses those intents clearly.',
        ];

        $closers = [
            'That is why expert guidance should focus on realistic routines rather than one-size-fits-all diet claims.',
            'Consistent quality checks and mindful serving sizes create better long-term outcomes than short-term restrictive plans.',
            'Choosing trusted brands with clean sourcing and balanced flavor profiles is one of the simplest upgrades consumers can make.',
        ];

        return $openers[$index % count($openers)] . ' '
            . $details[$index % count($details)] . ' '
            . $details[($index + 1) % count($details)] . ' '
            . $closers[$index % count($closers)];
    }

    private function nutritionTable(): string
    {
        return '<table><thead><tr><th>Nutrient</th><th>Typical Range Per 100g</th><th>Why It Helps</th></tr></thead><tbody>'
            . '<tr><td>Protein</td><td>8-10g</td><td>Supports satiety and daily recovery needs.</td></tr>'
            . '<tr><td>Fiber</td><td>7-9g</td><td>Helps digestive comfort and fullness.</td></tr>'
            . '<tr><td>Carbohydrates</td><td>65-75g</td><td>Provides sustained energy when portions are controlled.</td></tr>'
            . '<tr><td>Fat</td><td>0.5-2g</td><td>Naturally low fat in plain, roasted form.</td></tr>'
            . '<tr><td>Magnesium & Potassium</td><td>Moderate</td><td>Useful for electrolyte support and routine wellness.</td></tr>'
            . '</tbody></table>';
    }

    private function mistakesList(string $keyword): string
    {
        $items = [
            'Treating ' . $keyword . ' as unlimited; portion control remains essential.',
            'Buying heavily fried variants with hidden sodium and sugar.',
            'Ignoring freshness date and storage conditions before purchase.',
            'Skipping ingredient checks for flavor coatings and preservatives.',
            'Replacing full meals entirely with snacks instead of balanced planning.',
        ];

        $html = '<ul>';
        foreach ($items as $item) {
            $html .= '<li>' . e($item) . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function conclusionParagraphs(string $keyword, string $angle, string $cluster, string $cityHint): string
    {
        return '<p>' . e($this->titleCase($keyword) . ' remains one of the most practical options in the ' . $cluster . ' category when consumers prioritize ingredient quality, moderate seasoning, and portion discipline.') . '</p>'
            . '<p>' . e('This ' . $angle . ' for Indian audiences is designed to help readers in ' . $cityHint . ' and beyond make smarter snack decisions with confidence.') . '</p>'
            . '<p>' . e('For the best outcomes, combine these recommendations with regular meal timing, hydration, and an active lifestyle.') . '</p>';
    }

    private function tocHtml(array $toc): string
    {
        $html = '<h2>Table of Contents</h2><ol>';
        foreach ($toc as $item) {
            $html .= '<li><a href="#' . e($item['id']) . '">' . e($item['label']) . '</a></li>';
        }
        $html .= '</ol>';

        return $html;
    }

    private function faqHtml(array $faqItems): string
    {
        $html = '';
        foreach ($faqItems as $item) {
            $html .= '<h3>' . e($item['question']) . '</h3>';
            $html .= '<p>' . e($item['answer']) . '</p>';
        }

        return $html;
    }

    private function internalLinksHtml(array $links): string
    {
        $html = '<ul>';
        foreach ($links as $link) {
            $html .= '<li><a href="' . e($link['url']) . '">' . e($link['label']) . '</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function excerpt(string $keyword, string $cluster, string $cityHint): string
    {
        return 'Expert guide to ' . $keyword . ' in the ' . $cluster . ' cluster, including nutrition insights, practical tips, FAQs, and buying guidance for ' . $cityHint . '.';
    }

    private function tags(string $keyword, string $cluster, string $cityHint): array
    {
        return [
            Str::slug($keyword),
            Str::slug($cluster),
            Str::slug($cityHint),
            'snackzar',
        ];
    }

    private function metaKeywords(string $keyword, string $cluster, string $cityHint): array
    {
        return [
            $keyword,
            'healthy snacks',
            'fox nuts nutrition',
            'bihari snacks',
            strtolower($cluster),
            'best snacks in patna',
            'healthy snacks in delhi',
            'buy makhana in mumbai',
            'snacks in ' . strtolower($cityHint),
        ];
    }

    private function metaTitle(string $title): string
    {
        $raw = str_replace(' | Snackzar', '', $title);
        $candidate = trim($raw) . ' | Snackzar';

        return Str::limit($candidate, 60, '');
    }

    private function metaDescription(string $keyword, string $cluster, string $cityHint): string
    {
        $description = 'Explore ' . $keyword . ' with Snackzar: nutrition, practical tips, FAQ, and smart buying advice in ' . $cityHint . '. Part of our ' . $cluster . ' content cluster.';

        return Str::limit($description, 158, '');
    }

    private function articleSchema(string $title, string $description, string $canonicalUrl, string $imageUrl, string $publishedAt): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'description' => $description,
            'image' => [$imageUrl],
            'datePublished' => $publishedAt,
            'dateModified' => now()->toAtomString(),
            'author' => [
                '@type' => 'Organization',
                'name' => 'Snackzar Editorial Team',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Snackzar',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => rtrim(config('app.url', 'https://snackzar.com'), '/') . '/images/logo.png',
                ],
            ],
            'mainEntityOfPage' => $canonicalUrl,
        ];
    }

    private function breadcrumbSchema(string $title, string $canonicalUrl): array
    {
        $baseUrl = rtrim(config('app.url', 'https://snackzar.com'), '/');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $baseUrl,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Blog',
                    'item' => $baseUrl . '/blog',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $title,
                    'item' => $canonicalUrl,
                ],
            ],
        ];
    }

    private function faqSchema(array $faqItems): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ], $faqItems),
        ];
    }

    private function cityHints(): array
    {
        return ['Patna', 'Delhi', 'Mumbai', 'Bengaluru', 'Pune', 'Kolkata', 'Lucknow', 'Jaipur'];
    }

    private function internalLinks(string $keyword, string $cluster, string $cityHint): array
    {
        $baseUrl = rtrim(config('app.url', 'https://snackzar.com'), '/');

        return [
            [
                'label' => 'Buy Premium Makhana',
                'url' => $baseUrl . '/products?search=makhana',
            ],
            [
                'label' => 'Explore Healthy Snacks Category',
                'url' => $baseUrl . '/category/healthy-snacks',
            ],
            [
                'label' => 'Best Snacks in Patna',
                'url' => $baseUrl . '/blog/best-snacks-in-patna-traditional-and-healthy-picks',
            ],
            [
                'label' => 'Healthy Snacks in Delhi Guide',
                'url' => $baseUrl . '/blog/healthy-snacks-in-delhi-complete-local-guide',
            ],
            [
                'label' => 'Buy Makhana in Mumbai',
                'url' => $baseUrl . '/blog/buy-makhana-in-mumbai-quality-price-and-delivery-guide',
            ],
            [
                'label' => $this->titleCase($cluster) . ' Articles',
                'url' => $baseUrl . '/blog?category=' . urlencode($cluster),
            ],
            [
                'label' => 'Snacking Tips for ' . $cityHint,
                'url' => $baseUrl . '/blog',
            ],
        ];
    }

    private function pickOpenSourceImage(string $keyword, string $cluster, int $index): array
    {
        $keywordQuery = urlencode($keyword . ',snack,healthy food');

        $sources = [
            [
                'source' => 'Unsplash',
                'url' => 'https://source.unsplash.com/1600x900/?' . $keywordQuery,
            ],
            [
                'source' => 'Pexels',
                'url' => 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg?auto=compress&cs=tinysrgb&w=1600',
            ],
            [
                'source' => 'Pixabay',
                'url' => 'https://cdn.pixabay.com/photo/2017/06/02/18/24/salad-2367027_1280.jpg',
            ],
        ];

        $picked = $sources[$index % count($sources)];

        return [
            'source' => $picked['source'],
            'url' => $picked['url'],
            'alt' => $this->titleCase($keyword) . ' - ' . $cluster . ' article image',
        ];
    }

    private function isFeaturedArticle(string $title, string $cluster, int $index): bool
    {
        if (str_contains(strtolower($title), 'benefits of makhana')) {
            return true;
        }

        if (str_contains(strtolower($title), 'nutrition')) {
            return true;
        }

        if (str_contains(strtolower($title), 'best healthy snacks')) {
            return true;
        }

        return $index < 3 && in_array($cluster, ['Makhana Benefits', 'Makhana Nutrition', 'Healthy Snacks'], true);
    }

    private function titleCase(string $value): string
    {
        return Str::title(strtolower($value));
    }

    private function uniqueSlug(string $baseSlug, array &$usedSlugs): string
    {
        $candidate = $baseSlug;
        $suffix = 2;

        while (isset($usedSlugs[$candidate])) {
            $candidate = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        $usedSlugs[$candidate] = true;

        return $candidate;
    }
}
