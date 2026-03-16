<?php

namespace App\Modules\Shared\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class CityLandingPageService
{
    private const BIHAR_DISTRICTS = [
        'araria' => 'Araria',
        'arwal' => 'Arwal',
        'aurangabad' => 'Aurangabad',
        'banka' => 'Banka',
        'begusarai' => 'Begusarai',
        'bhagalpur' => 'Bhagalpur',
        'bhojpur' => 'Bhojpur',
        'buxar' => 'Buxar',
        'darbhanga' => 'Darbhanga',
        'east-champaran' => 'East Champaran',
        'gaya' => 'Gaya',
        'gopalganj' => 'Gopalganj',
        'jamui' => 'Jamui',
        'jehanabad' => 'Jehanabad',
        'kaimur' => 'Kaimur',
        'katihar' => 'Katihar',
        'khagaria' => 'Khagaria',
        'kishanganj' => 'Kishanganj',
        'lakhisarai' => 'Lakhisarai',
        'madhepura' => 'Madhepura',
        'madhubani' => 'Madhubani',
        'munger' => 'Munger',
        'muzaffarpur' => 'Muzaffarpur',
        'nalanda' => 'Nalanda',
        'nawada' => 'Nawada',
        'patna' => 'Patna',
        'purnia' => 'Purnia',
        'rohtas' => 'Rohtas',
        'saharsa' => 'Saharsa',
        'samastipur' => 'Samastipur',
        'saran' => 'Saran',
        'sheikhpura' => 'Sheikhpura',
        'sheohar' => 'Sheohar',
        'sitamarhi' => 'Sitamarhi',
        'siwan' => 'Siwan',
        'supaul' => 'Supaul',
        'vaishali' => 'Vaishali',
        'west-champaran' => 'West Champaran',
    ];

    private const INDIA_CITY_SEEDS = [
        'delhi', 'mumbai', 'bangalore', 'hyderabad', 'chennai', 'kolkata', 'pune', 'ahmedabad',
        'jaipur', 'surat', 'lucknow', 'kanpur', 'nagpur', 'indore', 'thane', 'bhopal',
        'visakhapatnam', 'pimpri-chinchwad', 'patna', 'vadodara', 'ghaziabad', 'ludhiana',
        'agra', 'nashik', 'faridabad', 'meerut', 'rajkot', 'kalyan', 'vasai-virar', 'varanasi',
        'srinagar', 'aurangabad', 'dhanbad', 'amritsar', 'navi-mumbai', 'allahabad', 'ranchi',
        'howrah', 'coimbatore', 'jabalpur', 'gwalior', 'vijayawada', 'jodhpur', 'madurai',
        'raipur', 'kota', 'guwahati', 'chandigarh', 'solapur', 'hubli', 'tiruchirappalli',
        'bareilly', 'mysore', 'tiruppur', 'gurgaon', 'aligarh', 'jalandhar', 'bhubaneswar',
        'salem', 'warangal', 'mira-bhayandar', 'thiruvananthapuram', 'bhiwandi', 'saharanpur',
        'gorakhpur', 'guntur', 'bikaner', 'amravati', 'noida', 'jamshedpur', 'bhilai',
        'cuttack', 'firozabad', 'kochi', 'nellore', 'bhavnagar', 'dehradun', 'durgapur',
        'asansol', 'rourkela', 'nanded', 'kolhapur', 'ajmer', 'akola', 'gulbarga',
        'jamnagar', 'ujjain', 'loni', 'siliguri', 'jhansi', 'ulhasnagar', 'jammu',
        'sangli', 'mangalore', 'erode', 'belgaum', 'ambattur', 'tirunelveli', 'malegaon',
        'gaya', 'jalgaon', 'udaipur', 'maheshtala', 'davanagere', 'kozhikode', 'kurnool',
        'rajpur-sonarpur', 'rajahmundry', 'bokaro', 'south-dumdum', 'bellary', 'patiala',
        'gopalpur', 'agartala', 'bhagalpur', 'muzaffarpur', 'bhatpara', 'panihati', 'latur',
        'dhule', 'rohtak', 'korba', 'bhilwara', 'berhampur', 'muzaffarnagar', 'ahmednagar',
        'mathura', 'kollam', 'avadi', 'kadapa', 'anantapur', 'kamarhati', 'bilaspur',
        'shahjahanpur', 'satara', 'bijapur', 'rampur', 'shivamogga', 'chandrapur', 'junagadh',
        'thrissur', 'alwar', 'bardhaman', 'kulti', 'kakinada', 'nizamabad', 'parbhani',
        'tumkur', 'khammam', 'hisar', 'rampurhat', 'darbhanga', 'panipat', 'aizawl',
    ];

