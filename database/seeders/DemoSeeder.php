<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\BlogPost;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\DeliveryProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Users ──
        $seller1 = User::firstOrCreate(['email' => 'seller@snackzar.com'], [
            'name' => 'Bihar Snacks Co.',
            'phone' => '9876543210',
            'password' => 'password',
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $seller1->assignRole('seller');

        $seller2 = User::firstOrCreate(['email' => 'seller2@snackzar.com'], [
            'name' => 'Makhana House',
            'phone' => '9876543211',
            'password' => 'password',
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $seller2->assignRole('seller');

        $buyer = User::firstOrCreate(['email' => 'user@snackzar.com'], [
            'name' => 'Rahul Kumar',
            'phone' => '9876543220',
            'password' => 'password',
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $buyer->assignRole('user');

        $buyer2 = User::firstOrCreate(['email' => 'user2@snackzar.com'], [
            'name' => 'Priya Sharma',
            'phone' => '9876543221',
            'password' => 'password',
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $buyer2->assignRole('user');

        $delivery = User::firstOrCreate(['email' => 'delivery@snackzar.com'], [
            'name' => 'Delivery Partner',
            'phone' => '9876543230',
            'password' => 'password',
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $delivery->assignRole('delivery_partner');

        // ── 2. Seller & Delivery Profiles ──
        SellerProfile::firstOrCreate(['user_id' => $seller1->id], [
            'business_name' => 'Bihar Snacks Co.',
            'gst_number' => '10AABCT1332L1ZS',
            'pan_number' => 'AABCT1332L',
            'business_address' => 'Gandhi Maidan, Patna, Bihar 800001',
            'bank_name' => 'SBI',
            'bank_account_number' => '1234567890',
            'bank_ifsc' => 'SBIN0000123',
            'commission_rate' => 10.00,
            'status' => 'approved',
        ]);

        SellerProfile::firstOrCreate(['user_id' => $seller2->id], [
            'business_name' => 'Makhana House',
            'gst_number' => '10BBDFT5532M2ZP',
            'pan_number' => 'BBDFT5532M',
            'business_address' => 'Darbhanga, Bihar 846004',
            'bank_name' => 'HDFC',
            'bank_account_number' => '9876543210',
            'bank_ifsc' => 'HDFC0001234',
            'commission_rate' => 10.00,
            'status' => 'approved',
        ]);

        DeliveryProfile::firstOrCreate(['user_id' => $delivery->id], [
            'vehicle_type' => 'bike',
            'vehicle_number' => 'BR01AB1234',
            'license_number' => 'BR0120210001234',
            'status' => 'approved',
            'is_available' => true,
        ]);

        // ── 3. Addresses ──
        Address::firstOrCreate(['user_id' => $buyer->id, 'type' => 'home'], [
            'name' => 'Rahul Kumar',
            'phone' => '9876543220',
            'address_line_1' => '123, Boring Road',
            'address_line_2' => 'Near Patna University',
            'city' => 'Patna',
            'state' => 'Bihar',
            'pincode' => '800001',
            'is_default' => true,
        ]);

        Address::firstOrCreate(['user_id' => $buyer2->id, 'type' => 'home'], [
            'name' => 'Priya Sharma',
            'phone' => '9876543221',
            'address_line_1' => '45, MG Road',
            'city' => 'Gaya',
            'state' => 'Bihar',
            'pincode' => '823001',
            'is_default' => true,
        ]);

        // ── 4. Categories ──
        $categories = [
            ['name' => 'Makhana', 'slug' => 'makhana', 'description' => 'Premium fox nuts from Bihar — roasted, flavored, and organic makhana snacks.', 'image' => 'https://images.unsplash.com/photo-1630159376105-3ce0acd9e42b?w=400', 'sort_order' => 1],
            ['name' => 'Namkeen & Mixture', 'slug' => 'namkeen-mixture', 'description' => 'Crunchy Bihar-style namkeen, bhujia, and mixtures.', 'image' => 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=400', 'sort_order' => 2],
            ['name' => 'Sweets & Mithai', 'slug' => 'sweets-mithai', 'description' => 'Traditional Bihari sweets — tilkut, anarsa, lai, and more.', 'image' => 'https://images.unsplash.com/photo-1548907040-4baa42d10919?w=400', 'sort_order' => 3],
            ['name' => 'Pickles & Chutneys', 'slug' => 'pickles-chutneys', 'description' => 'Homemade authentic Bihar pickles and chutneys.', 'image' => 'https://images.unsplash.com/photo-1589135233689-3baf2f138e52?w=400', 'sort_order' => 4],
            ['name' => 'Spices & Masala', 'slug' => 'spices-masala', 'description' => 'Fresh ground Bihar spices and masala blends.', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', 'sort_order' => 5],
            ['name' => 'Healthy Snacks', 'slug' => 'healthy-snacks', 'description' => 'Protein-rich, sugar-free, and organic healthy Bihari snacks.', 'image' => 'https://images.unsplash.com/photo-1604908177453-7462950a6a3b?w=400', 'sort_order' => 6],
            ['name' => 'Sattu Products', 'slug' => 'sattu-products', 'description' => 'Bihar\'s superfood — roasted gram flour drinks, laddoos, and more.', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400', 'sort_order' => 7],
            ['name' => 'Gift Hampers', 'slug' => 'gift-hampers', 'description' => 'Curated Bihari snack hampers perfect for gifting on festivals and occasions.', 'image' => 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=400', 'sort_order' => 8],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['slug']] = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['is_active' => true])
            );
        }

        // ── 5. Products ──
        $products = [
            // ─── Makhana (7 products) ───
            ['name' => 'Classic Roasted Makhana', 'slug' => 'classic-roasted-makhana', 'category' => 'makhana', 'seller' => $seller1, 'price' => 299, 'compare_price' => 399, 'stock' => 150, 'is_featured' => true, 'weight' => 200, 'short_description' => 'Crunchy roasted fox nuts with a pinch of rock salt. A healthy and addictive snack.', 'description' => '<p>Our Classic Roasted Makhana is made from premium-grade fox nuts sourced directly from Mithilanchal, Bihar. Slow-roasted in pure ghee and lightly seasoned with Himalayan pink salt for a guilt-free, addictive crunch.</p><h3>Why You\'ll Love It</h3><ul><li>100% natural - no artificial flavors</li><li>Rich in protein and calcium</li><li>Low calorie snacking</li><li>Perfect for movie nights, office breaks, or mid-day hunger</li></ul>', 'image' => 'https://images.unsplash.com/photo-1630159376105-3ce0acd9e42b?w=600'],
            ['name' => 'Mint Masala Makhana', 'slug' => 'mint-masala-makhana', 'category' => 'makhana', 'seller' => $seller1, 'price' => 349, 'compare_price' => 449, 'stock' => 100, 'is_featured' => true, 'weight' => 200, 'short_description' => 'Refreshing mint with a tangy masala twist on crunchy makhana.', 'description' => '<p>A delightful combination of cool mint and tangy masala spices on premium fox nuts. Each bite delivers a refreshing crunch that\'s hard to put down.</p>', 'image' => 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=600'],
            ['name' => 'Peri Peri Makhana', 'slug' => 'peri-peri-makhana', 'category' => 'makhana', 'seller' => $seller2, 'price' => 349, 'compare_price' => null, 'stock' => 80, 'is_featured' => false, 'weight' => 200, 'short_description' => 'Fiery peri peri flavored makhana for spice lovers.', 'description' => '<p>Turn up the heat with our Peri Peri Makhana! Generously coated with our signature peri peri seasoning, these crunchy fox nuts pack a flavorful punch.</p>', 'image' => 'https://images.unsplash.com/photo-1604908177453-7462950a6a3b?w=600'],
            ['name' => 'Cream & Onion Makhana', 'slug' => 'cream-onion-makhana', 'category' => 'makhana', 'seller' => $seller2, 'price' => 329, 'compare_price' => 429, 'stock' => 120, 'is_featured' => true, 'weight' => 200, 'short_description' => 'Creamy, savory, and deeply satisfying cream & onion makhana.', 'description' => '<p>The classic cream & onion flavor reimagined with premium makhana. Rich, savory, and incredibly satisfying.</p>', 'image' => 'https://images.unsplash.com/photo-1548907040-4baa42d10919?w=600'],
            ['name' => 'Organic Raw Makhana', 'slug' => 'organic-raw-makhana', 'category' => 'makhana', 'seller' => $seller1, 'price' => 499, 'compare_price' => 599, 'stock' => 200, 'is_featured' => true, 'weight' => 500, 'short_description' => 'Pure organic raw makhana — cook, roast, or add to your recipes.', 'description' => '<p>Premium organic raw fox nuts sourced from certified organic farms in Darbhanga, Bihar. Perfect for making makhana kheer, roasting at home, or adding to curries.</p>', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=600'],
            ['name' => 'Cheese & Herbs Makhana', 'slug' => 'cheese-herbs-makhana', 'category' => 'makhana', 'seller' => $seller1, 'price' => 359, 'compare_price' => 449, 'stock' => 90, 'is_featured' => false, 'weight' => 200, 'short_description' => 'Italian herbs and cheese flavored makhana — a gourmet twist on the classic.', 'description' => '<p>Experience the fusion of Italian herbs and rich cheese flavor on crunchy Bihar makhana. A gourmet snacking experience that\'s both delicious and healthy.</p>', 'image' => 'https://images.unsplash.com/photo-1630159376105-3ce0acd9e42b?w=600'],
            ['name' => 'Tandoori Makhana', 'slug' => 'tandoori-makhana', 'category' => 'makhana', 'seller' => $seller2, 'price' => 339, 'compare_price' => null, 'stock' => 110, 'is_featured' => false, 'weight' => 200, 'short_description' => 'Smoky tandoori flavored makhana with a hint of lemon.', 'description' => '<p>Our smoky Tandoori Makhana captures the essence of tandoori spices with a refreshing lemon finish. Perfect for parties and evening snacking.</p>', 'image' => 'https://images.unsplash.com/photo-1604908177453-7462950a6a3b?w=600'],

            // ─── Namkeen & Mixture (4 products) ───
            ['name' => 'Bihar Special Chura Mixture', 'slug' => 'bihar-special-chura-mixture', 'category' => 'namkeen-mixture', 'seller' => $seller1, 'price' => 199, 'compare_price' => 249, 'stock' => 200, 'is_featured' => false, 'weight' => 400, 'short_description' => 'Authentic Bihar-style chura mixture with peanuts and spices.', 'description' => '<p>A crunchy medley of flattened rice, roasted peanuts, curry leaves, and Bihar\'s signature spice blend. Perfect tea-time snack.</p>', 'image' => 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=600'],
            ['name' => 'Sattu Namkeen Bites', 'slug' => 'sattu-namkeen-bites', 'category' => 'namkeen-mixture', 'seller' => $seller2, 'price' => 179, 'compare_price' => null, 'stock' => 90, 'is_featured' => false, 'weight' => 300, 'short_description' => 'Crispy sattu bites — Bihar\'s beloved protein snack.', 'description' => '<p>Made from roasted gram flour (sattu), these crispy bites are packed with protein and quintessential Bihar flavor.</p>', 'image' => 'https://images.unsplash.com/photo-1604908177453-7462950a6a3b?w=600'],
            ['name' => 'Litti Chips', 'slug' => 'litti-chips', 'category' => 'namkeen-mixture', 'seller' => $seller1, 'price' => 149, 'compare_price' => 199, 'stock' => 150, 'is_featured' => true, 'weight' => 200, 'short_description' => 'Crispy chips inspired by Bihar\'s iconic Litti Chokha — smoky, spicy, and addictive.', 'description' => '<p>The flavors of Bihar\'s beloved Litti Chokha transformed into crispy chips. Smoky roasted gram seasoning with a tangy chokha twist.</p>', 'image' => 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=600'],
            ['name' => 'Dalmoth Special', 'slug' => 'dalmoth-special', 'category' => 'namkeen-mixture', 'seller' => $seller2, 'price' => 169, 'compare_price' => null, 'stock' => 130, 'is_featured' => false, 'weight' => 350, 'short_description' => 'Traditional Bihari dalmoth with moong dal, cashews, and aromatic spices.', 'description' => '<p>A beloved Bihari namkeen made with crispy moong dal, cashews, raisins, and a secret blend of aromatic spices. A must-have for chai time.</p>', 'image' => 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=600'],

            // ─── Sweets & Mithai (4 products) ───
            ['name' => 'Tilkut (Sesame Brittle)', 'slug' => 'tilkut-sesame-brittle', 'category' => 'sweets-mithai', 'seller' => $seller1, 'price' => 249, 'compare_price' => 299, 'stock' => 100, 'is_featured' => true, 'weight' => 400, 'short_description' => 'Traditional Gaya tilkut — crunchy sesame brittle made with jaggery.', 'description' => '<p>Famous Gaya ka Tilkut! Hand-crafted sesame seed brittle sweetened with organic jaggery. A winter delicacy from Bihar that\'s loved across India.</p>', 'image' => 'https://images.unsplash.com/photo-1548907040-4baa42d10919?w=600'],
            ['name' => 'Anarsa Pack', 'slug' => 'anarsa-pack', 'category' => 'sweets-mithai', 'seller' => $seller2, 'price' => 349, 'compare_price' => null, 'stock' => 60, 'is_featured' => false, 'weight' => 500, 'short_description' => 'Traditional deep-fried rice flour sweet from Bihar.', 'description' => '<p>Anarsa is a beloved Bihari sweet made from soaked rice flour, jaggery, and poppy seeds. Festive favorite, perfect with chai.</p>', 'image' => 'https://images.unsplash.com/photo-1548907040-4baa42d10919?w=600'],
            ['name' => 'Lai (Puffed Rice Chikki)', 'slug' => 'lai-puffed-rice-chikki', 'category' => 'sweets-mithai', 'seller' => $seller1, 'price' => 189, 'compare_price' => 229, 'stock' => 80, 'is_featured' => false, 'weight' => 300, 'short_description' => 'Crunchy puffed rice bars bonded with jaggery — Bihar\'s Makar Sankranti special.', 'description' => '<p>Lai is a traditional Bihari sweet made by binding puffed rice with melted jaggery. Crunchy, sweet, and deeply nostalgic. A Makar Sankranti staple across Bihar.</p>', 'image' => 'https://images.unsplash.com/photo-1548907040-4baa42d10919?w=600'],
            ['name' => 'Thekua (Wheat Cookie)', 'slug' => 'thekua-wheat-cookie', 'category' => 'sweets-mithai', 'seller' => $seller2, 'price' => 279, 'compare_price' => 349, 'stock' => 70, 'is_featured' => true, 'weight' => 400, 'short_description' => 'Traditional Bihari wheat cookie with jaggery and coconut — Chhath Puja prasad.', 'description' => '<p>Thekua is the most iconic Bihari sweet, made during Chhath Puja. Prepared from whole wheat flour, jaggery, coconut, and ghee. Deep-fried to golden perfection.</p>', 'image' => 'https://images.unsplash.com/photo-1548907040-4baa42d10919?w=600'],

            // ─── Pickles & Chutneys (3 products) ───
            ['name' => 'Bihar Mango Pickle', 'slug' => 'bihar-mango-pickle', 'category' => 'pickles-chutneys', 'seller' => $seller1, 'price' => 229, 'compare_price' => 299, 'stock' => 150, 'is_featured' => false, 'weight' => 400, 'short_description' => 'Authentic homestyle Bihar mango pickle with mustard oil.', 'description' => '<p>Sun-dried raw mangoes pickled in cold-pressed mustard oil with fenugreek, fennel, and nigella seeds. Just like grandmother makes it.</p>', 'image' => 'https://images.unsplash.com/photo-1589135233689-3baf2f138e52?w=600'],
            ['name' => 'Lemon & Green Chili Pickle', 'slug' => 'lemon-green-chili-pickle', 'category' => 'pickles-chutneys', 'seller' => $seller2, 'price' => 199, 'compare_price' => null, 'stock' => 120, 'is_featured' => false, 'weight' => 350, 'short_description' => 'Tangy lemon and fiery green chili pickle in mustard oil — Bihar\'s favorite condiment.', 'description' => '<p>A zesty combination of fresh lemons and green chilies preserved in pure mustard oil with traditional Bihar spices. Pairs perfectly with dal-chawal and parathas.</p>', 'image' => 'https://images.unsplash.com/photo-1589135233689-3baf2f138e52?w=600'],
            ['name' => 'Tomato Chutney (Tamatar ki Chutney)', 'slug' => 'tomato-chutney', 'category' => 'pickles-chutneys', 'seller' => $seller1, 'price' => 159, 'compare_price' => 199, 'stock' => 100, 'is_featured' => false, 'weight' => 250, 'short_description' => 'Sweet and tangy tomato chutney made with Bihar\'s signature spice blend.', 'description' => '<p>A quintessential Bihar condiment — ripe tomatoes slow-cooked with mustard seeds, cumin, and jaggery. Sweet, tangy, and perfect with any meal.</p>', 'image' => 'https://images.unsplash.com/photo-1589135233689-3baf2f138e52?w=600'],

            // ─── Spices & Masala (2 products) ───
            ['name' => 'Bihar Special Masala Box', 'slug' => 'bihar-special-masala-box', 'category' => 'spices-masala', 'seller' => $seller2, 'price' => 399, 'compare_price' => 499, 'stock' => 80, 'is_featured' => false, 'weight' => 600, 'short_description' => 'Curated spice box with 6 essential Bihar masalas.', 'description' => '<p>A beautiful gift box containing 6 freshly ground spices essential to Bihar cuisine: Panch Phoran, Mustard Powder, Turmeric, Red Chili, Coriander, and our signature Bihari Garam Masala.</p>', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=600'],
            ['name' => 'Bihari Panch Phoran', 'slug' => 'bihari-panch-phoran', 'category' => 'spices-masala', 'seller' => $seller1, 'price' => 149, 'compare_price' => null, 'stock' => 140, 'is_featured' => false, 'weight' => 200, 'short_description' => 'Authentic five-spice blend used in every Bihari kitchen — fennel, fenugreek, cumin, mustard, and nigella.', 'description' => '<p>Panch Phoran is the soul of Bihari cooking. Our blend uses whole seeds of fennel, fenugreek, cumin, black mustard, and nigella (kalonji) in perfect proportion.</p>', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=600'],

            // ─── Healthy Snacks (2 products) ───
            ['name' => 'Protein Makhana Trail Mix', 'slug' => 'protein-makhana-trail-mix', 'category' => 'healthy-snacks', 'seller' => $seller1, 'price' => 449, 'compare_price' => 549, 'stock' => 70, 'is_featured' => true, 'weight' => 300, 'short_description' => 'Makhana, almonds, cashews, and seeds — the ultimate trail mix.', 'description' => '<p>A health-conscious trail mix combining roasted makhana, almonds, cashews, pumpkin seeds, and a touch of honey. 12g protein per serving!</p>', 'image' => 'https://images.unsplash.com/photo-1604908177453-7462950a6a3b?w=600'],
            ['name' => 'Low-Calorie Makhana Bites (Jaggery)', 'slug' => 'low-calorie-makhana-bites', 'category' => 'healthy-snacks', 'seller' => $seller2, 'price' => 329, 'compare_price' => 399, 'stock' => 85, 'is_featured' => false, 'weight' => 200, 'short_description' => 'Sweet makhana bites with organic jaggery — only 90 calories per serving.', 'description' => '<p>Guilt-free sweet snacking! Roasted makhana lightly coated with organic jaggery and a hint of cardamom. Just 90 calories per 30g serving.</p>', 'image' => 'https://images.unsplash.com/photo-1604908177453-7462950a6a3b?w=600'],

            // ─── Sattu Products (3 products) ───
            ['name' => 'Premium Sattu Powder', 'slug' => 'premium-sattu-powder', 'category' => 'sattu-products', 'seller' => $seller1, 'price' => 199, 'compare_price' => 249, 'stock' => 180, 'is_featured' => true, 'weight' => 500, 'short_description' => 'Stone-ground roasted gram flour — Bihar\'s original protein drink.', 'description' => '<p>100% pure Sattu made from slow-roasted Bengal gram (chana), stone-ground to preserve maximum nutrition. Mix with water, lemon, and salt for a refreshing summer drink, or with jaggery for a sweet energy boost.</p><h3>Nutritional Highlights</h3><ul><li>20g protein per 100g</li><li>High in fiber and iron</li><li>Natural coolant for summers</li><li>Zero additives or preservatives</li></ul>', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600'],
            ['name' => 'Sattu Laddoo (Pack of 12)', 'slug' => 'sattu-laddoo', 'category' => 'sattu-products', 'seller' => $seller2, 'price' => 299, 'compare_price' => 349, 'stock' => 60, 'is_featured' => false, 'weight' => 400, 'short_description' => 'Handmade sattu laddoos with jaggery, ghee, and dry fruits — a protein-packed treat.', 'description' => '<p>Traditional Bihari Sattu Laddoo made with roasted gram flour, organic jaggery, pure desi ghee, and a mix of almonds and cashews. Each laddoo packs 8g of protein!</p>', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600'],
            ['name' => 'Sattu Paratha Mix', 'slug' => 'sattu-paratha-mix', 'category' => 'sattu-products', 'seller' => $seller1, 'price' => 179, 'compare_price' => null, 'stock' => 100, 'is_featured' => false, 'weight' => 400, 'short_description' => 'Ready-to-use sattu paratha stuffing mix with onion, green chili, and spices.', 'description' => '<p>Make authentic Bihar ka Sattu Paratha in minutes! Our pre-mixed sattu stuffing comes with dehydrated onion, green chili flakes, and aromatic spices. Just add water, stuff in dough, and cook.</p>', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600'],

            // ─── Gift Hampers (2 products) ───
            ['name' => 'Bihar Snack Sampler Box', 'slug' => 'bihar-snack-sampler-box', 'category' => 'gift-hampers', 'seller' => $seller1, 'price' => 999, 'compare_price' => 1299, 'stock' => 40, 'is_featured' => true, 'weight' => 1200, 'short_description' => 'A curated box of 8 best-selling Bihari snacks — perfect gift for any occasion.', 'description' => '<p>Our best-seller! This curated gift box includes 8 mini packs: Classic Makhana, Mint Makhana, Chura Mixture, Tilkut, Thekua, Sattu Laddoo, Mango Pickle, and Panch Phoran. Beautifully packaged in a handcrafted Madhubani art box.</p>', 'image' => 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600'],
            ['name' => 'Makhana Premium Gift Set', 'slug' => 'makhana-premium-gift-set', 'category' => 'gift-hampers', 'seller' => $seller2, 'price' => 1499, 'compare_price' => 1799, 'stock' => 25, 'is_featured' => false, 'weight' => 1500, 'short_description' => '6 premium makhana flavors in an elegant Madhubani art gift box — a true Bihar experience.', 'description' => '<p>The ultimate makhana gift experience! Contains 6 premium flavors: Classic, Mint Masala, Peri Peri, Cream & Onion, Cheese & Herbs, and Tandoori — each in a 100g premium jar. Presented in our signature Madhubani-painted gift box from Mithila artisans.</p>', 'image' => 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600'],
        ];

        $productModels = [];
        foreach ($products as $p) {
            $category = $categoryModels[$p['category']];
            $product = Product::firstOrCreate(
                ['slug' => $p['slug']],
                [
                    'category_id' => $category->id,
                    'seller_id' => $p['seller']->id,
                    'name' => $p['name'],
                    'slug' => $p['slug'],
                    'short_description' => $p['short_description'],
                    'description' => $p['description'],
                    'sku' => 'SNK-' . strtoupper(Str::random(6)),
                    'price' => $p['price'],
                    'compare_price' => $p['compare_price'],
                    'cost_price' => round($p['price'] * 0.5, 2),
                    'stock' => $p['stock'],
                    'low_stock_threshold' => 5,
                    'weight' => $p['weight'] ?? 250,
                    'unit' => 'g',
                    'is_active' => true,
                    'is_featured' => $p['is_featured'],
                ]
            );

            // Primary image
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'is_primary' => true],
                [
                    'url' => $p['image'],
                    'alt_text' => $p['name'],
                    'sort_order' => 0,
                ]
            );

            $productModels[$product->slug] = $product;
        }

        // ── 6. Product Variants ──
        // Makhana variants (100g, 250g, 500g)
        $makhanaFlavors = ['classic-roasted-makhana', 'mint-masala-makhana', 'peri-peri-makhana', 'cream-onion-makhana', 'cheese-herbs-makhana', 'tandoori-makhana'];
        foreach ($makhanaFlavors as $slug) {
            if (!isset($productModels[$slug])) continue;
            $product = $productModels[$slug];
            ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'name' => '100g Pack'],
                ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => $product->price, 'stock' => 50, 'weight' => 100, 'is_active' => true, 'sort_order' => 1]
            );
            ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'name' => '250g Pack'],
                ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => round($product->price * 2.2, 0), 'stock' => 30, 'weight' => 250, 'is_active' => true, 'sort_order' => 2]
            );
            ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'name' => '500g Family Pack'],
                ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => round($product->price * 4, 0), 'stock' => 20, 'weight' => 500, 'is_active' => true, 'sort_order' => 3]
            );
        }

        // Raw makhana variants (250g, 500g, 1kg)
        if (isset($productModels['organic-raw-makhana'])) {
            $raw = $productModels['organic-raw-makhana'];
            ProductVariant::firstOrCreate(['product_id' => $raw->id, 'name' => '250g Pack'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => 499, 'stock' => 60, 'weight' => 250, 'is_active' => true, 'sort_order' => 1]);
            ProductVariant::firstOrCreate(['product_id' => $raw->id, 'name' => '500g Pack'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => 899, 'stock' => 40, 'weight' => 500, 'is_active' => true, 'sort_order' => 2]);
            ProductVariant::firstOrCreate(['product_id' => $raw->id, 'name' => '1 Kg Bulk Pack'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => 1599, 'stock' => 25, 'weight' => 1000, 'is_active' => true, 'sort_order' => 3]);
        }

        // Sattu powder variants
        if (isset($productModels['premium-sattu-powder'])) {
            $sattu = $productModels['premium-sattu-powder'];
            ProductVariant::firstOrCreate(['product_id' => $sattu->id, 'name' => '500g Pack'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => 199, 'stock' => 80, 'weight' => 500, 'is_active' => true, 'sort_order' => 1]);
            ProductVariant::firstOrCreate(['product_id' => $sattu->id, 'name' => '1 Kg Pack'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => 349, 'stock' => 50, 'weight' => 1000, 'is_active' => true, 'sort_order' => 2]);
            ProductVariant::firstOrCreate(['product_id' => $sattu->id, 'name' => '2 Kg Family Pack'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => 649, 'stock' => 30, 'weight' => 2000, 'is_active' => true, 'sort_order' => 3]);
        }

        // Pickle variants (250g, 500g)
        $pickles = ['bihar-mango-pickle', 'lemon-green-chili-pickle', 'tomato-chutney'];
        foreach ($pickles as $slug) {
            if (!isset($productModels[$slug])) continue;
            $p = $productModels[$slug];
            ProductVariant::firstOrCreate(['product_id' => $p->id, 'name' => '250g Jar'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => $p->price, 'stock' => 60, 'weight' => 250, 'is_active' => true, 'sort_order' => 1]);
            ProductVariant::firstOrCreate(['product_id' => $p->id, 'name' => '500g Jar'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => round($p->price * 1.8, 0), 'stock' => 35, 'weight' => 500, 'is_active' => true, 'sort_order' => 2]);
        }

        // Namkeen variants (200g, 500g)
        $namkeens = ['bihar-special-chura-mixture', 'dalmoth-special', 'litti-chips'];
        foreach ($namkeens as $slug) {
            if (!isset($productModels[$slug])) continue;
            $p = $productModels[$slug];
            ProductVariant::firstOrCreate(['product_id' => $p->id, 'name' => '200g Pack'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => $p->price, 'stock' => 70, 'weight' => 200, 'is_active' => true, 'sort_order' => 1]);
            ProductVariant::firstOrCreate(['product_id' => $p->id, 'name' => '500g Value Pack'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => round($p->price * 2.3, 0), 'stock' => 40, 'weight' => 500, 'is_active' => true, 'sort_order' => 2]);
        }

        // Sweet variants (250g, 500g)
        $sweets = ['tilkut-sesame-brittle', 'thekua-wheat-cookie', 'lai-puffed-rice-chikki'];
        foreach ($sweets as $slug) {
            if (!isset($productModels[$slug])) continue;
            $p = $productModels[$slug];
            ProductVariant::firstOrCreate(['product_id' => $p->id, 'name' => '250g Box'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => $p->price, 'stock' => 40, 'weight' => 250, 'is_active' => true, 'sort_order' => 1]);
            ProductVariant::firstOrCreate(['product_id' => $p->id, 'name' => '500g Gift Box'], ['sku' => 'SNK-V-' . strtoupper(Str::random(4)), 'price' => round($p->price * 1.8, 0), 'stock' => 25, 'weight' => 500, 'is_active' => true, 'sort_order' => 2]);
        }

        // ── 7. Reviews ──
        $reviewData = [
            ['classic-roasted-makhana', $buyer, 5, 'Absolutely love it! Best makhana I\'ve ever had. The roast is perfect and salt is just right.'],
            ['classic-roasted-makhana', $buyer2, 4, 'Very tasty and crunchy. Packaging was good. Would buy again.'],
            ['mint-masala-makhana', $buyer, 5, 'The mint flavor is so refreshing. Great healthy alternative to chips!'],
            ['cream-onion-makhana', $buyer2, 4, 'Cream & onion is my favorite now. Addictive snack.'],
            ['organic-raw-makhana', $buyer, 5, 'Premium quality raw makhana. Made amazing kheer with it.'],
            ['tilkut-sesame-brittle', $buyer2, 5, 'Tilkut reminds me of my childhood in Gaya. Authentic taste!'],
            ['protein-makhana-trail-mix', $buyer, 4, 'Great trail mix for my gym sessions. Good protein content.'],
            ['premium-sattu-powder', $buyer2, 5, 'Best sattu powder I\'ve found online. Stone-ground texture is perfect for drinks.'],
            ['litti-chips', $buyer, 5, 'Finally a chip that tastes like real litti! Smoky and delicious.'],
            ['thekua-wheat-cookie', $buyer2, 5, 'Tastes exactly like the thekua my nani makes. Brought back so many memories!'],
            ['bihar-mango-pickle', $buyer, 4, 'Authentic taste, slightly less spicy than I expected but overall great quality.'],
            ['bihar-snack-sampler-box', $buyer2, 5, 'Gifted this to my friends in Bangalore. They absolutely loved every single item!'],
        ];

        foreach ($reviewData as [$slug, $user, $rating, $comment]) {
            if (!isset($productModels[$slug])) continue;
            $product = $productModels[$slug];
            Review::firstOrCreate(
                ['user_id' => $user->id, 'product_id' => $product->id],
                [
                    'rating' => $rating,
                    'comment' => $comment,
                    'is_approved' => true,
                ]
            );
        }

        // Update product ratings
        foreach ($productModels as $product) {
            $reviews = Review::where('product_id', $product->id)->where('is_approved', true);
            if ($reviews->count() > 0) {
                $product->update([
                    'avg_rating' => round($reviews->avg('rating'), 2),
                    'total_reviews' => $reviews->count(),
                ]);
            }
        }

        // ── 8. Wishlist ──
        Wishlist::firstOrCreate(
            ['user_id' => $buyer->id, 'product_id' => $productModels['peri-peri-makhana']->id]
        );
        Wishlist::firstOrCreate(
            ['user_id' => $buyer->id, 'product_id' => $productModels['tilkut-sesame-brittle']->id]
        );
        Wishlist::firstOrCreate(
            ['user_id' => $buyer2->id, 'product_id' => $productModels['bihar-snack-sampler-box']->id]
        );

        // ── 9. Sample Order ──
        $address = Address::where('user_id', $buyer->id)->first();
        $order = Order::firstOrCreate(
            ['user_id' => $buyer->id, 'order_number' => 'SNK-000001'],
            [
                'address_id' => $address->id,
                'status' => 'delivered',
                'subtotal' => 947.00,
                'shipping_charge' => 0,
                'tax' => 47.35,
                'discount' => 0,
                'total' => 994.35,
                'shipped_at' => now()->subDays(3),
                'delivered_at' => now()->subDay(),
                'shipping_address' => [
                    'name' => 'Rahul Kumar',
                    'phone' => '9876543220',
                    'address_line_1' => '123, Boring Road',
                    'city' => 'Patna',
                    'state' => 'Bihar',
                    'pincode' => '800001',
                ],
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'product_id' => $productModels['classic-roasted-makhana']->id],
            [
                'seller_id' => $seller1->id,
                'product_name' => $productModels['classic-roasted-makhana']->name,
                'variant_name' => null,
                'sku' => $productModels['classic-roasted-makhana']->sku,
                'quantity' => 2,
                'unit_price' => 299,
                'total' => 598,
                'status' => 'delivered',
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'product_id' => $productModels['mint-masala-makhana']->id],
            [
                'seller_id' => $seller1->id,
                'product_name' => $productModels['mint-masala-makhana']->name,
                'variant_name' => null,
                'sku' => $productModels['mint-masala-makhana']->sku,
                'quantity' => 1,
                'unit_price' => 349,
                'total' => 349,
                'status' => 'delivered',
            ]
        );

        // ── 10. Blog Posts ──
        $blogs = [
            [
                'title' => '10 Amazing Health Benefits of Makhana (Fox Nuts)',
                'slug' => '10-health-benefits-of-makhana',
                'excerpt' => 'Discover why makhana is considered a superfood and how it can boost your health with its incredible nutritional profile.',
                'content' => '<h2>What Makes Makhana a Superfood?</h2><p>Makhana, also known as fox nuts or lotus seeds, has been a staple in Bihar for centuries. Rich in protein, fiber, and essential minerals, these nutrient-dense pops are nature\'s perfect snack.</p><h3>1. Rich in Protein</h3><p>100g of makhana contains about 9.7g of protein, making it an excellent plant-based protein source.</p><h3>2. Low in Calories</h3><p>With only 347 calories per 100g, makhana is a guilt-free snack option.</p><h3>3. High in Antioxidants</h3><p>Makhana contains kaempferol, a potent antioxidant that fights inflammation and aging.</p><h3>4. Supports Heart Health</h3><p>Low sodium and high magnesium content makes makhana heart-friendly.</p><h3>5. Aids Weight Loss</h3><p>High fiber content keeps you full longer, reducing overeating.</p><h3>6. Regulates Blood Sugar</h3><p>The low glycemic index of makhana helps manage blood sugar levels.</p><h3>7. Strengthens Bones</h3><p>Rich in calcium and phosphorus, makhana supports bone health.</p><h3>8. Improves Digestion</h3><p>The fiber in makhana aids in smooth digestion and gut health.</p><h3>9. Anti-Aging Properties</h3><p>Antioxidants in makhana keep your skin youthful and radiant.</p><h3>10. Boosts Kidney Health</h3><p>In Ayurveda, makhana is recommended for healthy kidney function.</p>',
                'category' => 'Health',
                'tags' => ['makhana', 'health', 'superfood', 'nutrition'],
                'featured_image' => 'https://images.unsplash.com/photo-1630159376105-3ce0acd9e42b?w=800',
            ],
            [
                'title' => 'The Ultimate Makhana Kheer Recipe',
                'slug' => 'ultimate-makhana-kheer-recipe',
                'excerpt' => 'Learn how to make the perfect creamy makhana kheer — a beloved Bihari dessert that\'s both delicious and nutritious.',
                'content' => '<h2>Makhana Kheer — A Bihar Favourite</h2><p>Makhana Kheer is a rich, creamy dessert made with fox nuts and milk. It\'s a festive staple in Bihar, especially during Chhath Puja and Makar Sankranti.</p><h3>Ingredients</h3><ul><li>2 cups raw makhana</li><li>1 liter full cream milk</li><li>1/2 cup sugar (or jaggery)</li><li>4-5 green cardamom pods</li><li>2 tbsp ghee</li><li>Almonds and pistachios for garnish</li><li>A pinch of saffron strands</li></ul><h3>Instructions</h3><ol><li>Dry roast makhana in ghee until crunchy and golden</li><li>Let them cool and crush coarsely</li><li>Boil milk in a heavy-bottom pan, reduce to half</li><li>Add crushed makhana and cook for 10 minutes</li><li>Add sugar, cardamom, and saffron</li><li>Cook for 5 more minutes</li><li>Garnish with nuts and serve warm or chilled</li></ol>',
                'category' => 'Recipes',
                'tags' => ['recipe', 'makhana', 'kheer', 'dessert'],
                'featured_image' => 'https://images.unsplash.com/photo-1548907040-4baa42d10919?w=800',
            ],
            [
                'title' => 'The Rich History of Makhana Farming in Bihar',
                'slug' => 'history-makhana-farming-bihar',
                'excerpt' => 'Explore the centuries-old tradition of makhana cultivation in Bihar\'s Mithilanchal region and its journey to global recognition.',
                'content' => '<h2>From Bihar\'s Ponds to the World</h2><p>Bihar produces over 90% of India\'s makhana, which in turn accounts for 80% of the global production. The Mithilanchal region — Darbhanga, Madhubani, Saharsa, and Supaul — has been the heartland of makhana cultivation for centuries.</p><h3>The GI Tag</h3><p>In 2022, Bihar Makhana received the coveted Geographical Indication (GI) tag, recognizing Bihar as the authentic origin of this superfood.</p><h3>Traditional Harvesting</h3><p>Makhana harvesting is a labor-intensive process. Farmers wade into waist-deep muddy ponds to collect the seeds from the lotus plant (Euryale ferox). The seeds are then sun-dried and roasted over fire.</p><h3>Economic Impact</h3><p>The makhana industry supports lakhs of families in Bihar. With growing awareness of healthy snacking, demand has skyrocketed both nationally and internationally.</p>',
                'category' => 'Bihar Culture',
                'tags' => ['bihar', 'makhana', 'farming', 'culture', 'history'],
                'featured_image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=800',
            ],
            [
                'title' => '5 Quick Makhana Snack Recipes for Your Kids',
                'slug' => '5-quick-makhana-snack-recipes-kids',
                'excerpt' => 'Healthy doesn\'t have to be boring! Try these 5 kid-friendly makhana snack recipes.',
                'content' => '<h2>Kid-Friendly Makhana Snacks</h2><p>Getting kids to eat healthy can be challenging. Here are 5 makhana recipes that kids absolutely love!</p><h3>1. Chocolate Makhana</h3><p>Coat roasted makhana in melted dark chocolate. Let them set in the fridge for a healthy chocolate treat.</p><h3>2. Makhana Chaat</h3><p>Roasted makhana topped with diced onion, tomato, green chutney, and a squeeze of lemon.</p><h3>3. Honey Cinnamon Makhana</h3><p>Toss roasted makhana in warm honey and a sprinkle of cinnamon powder.</p><h3>4. Makhana Trail Mix</h3><p>Mix makhana with dried cranberries, almonds, and coconut flakes.</p><h3>5. Makhana Cutlets</h3><p>Crush makhana and mix with boiled potato, mild spices. Shape into cutlets and bake.</p>',
                'category' => 'Recipes',
                'tags' => ['recipes', 'kids', 'healthy', 'makhana'],
                'featured_image' => 'https://images.unsplash.com/photo-1604908177453-7462950a6a3b?w=800',
            ],
        ];

        $admin = User::where('email', 'admin@snackzar.com')->first();
        foreach ($blogs as $blog) {
            BlogPost::firstOrCreate(
                ['slug' => $blog['slug']],
                array_merge($blog, [
                    'author_id' => $admin?->id ?? $seller1->id,
                    'status' => 'published',
                    'published_at' => now()->subDays(rand(1, 30)),
                    'views_count' => rand(50, 500),
                    'meta_title' => $blog['title'],
                    'meta_description' => $blog['excerpt'],
                ])
            );
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('  Login credentials:');
        $this->command->info('  Admin:    admin@snackzar.com / password');
        $this->command->info('  Seller:   seller@snackzar.com / password');
        $this->command->info('  User:     user@snackzar.com / password');
        $this->command->info('  Delivery: delivery@snackzar.com / password');
    }
}
