<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'updated_at']);

        return response()
            ->view('sitemap', compact('products'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
