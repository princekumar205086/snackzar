# SNACKZAR SEO & PWA - QUICK START GUIDE

## 🚀 Quick Implementation & Deployment

### Step 1: Environment Configuration

Add to `.env`:
```env
APP_DOMAIN=snackzar.com
APP_SCHEME=https
APP_ENV=production

# Google
GOOGLE_SITE_VERIFICATION=your-verification-code
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX

# Bing
BING_WEBMASTER_VERIFICATION=your-verification-code
```

### Step 2: Activate Canonical Domain Middleware

Update `app/Http/Middleware/CanonicalDomain.php` set to registered in `bootstrap/app.php`:

```php
// In bootstrap/app.php (Laravel 12)
// Add to middleware if needed
```

Or in routes:
```php
Route::middleware('canonical-domain')->group(function() {
    // Your routes
});
```

### Step 3: Register Service Worker in Frontend

The service worker is already registered in `resources/js/app.js`.

Test it:
```javascript
// In browser console
navigator.serviceWorker.getRegistrations().then(regs => {
    console.log(regs); // Should show service-worker.js
});
```

### Step 4: Verify SEO Files

Check these URLs working:
- ✓ `https://snackzar.com/robots.txt`
- ✓ `https://snackzar.com/sitemap.xml`
- ✓ `https://snackzar.com/manifest.json`
- ✓ `https://snackzar.com/service-worker.js`
- ✓ `https://snackzar.com/offline.html`

### Step 5: Test SiteMap Generation

```bash
php artisan tinker

# Generate URLs for sitemap
$sitemap = new \App\Modules\Shared\Services\SitemapService();
$urls = $sitemap->generate();
echo count($urls) . " URLs generated";
```

---

## 📍 Using City Landing Pages

### Add City Landing Route (Already in web.php)

```php
Route::get('/makhana-in-{district}', [HomeController::class, 'cityLanding'])->name('city.landing.district');
Route::get('/buy-makhana-online-{city}', [HomeController::class, 'cityLanding'])->name('city.landing.city');
```

### In HomeController

```php
public function cityLanding(string $slug): Response
{
    $service = new CityLandingPageService();
    $cellData = $service->getCityContent($slug, 'district'); // or 'city'
    
    if (!$cityData) {
        abort(404);
    }
    
    return Inertia::render('CityLanding', [
        'city' => $cityData,
        'seo' => $service->generateSeoData($cityData),
    ]);
}
```

---

## 🏷️ Using Structured Data in Components

### In Vue Components or Blade Templates

```php
// Get structure data service
$structuredData = resolve(StructuredDataService::class);

// Generate product schema
$productSchema = $structuredData->generateProductSchema($product, $variants);

// Generate breadcrumb schema
$breadcrumbs = [
    ['name' => 'Home', 'url' => url('/')],
    ['name' => 'Products', 'url' => route('products.index')],
    ['name' => $product->name],
];
$breadcrumbSchema = $structuredData->generateBreadcrumbSchema($breadcrumbs);

// Render JSON-LD
echo $structuredData->renderMultipleJsonLd([
    $productSchema,
    $breadcrumbSchema,
]);
```

---

## 🏪 Meta Tags Implementation

### Service Usage

```php
use App\Modules\Shared\Services\SeoMetaTagsService;

$seo = new SeoMetaTagsService();

$seo->setTitle('Buy Premium Makhana Online | Snackzar')
    ->setDescription('Premium makhana and healthy snacks delivered fresh to your door.')
    ->setCanonical(url()->current())
    ->setOgImage(asset('images/og/makhana.jpg'))
    ->addHrefLang('en-IN', url()->current())
    ->addHrefLang('en-US', url('/en/') . request()->path());

// In Blade template
{!! $seo->renderAllTags() !!}
```

---

## 📱 Android App Testing

### Emulator Testing

```bash
# Build debug APK
./gradlew assembleDebug

# Install on emulator
adb install android/app/build/outputs/apk/debug/app-debug.apk

# Test deep linking
adb shell am start -a android.intent.action.VIEW -d "https://snackzar.com/products/makhana-premium" com.snackzar.app
```

---

## 🔍 Submission to Search Engines

### Google Search Console

1. Add property: `https://snackzar.com`
2. Verify ownership (DNS or file)
3. Submit sitemap:
   - Settings → Sitemaps → `https://snackzar.com/sitemap-index.xml`
4. Request indexing for key pages

### Bing Webmaster Tools

1. Add site: `https://snackzar.com`
2. Verify ownership
3. Submit `sitemap-index.xml`

### Yandex Webmaster

1. Add site
2. Submit `robots.txt` and `sitemap-index.xml`

---

## ⚡ Performance Optimization

### Check Lighthouse Score

```javascript
// In Chrome DevTools
// Press Ctrl+Shift+I → Lighthouse → Generate report
// Target: > 90 for all metrics
```

### Monitor Core Web Vitals

```javascript
// In browser console
import web-vital library for monitoring
// Largest Contentful Paint (LCP)
// First Input Delay (FID)
// Cumulative Layout Shift (CLS)
```

---

## 🛠️ Troubleshooting

### Issue: robots.txt not found
**Solution:** Check file is in `public/robots.txt` with correct permissions

### Issue: Service Worker not registering
**Solution:** 
- Check HTTPS is enabled (dev can use localhost)
- Clear browser cache
- Check browser console for errors

### Issue: Sitemap errors
**Solution:**
```bash
# Clear cache
php artisan cache:clear

# Regenerate
php artisan tinker
> $service = new \App\Modules\Shared\Services\SitemapService();
> $urls = $service->generate();
> Cache::put('sitemap:urls', $urls);
```

### Issue: PWA not installing
**Solution:**
- HTTPS required (except localhost)
- manifest.json must be valid JSON
- Icons must exist at specified paths

---

## 📊 Monitoring & Analytics

### Key Metrics to Track

1. **Organic Traffic**
   - Google Analytics → Acquisition → Organic Search

2. **Keyword Rankings**
   - Google Search Console → Performance

3. **Indexation**
   - Google Search Console → Coverage

4. **Crawlability**
   - Google Search Console → Crawl Stats

5. **Core Web Vitals**
   - Google Search Console → Experience

---

## 🔄 Continuous Maintenance

### Weekly
- Check GSC for errors
- Monitor 404 errors
- Review crawl stats

### Monthly
- Audit internal links
- Check ranking changes
- Review user behavior

### Quarterly
- Audit structure data
- Review duplicate content
- Update city pages

---

## 📚 Documentation Files

1. **Full Implementation Guide:** `docs/SEO_PWA_IMPLEMENTATION.md`
2. **This Quick Start:** `docs/SEO_PWA_QUICK_START.md`
3. **Project Implementation:** `PROJECT_IMPLEMENTATION.md`

---

## 🎓 Learning Resources

- Google Search Central: https://developers.google.com/search
- Schema.org: https://schema.org
- MDN Web Docs: https://developer.mozilla.org/
- CSS-Tricks Service Workers: https://css-tricks.com/serviceworker/
- Android Developers: https://developer.android.com/

---

**Version:** 1.0
**Last Updated:** March 17, 2026
**Ready for Production:** ✓
