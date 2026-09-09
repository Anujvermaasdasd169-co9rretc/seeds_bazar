<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at']);

        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'updated_at']);

        return response()
            ->view('sitemap', compact('products', 'categories'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
