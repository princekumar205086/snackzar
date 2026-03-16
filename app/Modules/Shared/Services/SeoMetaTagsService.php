<?php

namespace App\Modules\Shared\Services;

/**
 * SEO Meta Tags Service
 * Manages meta tags, canonical links, and hreflang tags for global SEO
 */
class SeoMetaTagsService
{
    private string $title = '';
    private string $description = '';
    private string $canonical = '';
    private string $ogType = 'website';
    private string $ogTitle = '';
    private string $ogDescription = '';
    private string $ogImage = '';
    private string $ogUrl = '';
    private array $hreflangs = [];
    private array $alternates = [];
    private string $robotsDirective = 'index, follow';
    private string $languageCode = 'en-IN';

    public function setTitle(string $title): self
    {
        $this->title = trim($title);
        if (empty($this->ogTitle)) {
            $this->ogTitle = $this->title;
        }
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = trim(preg_replace('/\s+/', ' ', $description));
        if (strlen($this->description) > 160) {
            $this->description = substr($this->description, 0, 157) . '...';
        }
        if (empty($this->ogDescription)) {
            $this->ogDescription = $this->description;
        }
        return $this;
    }

    public function setCanonical(string $url): self
    {
        $this->canonical = trim($url);
        return $this;
    }

    public function setOgType(string $type): self
    {
        $this->ogType = $type;
        return $this;
    }

    public function setOgTitle(string $title): self
    {
        $this->ogTitle = trim($title);
        return $this;
    }

    public function setOgDescription(string $description): self
    {
        $this->ogDescription = trim(preg_replace('/\s+/', ' ', $description));
        return $this;
    }

    public function setOgImage(string $imageUrl): self
    {
        $this->ogImage = trim($imageUrl);
        return $this;
    }

    public function setOgUrl(string $url): self
    {
        $this->ogUrl = trim($url);
        return $this;
    }

    public function addHrefLang(string $lang, string $url): self
    {
        $this->hreflangs[$lang] = trim($url);
        return $this;
    }

    public function setHreflangs(array $hreflangs): self
    {
        $this->hreflangs = array_map('trim', $hreflangs);
        return $this;
    }

    public function addAlternate(string $lang, string $url): self
    {
        $this->alternates[$lang] = trim($url);
        return $this;
    }

    public function setLanguageCode(string $code): self
    {
        $this->languageCode = $code;
        return $this;
    }

    public function setRobotsDirective(string $directive): self
    {
        $this->robotsDirective = $directive;
        return $this;
    }

    public function noindex(): self
    {
        $this->robotsDirective = 'noindex, nofollow';
        return $this;
    }

