<?php

namespace App\Modules\Delivery\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryWebController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Delivery/Dashboard');
    }

    public function profile(): Response
    {
        return Inertia::render('Delivery/Profile');
    }

    public function assignments(): Response
    {
        return Inertia::render('Delivery/Assignments/Index');
    }

    public function assignmentShow(int $id): Response
    {
        return Inertia::render('Delivery/Assignments/Show', ['id' => $id]);
    }
}
