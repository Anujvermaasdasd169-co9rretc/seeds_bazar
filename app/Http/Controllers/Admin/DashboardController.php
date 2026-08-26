<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'productCount' => Product::count(),
            'categoryCount' => Category::count(),
            'activeCount' => Product::where('is_active', true)->count(),
            'contactCount' => ContactMessage::count(),
            'productMonthly' => $this->monthlyCreatedCounts(Product::class),
            'contactMonthly' => $this->monthlyCreatedCounts(ContactMessage::class),
            'recentContacts' => ContactMessage::query()->latest()->limit(5)->get(),
            'productsThisMonth' => Product::query()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'contactsThisMonth' => ContactMessage::query()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ]);
    }

    /**
     * @param  class-string<Model>  $model
     * @return array{labels: list<string>, values: list<int>, max: int, total: int}
     */
    private function monthlyCreatedCounts(string $model, int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M');
            $values[] = $model::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        $max = max(array_merge($values, [1]));

        return [
            'labels' => $labels,
            'values' => $values,
            'max' => $max,
            'total' => array_sum($values),
        ];
    }
}
