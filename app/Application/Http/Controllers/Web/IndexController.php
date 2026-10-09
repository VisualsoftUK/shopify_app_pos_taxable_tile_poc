<?php

namespace App\Application\Http\Controllers\Web;

use App\Application\Http\Controllers\Controller;
use Illuminate\View\View;

class IndexController extends Controller
{
    public function index(): View
    {
        return view('index');
    }
}