    private const GLOBAL_CITY_SEEDS = [
        'new-york', 'los-angeles', 'chicago', 'houston', 'phoenix', 'philadelphia', 'san-antonio',
        'san-diego', 'dallas', 'san-jose', 'austin', 'jacksonville', 'fort-worth', 'columbus',
        'charlotte', 'san-francisco', 'indianapolis', 'seattle', 'denver', 'washington', 'boston',
        'el-paso', 'detroit', 'nashville', 'portland', 'memphis', 'oklahoma-city', 'las-vegas',
        'louisville', 'baltimore', 'milwaukee', 'albuquerque', 'tucson', 'fresno', 'sacramento',
        'kansas-city', 'atlanta', 'miami', 'paris', 'london', 'manchester', 'birmingham-uk',
        'liverpool', 'leeds', 'glasgow', 'edinburgh', 'dublin', 'amsterdam', 'rotterdam',
        'brussels', 'berlin', 'munich', 'frankfurt', 'hamburg', 'cologne', 'vienna', 'zurich',
        'geneva', 'madrid', 'barcelona', 'valencia', 'lisbon', 'porto', 'rome', 'milan',
        'naples', 'turin', 'athens', 'stockholm', 'oslo', 'helsinki', 'copenhagen', 'warsaw',
        'prague', 'budapest', 'bucharest', 'sofia', 'istanbul', 'ankara', 'dubai', 'abu-dhabi',
        'doha', 'riyadh', 'jeddah', 'muscat', 'singapore', 'kuala-lumpur', 'jakarta', 'bangkok',
        'ho-chi-minh-city', 'hanoi', 'manila', 'seoul', 'busan', 'tokyo', 'osaka', 'kyoto',
        'beijing', 'shanghai', 'guangzhou', 'shenzhen', 'hong-kong', 'taipei', 'sydney', 'melbourne',
        'brisbane', 'perth', 'adelaide', 'auckland', 'wellington', 'christchurch', 'toronto',
        'vancouver', 'montreal', 'calgary', 'ottawa', 'edmonton', 'quebec-city', 'mexico-city',
        'guadalajara', 'monterrey', 'sao-paulo', 'rio-de-janeiro', 'brasilia', 'buenos-aires',
        'cordoba-ar', 'santiago', 'bogota', 'medellin', 'lima', 'quito', 'johannesburg',
        'cape-town', 'durban', 'nairobi', 'lagos', 'accra', 'casablanca', 'cairo', 'alexandria',
        'marrakesh', 'tunis', 'algiers', 'addis-ababa',
    ];

    private const INDIA_EXPANSION_SUFFIXES = [
        '', '-central', '-east', '-west', '-north', '-south', '-metro', '-new-town',
    ];

    private const GLOBAL_EXPANSION_SUFFIXES = [
        '', '-downtown', '-metro', '-city-center', '-north', '-south', '-east', '-west',
    ];

    private const KEYWORD_PRODUCTS = [
        'makhana', 'fox-nuts', 'flavoured-makhana', 'roasted-makhana', 'peri-peri-makhana',
        'cheese-makhana', 'masala-makhana', 'salted-makhana', 'jaggery-makhana', 'diet-makhana',
        'healthy-snacks', 'bihari-snacks', 'protein-snacks', 'vegan-snacks', 'gluten-free-snacks',
        'snacks-for-weight-loss', 'evening-snacks', 'office-snacks', 'kids-snacks', 'snack-combo',
    ];

    private const KEYWORD_INTENTS = [
        'buy', 'order', 'shop', 'best', 'top', 'premium', 'authentic', 'organic', 'fresh',
        'wholesale', 'bulk', 'export-quality', 'affordable', 'discount', 'offer', 'same-day-delivery',
        'home-delivery', 'online-delivery', 'quick-delivery', 'cash-on-delivery', 'near-me',
        'lowest-price', 'trusted-brand', 'original', 'high-protein',
    ];

    private const KEYWORD_BENEFITS = [
        'for-weight-loss', 'for-fasting', 'for-kids', 'for-diabetics', 'for-gym',
        'for-night-snacking', 'for-diet-plan', 'for-office', 'for-travel', 'for-gifting',
        'for-festivals', 'for-parties', 'for-health-conscious', 'for-immunity', 'for-energy',
        'for-heart-health', 'for-low-calorie-diet', 'for-protein-intake', 'for-smart-snacking',
        'for-healthy-lifestyle',
    ];

