<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HowItWorksController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('HowItWorks');
    }
}