    public function nofollow(): self
    {
        $this->robotsDirective = 'noindex, nofollow';
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCanonical(): string
    {
        return $this->canonical;
    }

    public function getHreflangs(): array
    {
        return $this->hreflangs;
    }

    public function renderTitleTag(): string
    {
        return !empty($this->title) ? "<title>{$this->escapeHtml($this->title)}</title>" : '';
    }

    public function renderMetaTags(): string
    {
        $html = '';

        // Character set and viewport
        $html .= '<meta charset="UTF-8">' . PHP_EOL;
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;

        // Description
        if (!empty($this->description)) {
            $html .= '<meta name="description" content="' . $this->escapeHtml($this->description) . '">' . PHP_EOL;
        }

        // Keywords
        $html .= '<meta name="keywords" content="makhana, fox nuts, healthy snacks, snacks online, indian snacks, Bihar makhana">' . PHP_EOL;

        // Robots
        $html .= '<meta name="robots" content="' . $this->escapeHtml($this->robotsDirective) . '">' . PHP_EOL;

        // Language
        $html .= '<meta http-equiv="content-language" content="' . $this->languageCode . '">' . PHP_EOL;

        // Author and Creator
        $html .= '<meta name="author" content="' . config('app.name') . '">' . PHP_EOL;
        $html .= '<meta name="creator" content="' . config('app.name') . '">' . PHP_EOL;

        // Canonical
        if (!empty($this->canonical)) {
            $html .= '<link rel="canonical" href="' . $this->escapeHtml($this->canonical) . '">' . PHP_EOL;
        }

        // Hreflangs
        foreach ($this->hreflangs as $lang => $url) {
            $html .= '<link rel="alternate" hreflang="' . $this->escapeHtml($lang) . '" href="' . $this->escapeHtml($url) . '">' . PHP_EOL;
        }

        // Default hreflang tag
        if (!empty($this->canonical)) {
            $html .= '<link rel="alternate" hreflang="x-default" href="' . $this->escapeHtml($this->canonical) . '">' . PHP_EOL;
        }

        // Alternates
        foreach ($this->alternates as $lang => $url) {
            $html .= '<link rel="alternate" hreflang="' . $this->escapeHtml($lang) . '" href="' . $this->escapeHtml($url) . '">' . PHP_EOL;
        }

        return $html;
    }

    public function renderOpenGraphTags(): string
    {
        $html = '';

        $html .= '<meta property="og:type" content="' . $this->ogType . '">' . PHP_EOL;

        if (!empty($this->ogTitle)) {
            $html .= '<meta property="og:title" content="' . $this->escapeHtml($this->ogTitle) . '">' . PHP_EOL;
        }

        if (!empty($this->ogDescription)) {
            $html .= '<meta property="og:description" content="' . $this->escapeHtml($this->ogDescription) . '">' . PHP_EOL;
        }

        if (!empty($this->ogImage)) {
            $html .= '<meta property="og:image" content="' . $this->escapeHtml($this->ogImage) . '">' . PHP_EOL;
            $html .= '<meta property="og:image:type" content="image/png">' . PHP_EOL;
            $html .= '<meta property="og:image:width" content="1200">' . PHP_EOL;
            $html .= '<meta property="og:image:height" content="630">' . PHP_EOL;
        }

        if (!empty($this->ogUrl)) {
            $html .= '<meta property="og:url" content="' . $this->escapeHtml($this->ogUrl) . '">' . PHP_EOL;
        } elseif (!empty($this->canonical)) {
            $html .= '<meta property="og:url" content="' . $this->escapeHtml($this->canonical) . '">' . PHP_EOL;
        }

        $html .= '<meta property="og:site_name" content="' . config('app.name') . '">' . PHP_EOL;
        $html .= '<meta property="og:locale" content="' . str_replace('-', '_', $this->languageCode) . '">' . PHP_EOL;

        return $html;
    }

    public function renderTwitterCardTags(): string
    {
        $html = '';

        $html .= '<meta name="twitter:card" content="summary_large_image">' . PHP_EOL;
        $html .= '<meta name="twitter:site" content="@snackzar">' . PHP_EOL;
        $html .= '<meta name="twitter:creator" content="@snackzar">' . PHP_EOL;

        if (!empty($this->title)) {
            $html .= '<meta name="twitter:title" content="' . $this->escapeHtml($this->title) . '">' . PHP_EOL;
        }

        if (!empty($this->description)) {
            $html .= '<meta name="twitter:description" content="' . $this->escapeHtml($this->description) . '">' . PHP_EOL;
        }

        if (!empty($this->ogImage)) {
            $html .= '<meta name="twitter:image" content="' . $this->escapeHtml($this->ogImage) . '">' . PHP_EOL;
        }

        return $html;
    }

    public function renderAllTags(): string
    {
        return $this->renderMetaTags() . 
               $this->renderOpenGraphTags() . 
               $this->renderTwitterCardTags();
    }

    private function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'canonical' => $this->canonical,
            'og_type' => $this->ogType,
            'og_title' => $this->ogTitle,
            'og_description' => $this->ogDescription,
            'og_image' => $this->ogImage,
            'og_url' => $this->ogUrl,
            'hreflangs' => $this->hreflangs,
            'alternates' => $this->alternates,
            'robots' => $this->robotsDirective,
            'language_code' => $this->languageCode,
        ];
    }
}