    private const KEYWORD_AUDIENCES = [
        'india', 'bihar', 'patna', 'delhi', 'mumbai', 'bangalore', 'hyderabad', 'chennai',
        'kolkata', 'pune', 'lucknow', 'jaipur', 'ahmedabad', 'surat', 'london', 'dubai',
        'singapore', 'new-york', 'toronto', 'sydney', 'near-me', 'online', 'at-home',
        'for-family', 'for-students',
    ];

    public function getCityContent(string $slug, string $type = 'city'): ?array
    {
        $cacheKey = "city-landing:{$type}:{$slug}";

        return Cache::remember($cacheKey, 86400, function () use ($slug, $type) {
            if ($type === 'district') {
                $name = self::BIHAR_DISTRICTS[$slug] ?? null;
                if (!$name) {
                    return null;
                }

                return $this->buildLocationPayload($slug, $name, 'district', 'Bihar', 'India');
            }

            $indianCities = $this->getMajorCities();
            if (isset($indianCities[$slug])) {
                return $this->buildLocationPayload($slug, $indianCities[$slug], 'city', 'India', 'India');
            }

            $globalCities = $this->getGlobalCities();
            if (isset($globalCities[$slug])) {
                return $this->buildLocationPayload($slug, $globalCities[$slug], 'global', 'International', 'Global');
            }

            return null;
        });
    }

    public function getBiharDistricts(): array
    {
        return self::BIHAR_DISTRICTS;
    }

    public function getMajorCities(): array
    {
        $target = (int) config('snackzar.seo.programmatic.indian_city_target', 420);

        return Cache::remember('seo:major-cities', 86400, function () use ($target) {
            return $this->expandLocations(self::INDIA_CITY_SEEDS, self::INDIA_EXPANSION_SUFFIXES, $target);
        });
    }

    public function getGlobalCities(): array
    {
        $target = (int) config('snackzar.seo.programmatic.global_city_target', 520);

        return Cache::remember('seo:global-cities', 86400, function () use ($target) {
            return $this->expandLocations(self::GLOBAL_CITY_SEEDS, self::GLOBAL_EXPANSION_SUFFIXES, $target);
        });
    }

    public function getAllLocationSlugs(): array
    {
        return array_keys(array_merge(
            self::BIHAR_DISTRICTS,
            $this->getMajorCities(),
            $this->getGlobalCities()
        ));
    }

    public function getKeywordUniverseSize(): int
    {
        return count(self::KEYWORD_PRODUCTS)
            * count(self::KEYWORD_INTENTS)
            * count(self::KEYWORD_BENEFITS)
            * count(self::KEYWORD_AUDIENCES);
    }

    public function getKeywordByIndex(int $index): string
    {
        $pCount = count(self::KEYWORD_PRODUCTS);
        $iCount = count(self::KEYWORD_INTENTS);
        $bCount = count(self::KEYWORD_BENEFITS);
        $aCount = count(self::KEYWORD_AUDIENCES);

        $total = $pCount * $iCount * $bCount * $aCount;
        if ($index < 0 || $index >= $total) {
            return 'buy-makhana-online';
        }

        $productIndex = intdiv($index, $iCount * $bCount * $aCount);
        $remAfterProduct = $index % ($iCount * $bCount * $aCount);

        $intentIndex = intdiv($remAfterProduct, $bCount * $aCount);
        $remAfterIntent = $remAfterProduct % ($bCount * $aCount);

        $benefitIndex = intdiv($remAfterIntent, $aCount);
        $audienceIndex = $remAfterIntent % $aCount;

        return implode('-', [
            self::KEYWORD_INTENTS[$intentIndex],
            self::KEYWORD_PRODUCTS[$productIndex],
            self::KEYWORD_BENEFITS[$benefitIndex],
            self::KEYWORD_AUDIENCES[$audienceIndex],
        ]);
    }

