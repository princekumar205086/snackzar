<?php

namespace App\Modules\Shared\Services;

/**
 * RobotsTxt Service
 * 
 * Dynamically generates robots.txt that:
 * - Allows crawling of products, categories, blog, and SEO pages
 * - Disallows crawling of admin, API, and login pages
 * - Sets crawl delay for efficient indexing
 * - Specifies sitemap locations
 */
class RobotsTxtService
{
    /**
     * Generate robots.txt content
     */
    public function generate(): string
    {
        $domain = config('snackzar.seo.canonical_domain');
        $content = <<<EOT
# Snackzar robots.txt
# Generated dynamically for optimal SEO crawling

User-agent: *
Allow: /
Allow: /shop/
Allow: /products/
Allow: /category/
Allow: /blog/
Allow: /makhana-in-*
Allow: /buy-makhana-online-*
Allow: /seo/k/*

# Disallow admin and authentication
Disallow: /admin/
Disallow: /dashboard/
Disallow: /api/
Disallow: /admin-login
Disallow: /login
Disallow: /register
Disallow: /password-reset

# Disallow temporary pages
Disallow: /checkout/
Disallow: /cart/
Disallow: /compare/
Disallow: /wishlist/
Disallow: /account/

# Disallow search functionality
Disallow: /search
Disallow: /q=

# Disallow duplicate content patterns
Disallow: /*?sort=
Disallow: /*?page=

# Crawl-delay for respectful crawling
Crawl-delay: 1

# Sitemaps
Sitemap: https://{$domain}/sitemap.xml
Sitemap: https://{$domain}/sitemap-index.xml
Sitemap: https://{$domain}/sitemap-main.xml
Sitemap: https://{$domain}/sitemap-products.xml
Sitemap: https://{$domain}/sitemap-categories.xml
Sitemap: https://{$domain}/sitemap-blog.xml
Sitemap: https://{$domain}/sitemap-cities.xml

# Google-specific rules
User-agent: Googlebot
Allow: /
Crawl-delay: 0

# Bing-specific rules
User-agent: Bingbot
Allow: /
Crawl-delay: 1

# Block bad bots
User-agent: AhrefsBot
Disallow: /

User-agent: SemrushBot
Disallow: /

User-agent: MJ12bot
Disallow: /

User-agent: DotBot
Disallow: /
EOT;

        return $content;
    }

    /**
     * Get robots.txt content as file
     */
    public function getAsFile(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        echo $this->generate();
    }
}
