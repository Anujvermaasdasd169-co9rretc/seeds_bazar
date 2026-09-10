<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $editingId = (int) $request->query('edit');
        $editing = $editingId ? Category::find($editingId) : null;

        return view('admin.categories.index', [
            'categories' => Category::flattenedTree(),
            'parentOptions' => Category::parentOptions($editing?->id),
            'editing' => $editing,
            'editingId' => $editingId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request->file('image'));
        }

        Category::create($validated);

        return back()->with('success', 'Category added. It will appear on the storefront where you enabled it.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validatedPayload($request, $category);

        if ($request->boolean('remove_image')) {
            $category->deleteImageFile();
            $validated['image'] = null;
        } elseif ($request->hasFile('image')) {
            $category->deleteImageFile();
            $validated['image'] = $this->storeImage($request->file('image'));
        }

        $category->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        $status = $category->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Category {$status}. It will ".($category->is_active ? 'show' : 'not show').' on the storefront.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->withTrashed()->exists()) {
            return back()->withErrors(['category' => 'Cannot delete a category that has products, including archived products. Move the products to another category first.']);
        }

        if ($category->children()->exists()) {
            return back()->withErrors(['category' => 'Cannot delete a category that has sub-categories. Move or delete them first.']);
        }

        $category->deleteImageFile();
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function move(Request $request, Category $category): RedirectResponse
    {
        $direction = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ])['direction'];

        $siblings = Category::query()
            ->where('parent_id', $category->parent_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $index = $siblings->search(fn (Category $item) => $item->id === $category->id);
        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || $swapIndex < 0 || $swapIndex >= $siblings->count()) {
            return back();
        }

        $ordered = $siblings->values();
        $moving = $ordered->splice($index, 1)->first();
        $ordered->splice($swapIndex, 0, [$moving]);

        foreach ($ordered->values() as $position => $item) {
            $item->update(['sort_order' => $position + 1]);
        }

        return back()->with('success', 'Category order updated.');
    }

    public function togglePlacement(Request $request, Category $category): RedirectResponse
    {
        $field = $request->validate([
            'field' => ['required', 'in:show_in_header,show_on_home'],
        ])['field'];

        $category->update([$field => ! $category->{$field}]);

        return back()->with('success', 'Category display updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request, ?Category $category = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'menu_label' => ['nullable', 'string', 'max:100'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string', 'max:500'],
            'slug' => ['nullable', 'string', 'max:120'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'show_in_header' => ['sometimes', 'boolean'],
            'show_on_home' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($category && $parentId && (int) $parentId === $category->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be its own parent.',
            ]);
        }

        if ($category && $parentId) {
            $parent = Category::query()->with('parent.parent')->find($parentId);
            if ($parent?->isSelfOrDescendantOf($category->id)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'A category cannot be moved under one of its own sub-categories.',
                ]);
            }
        }

        $subject = $category;
        if (Category::wouldExceedMaxDepth($parentId ? (int) $parentId : null, $subject)) {
            throw ValidationException::withMessages([
                'parent_id' => 'Categories can only nest up to 3 levels (category → sub-category → sub-sub-category).',
            ]);
        }

        $parentId = $parentId ? (int) $parentId : null;
        $sortOrder = isset($validated['sort_order']) && $validated['sort_order'] !== null && $validated['sort_order'] !== ''
            ? (int) $validated['sort_order']
            : ((Category::query()->where('parent_id', $parentId)->max('sort_order') ?? 0) + 1);

        return [
            'parent_id' => $parentId,
            'name' => $validated['name'],
            'menu_label' => $validated['menu_label'] ?? null,
            'emoji' => $validated['emoji'] ?? null,
            'description' => $validated['description'] ?? null,
            'slug' => Category::uniqueSlug($validated['name'], $category?->id, $validated['slug'] ?? null),
            'sort_order' => $sortOrder,
            'show_in_header' => $request->boolean('show_in_header'),
            'show_on_home' => $request->boolean('show_on_home'),
            'is_active' => $category?->is_active ?? true,
        ];
    }

    private function storeImage(UploadedFile $file): string
    {
        return $file->store('categories', 'public');
    }
}