    public function buildKeywordLandingData(int $id): ?array
    {
        $max = (int) config('snackzar.seo.programmatic.keyword_universe_size', 250000);
        if ($id < 1 || $id > $max) {
            return null;
        }

        $keywordSlug = $this->getKeywordByIndex($id - 1);
        $keywordText = ucwords(str_replace('-', ' ', $keywordSlug));

        return [
            'id' => $id,
            'slug' => $keywordSlug,
            'keyword' => $keywordText,
            'title' => "{$keywordText} | Snackzar",
            'description' => "Explore {$keywordText}. Premium makhana and healthy snacks from Snackzar with fast delivery and trusted quality.",
            'canonical' => config('app.url') . "/seo/k/{$id}-{$keywordSlug}",
            'og_title' => "{$keywordText} | Buy Online at Snackzar",
            'og_description' => "Order {$keywordText} with fresh stock, secure checkout, and pan-India/global shipping.",
            'og_image' => config('app.url') . '/images/og/city-landing.jpg',
            'related_locations' => array_slice($this->getAllLocationSlugs(), 0, 12),
        ];
    }

    public function generateSeoData(array $cityData): array
    {
        $slug = $cityData['slug'] ?? '';
        $name = $cityData['name'] ?? '';
        $type = $cityData['type'] ?? 'city';

        $path = $type === 'district'
            ? '/makhana-in-' . $slug
            : '/buy-makhana-online-' . $slug;

        return [
            'title' => $cityData['seo_title'] ?? "Buy Makhana in {$name} | Snackzar",
            'description' => $cityData['meta_description'] ?? "Premium makhana delivery in {$name}. Fast delivery and best prices.",
            'canonical' => config('app.url') . $path,
            'og_title' => "Snackzar - Premium Makhana in {$name}",
            'og_description' => $cityData['meta_description'] ?? "Best quality makhana in {$name}",
            'og_image' => config('app.url') . '/images/og/city-landing.jpg',
        ];
    }

    private function expandLocations(array $seedSlugs, array $suffixes, int $target): array
    {
        $expanded = [];

        foreach ($seedSlugs as $seed) {
            foreach ($suffixes as $suffix) {
                if (count($expanded) >= $target) {
                    break 2;
                }

                $slug = trim($seed . $suffix, '-');
                if (isset($expanded[$slug])) {
                    continue;
                }

                $expanded[$slug] = ucwords(str_replace('-', ' ', $slug));
            }
        }

        return $expanded;
    }

    private function buildLocationPayload(string $slug, string $name, string $type, string $state, string $region): array
    {
        $products = Product::active()
            ->inStock()
            ->with('primaryImage', 'category')
            ->limit(12)
            ->get();

        return [
            'name' => $name,
            'slug' => $slug,
            'type' => $type,
            'state' => $state,
            'region' => $region,
            'seo_title' => "Buy Premium Makhana in {$name} | Snackzar",
            'meta_description' => "Order premium makhana and healthy snacks in {$name}. Fast delivery, authentic quality, and best online pricing.",
            'content_summary' => "Snackzar delivers authentic makhana and healthy snacks in {$name} with quality assurance and fast shipping.",
            'products' => $products,
            'stats' => $this->getLocationStats(),
            'testimonials' => $this->getLocationTestimonials($name),
            'faq' => $this->getCityFAQ($name),
            'delivery_partners' => $this->getDeliveryPartners(),
        ];
    }

    private function getLocationStats(): array
    {
        return [
            'products_available' => Product::active()->inStock()->count(),
            'customers_served' => random_int(1000, 25000),
            'avg_rating' => 4.8,
            'total_reviews' => random_int(400, 8000),
        ];
    }

    private function getLocationTestimonials(string $location): array
    {
        return [
            [
                'name' => 'Rajesh Kumar',
                'location' => $location,
                'rating' => 5,
                'text' => 'Fresh stock, quick delivery, and excellent quality. Snackzar is my go-to for makhana.',
                'verified' => true,
            ],
            [
                'name' => 'Priya Sharma',
                'location' => $location,
                'rating' => 5,
                'text' => 'The taste and crunch are amazing. Packaging and service were excellent.',
                'verified' => true,
            ],
        ];
    }

    private function getCityFAQ(string $location): array
    {
        return [
            [
                'question' => "How long is delivery in {$location}?",
                'answer' => 'Delivery typically takes 2-5 business days depending on the exact pin code.',
            ],
            [
                'question' => 'Do you provide near me delivery support?',
                'answer' => 'Yes. Our logistics network is optimized for near me searches and local dispatch routing.',
            ],
            [
                'question' => 'Is this authentic Bihar makhana?',
                'answer' => 'Yes. We source premium quality makhana through trusted Bihar supply partners.',
            ],
            [
                'question' => 'Is COD available?',
                'answer' => 'Cash on delivery is available in serviceable pin codes.',
            ],
        ];
    }

    private function getDeliveryPartners(): array
    {
        return ['Delhivery', 'Xpressbees', 'Ekart', 'Blue Dart'];
    }
}
