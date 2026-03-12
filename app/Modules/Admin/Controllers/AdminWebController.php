<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AdminWebController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard');
    }

    public function users(): Response
    {
        return Inertia::render('Admin/Users/Index');
    }

    public function userShow(int $id): Response
    {
        return Inertia::render('Admin/Users/Show', ['id' => $id]);
    }

    public function orders(): Response
    {
        return Inertia::render('Admin/Orders/Index');
    }

    public function orderShow(int $id): Response
    {
        return Inertia::render('Admin/Orders/Show', ['id' => $id]);
    }

    public function sellers(): Response
    {
        return Inertia::render('Admin/Sellers/Index');
    }

    public function deliveryPartners(): Response
    {
        return Inertia::render('Admin/DeliveryPartners/Index');
    }

    public function categories(): Response
    {
        return Inertia::render('Admin/Categories/Index');
    }

    public function blog(): Response
    {
        return Inertia::render('Admin/Blog/Index');
    }

    public function blogCreate(): Response
    {
        return Inertia::render('Admin/Blog/Create');
    }

    public function blogEdit(int $id): Response
    {
        return Inertia::render('Admin/Blog/Edit', ['id' => $id]);
    }

    public function coupons(): Response
    {
        return Inertia::render('Admin/Coupons/Index');
    }
}
