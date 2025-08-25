<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PriceController extends Controller
{
    public function index()
    {
        return Inertia::render('Prices/Index', [
//            'variants' => $variants,
//            'filters' => $filters,
        ]);
    }
}
