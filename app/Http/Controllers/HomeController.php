<?php

namespace App\Http\Controllers;

use App\Models\Variant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $variants = Variant::all();

        return Inertia::render('Home', [
            'variants' => $variants
        ]);
    }
}
