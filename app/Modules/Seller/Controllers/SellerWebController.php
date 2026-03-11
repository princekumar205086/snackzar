<?php

namespace App\Modules\Seller\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SellerWebController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Seller/Dashboard');
    }

    public function profile(): Response
    {
        return Inertia::render('Seller/Profile');
    }

    public function payouts(): Response
    {
        return Inertia::render('Seller/Payouts/Index');
    }

    public function products(): Response
    {
        return Inertia::render('Seller/Products/Index');
    }

    public function productCreate(): Response
    {
        return Inertia::render('Seller/Products/Create');
    }

    public function productEdit(int $id): Response
    {
        return Inertia::render('Seller/Products/Edit', ['id' => $id]);
    }

    public function orders(): Response
    {
        return Inertia::render('Seller/Orders/Index');
    }

    public function orderShow(int $id): Response
    {
        return Inertia::render('Seller/Orders/Show', ['id' => $id]);
    }
}
