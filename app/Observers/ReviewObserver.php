<?php

namespace App\Observers;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;

class ReviewObserver
{
    public function saved(Review $review): void
    {
        Cache::forget('homepage:reviews');
        Cache::forget('homepage:stats');
    }

    public function deleted(Review $review): void
    {
        Cache::forget('homepage:reviews');
        Cache::forget('homepage:stats');
    }
}
