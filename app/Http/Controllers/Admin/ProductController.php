<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->filled('category_id')
            ? $request->integer('category_id')
            : null;

        $products = Product::with('category')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderByDesc('created_at')
            ->paginate(8)
            ->withQueryString();

        $categories = Category::orderBy('sort_order')->get();

        return view('admin.products.index', compact('products', 'categories', 'categoryId'));
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request->file('image'));
        }

        Product::create($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product added successfully.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        if ($request->boolean('remove_image')) {
            $product->deleteImageFile();
            $validated['image'] = null;
        } elseif ($request->hasFile('image')) {
            $product->deleteImageFile();
            $validated['image'] = $this->storeImage($request->file('image'));
        }

        $product->update($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->deleteImageFile();
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    private function storeImage(UploadedFile $file): string
    {
        return $file->store('products', 'public');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:100'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['emoji'] = $validated['emoji'] ?? '🌱';
        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['remove_image']);

        return $validated;
    }
}
