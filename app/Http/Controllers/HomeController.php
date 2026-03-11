<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $featuredProducts = Cache::remember('homepage:featured', 300, function () {
            return Product::active()
                ->inStock()
                ->featured()
                ->with(['primaryImage', 'category'])
                ->latest()
                ->limit(8)
                ->get();
        });

        $newArrivals = Cache::remember('homepage:new_arrivals', 300, function () {
            return Product::active()
                ->inStock()
                ->with(['primaryImage', 'category'])
                ->latest()
                ->limit(8)
                ->get();
        });

        $categories = Cache::remember('homepage:categories', 3600, function () {
            return Category::active()
                ->root()
                ->withCount(['products' => fn ($q) => $q->active()->inStock()])
                ->orderBy('sort_order')
                ->limit(6)
                ->get();
        });

        $topRated = Cache::remember('homepage:top_rated', 300, function () {
            return Product::active()
                ->inStock()
                ->where('avg_rating', '>=', 4)
                ->with(['primaryImage', 'category'])
                ->orderByDesc('avg_rating')
                ->limit(4)
                ->get();
        });

        $recentReviews = Cache::remember('homepage:reviews', 300, function () {
            return Review::approved()
                ->with(['user:id,name,avatar', 'product:id,name,slug'])
                ->where('rating', '>=', 4)
                ->latest()
                ->limit(6)
                ->get();
        });

        $stats = Cache::remember('homepage:stats', 600, function () {
            return [
                'products' => Product::active()->count(),
                'categories' => Category::active()->count(),
                'happy_customers' => Review::approved()->distinct('user_id')->count('user_id'),
            ];
        });

        return Inertia::render('Home', [
            'featuredProducts' => $featuredProducts,
            'newArrivals' => $newArrivals,
            'categories' => $categories,
            'topRated' => $topRated,
            'recentReviews' => $recentReviews,
            'stats' => $stats,
        ]);
    }

    public function products(Request $request): Response
    {
        $query = Product::active()->inStock()->with(['primaryImage', 'category']);

        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $sortBy = $request->get('sort', 'latest');
        match ($sortBy) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'rating' => $query->orderByDesc('avg_rating'),
            'popular' => $query->orderByDesc('total_sold'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::active()
            ->root()
            ->withCount(['products' => fn ($q) => $q->active()->inStock()])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['category', 'search', 'min_price', 'max_price', 'featured', 'sort']),
        ]);
    }

    public function productShow(string $slug): Response
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->with(['category', 'images', 'variants' => fn ($q) => $q->active(), 'seller:id,name'])
            ->firstOrFail();

        $reviews = $product->reviews()
            ->approved()
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(10);

        $relatedProducts = Product::active()
            ->inStock()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['primaryImage'])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return Inertia::render('Products/Show', [
            'product' => $product,
            'reviews' => $reviews,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('About');
    }

    public function contact(): Response
    {
        return Inertia::render('Contact');
    }
}
