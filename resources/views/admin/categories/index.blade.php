@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="page-header page-header--row">
    <div>
        <h1>Categories</h1>
        <p>Build the store header and product groups. Nest up to 3 levels: category → sub-category → sub-sub-category. Inactive items stay hidden on the store. Blogs are not part of this menu.</p>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <h2 class="card__title">{{ $editing ? 'Edit category' : 'Add category' }}</h2>
    <form method="POST"
          action="{{ $editing ? route('admin.categories.update', $editing) : route('admin.categories.store') }}"
          class="admin-form"
          enctype="multipart/form-data">
        @csrf
        @if ($editing)
            @method('PATCH')
        @endif

        <div class="form-grid">
            <label class="form-field">
                <span>Name *</span>
                <input type="text" name="name" value="{{ old('name', $editing?->name) }}" required maxlength="100" placeholder="Vegetable Seeds">
            </label>

            <label class="form-field">
                <span>Menu label</span>
                <input type="text" name="menu_label" value="{{ old('menu_label', $editing?->menu_label) }}" maxlength="100" placeholder="Leave blank to use the name">
                <small class="field-hint">Optional shorter label for the header and Top Categories cards.</small>
            </label>

            <label class="form-field">
                <span>URL slug</span>
                <input type="text" name="slug" value="{{ old('slug', $editing?->slug) }}" maxlength="120" placeholder="vegetable-seeds">
                <small class="field-hint">Used in /c/your-slug. Leave blank to generate from the name.</small>
            </label>

            <label class="form-field">
                <span>Emoji</span>
                <input type="text" name="emoji" value="{{ old('emoji', $editing?->emoji) }}" maxlength="16" placeholder="🌱">
                <small class="field-hint">Shown on Top Categories when no image is uploaded.</small>
            </label>

            <label class="form-field">
                <span>Parent category</span>
                <select name="parent_id">
                    <option value="">Top level (header item)</option>
                    @foreach ($parentOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('parent_id', $editing?->parent_id) === (string) $option->id)>
                            {{ str_repeat('— ', max(0, (int) $option->depth - 1)) }}{{ $option->name }}
                        </option>
                    @endforeach
                </select>
                <small class="field-hint">Leave empty to show this as a main header item.</small>
            </label>

            <label class="form-field">
                <span>Sort order</span>
                <input type="number" name="sort_order" value="{{ old('sort_order', $editing?->sort_order) }}" min="0" max="9999" placeholder="Auto">
            </label>

            <label class="form-field form-field--full">
                <span>Storefront description</span>
                <textarea name="description" rows="3" maxlength="500" placeholder="Shown on the category page and Top Categories cards.">{{ old('description', $editing?->description) }}</textarea>
            </label>

            <label class="form-field">
                <span>Homepage / header image</span>
                <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif">
                <small class="field-hint">Optional. Used on the Top Categories row.</small>
                @if ($editing?->image)
                    <div class="image-preview">
                        <img src="{{ $editing->image_url }}" alt="Current category image">
                        <label class="checkbox-field image-preview__remove">
                            <input type="checkbox" name="remove_image" value="1">
                            <span>Remove current image</span>
                        </label>
                    </div>
                @endif
            </label>

            <label class="form-field checkbox-field">
                <input type="checkbox" name="show_in_header" value="1" @checked(old('show_in_header', $editing?->show_in_header ?? true))>
                <span>Show in header menu</span>
            </label>

            <label class="form-field checkbox-field">
                <input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $editing?->show_on_home ?? false))>
                <span>Show in homepage Top Categories</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">{{ $editing ? 'Save category' : 'Add category' }}</button>
            @if ($editing)
                <a href="{{ route('admin.categories.index') }}" class="btn btn--outline">Cancel</a>
            @endif
        </div>
    </form>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Name</th>
                <th>Level</th>
                <th>Products</th>
                <th>Header</th>
                <th>Home</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr class="{{ $editingId === $category->id ? 'is-editing' : '' }}">
                    <td class="cell-actions">
                        <form method="POST" action="{{ route('admin.categories.move', $category) }}" class="inline-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="up">
                            <button type="submit" class="icon-btn" title="Move up" aria-label="Move {{ $category->name }} up">↑</button>
                        </form>
                        <form method="POST" action="{{ route('admin.categories.move', $category) }}" class="inline-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="down">
                            <button type="submit" class="icon-btn" title="Move down" aria-label="Move {{ $category->name }} down">↓</button>
                        </form>
                    </td>
                    <td>
                        <strong style="padding-left: {{ max(0, ((int) $category->depth - 1) * 18) }}px;">
                            {{ $category->emoji ? $category->emoji.' ' : '' }}{{ $category->name }}
                        </strong>
                        @if ($category->menu_label && $category->menu_label !== $category->name)
                            <small>Menu: {{ $category->menu_label }}</small>
                        @endif
                        <small><code>{{ $category->slug }}</code></small>
                    </td>
                    <td>
                        @if ((int) $category->depth === 1)
                            Category
                        @elseif ((int) $category->depth === 2)
                            Sub-category
                        @else
                            Sub-sub-category
                        @endif
                    </td>
                    <td>{{ $category->products_count ?? $category->products()->count() }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.categories.placement', $category) }}" class="inline-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="field" value="show_in_header">
                            <button type="submit" class="badge {{ $category->show_in_header ? 'badge--green' : 'badge--gray' }}" title="Toggle header">
                                {{ $category->show_in_header ? 'Yes' : 'No' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.categories.placement', $category) }}" class="inline-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="field" value="show_on_home">
                            <button type="submit" class="badge {{ $category->show_on_home ? 'badge--green' : 'badge--gray' }}" title="Toggle homepage">
                                {{ $category->show_on_home ? 'Yes' : 'No' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        @if ($category->is_active)
                            <span class="badge badge--green">Active</span>
                        @else
                            <span class="badge badge--gray">Inactive</span>
                        @endif
                    </td>
                    <td class="cell-actions">
                        <a href="{{ route('admin.categories.index', ['edit' => $category->id]) }}"
                           class="icon-btn"
                           title="Edit category"
                           aria-label="Edit {{ $category->name }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('admin.categories.toggle', $category) }}" class="inline-form">
                            @csrf
                            @method('PATCH')
                            @if ($category->is_active)
                                <button type="submit" class="icon-btn" title="Set inactive" aria-label="Set {{ $category->name }} inactive">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-5 0-9.27-3.11-11-8 1.02-2.87 2.9-5.17 5.21-6.61"/>
                                        <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c5 0 9.27 3.11 11 8a11.8 11.8 0 0 1-2.16 3.19"/>
                                        <path d="M1 1l22 22"/>
                                        <path d="M14.12 14.12a3 3 0 0 1-4.24-4.24"/>
                                    </svg>
                                </button>
                            @else
                                <button type="submit" class="icon-btn icon-btn--success" title="Set active" aria-label="Set {{ $category->name }} active">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            @endif
                        </form>
                        @if (($category->products_count ?? $category->products()->withTrashed()->count()) === 0 && $category->children->isEmpty())
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline-form" onsubmit="return confirm('Delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn icon-btn--danger" title="Delete category" aria-label="Delete {{ $category->name }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M3 6h18"/>
                                        <path d="M8 6V4h8v2"/>
                                        <path d="M6 6l1 16h10l1-16"/>
                                        <path d="M10 11v6"/>
                                        <path d="M14 11v6"/>
                                    </svg>
                                </button>
                            </form>
                        @else
                            <span class="icon-btn icon-btn--danger is-disabled" title="Cannot delete: category has products, archived products, or sub-categories" aria-disabled="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M3 6h18"/>
                                    <path d="M8 6V4h8v2"/>
                                    <path d="M6 6l1 16h10l1-16"/>
                                    <path d="M10 11v6"/>
                                    <path d="M14 11v6"/>
                                </svg>
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty-cell">No categories yet. Add a top-level category to start the header menu.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
