@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="page-header page-header--row">
    <div>
        <h1>Categories</h1>
        <p>Organize products by category on the storefront. Inactive categories are hidden from the store.</p>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <h2 class="card__title">Add Category</h2>
    <form method="POST" action="{{ route('admin.categories.store') }}" class="inline-add-form">
        @csrf
        <input type="text" name="name" placeholder="Category name" required maxlength="100">
        <button type="submit" class="btn btn--primary">Add</button>
    </form>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Products</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>
                        @if ($editingId === $category->id)
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="inline-add-form">
                                @csrf
                                @method('PATCH')
                                <input type="text" name="name" value="{{ old('name', $category->name) }}" required maxlength="100" autofocus>
                                <button type="submit" class="btn btn--sm btn--primary">Save</button>
                                <a href="{{ route('admin.categories.index') }}" class="btn btn--sm btn--outline">Cancel</a>
                            </form>
                        @else
                            <strong>{{ $category->name }}</strong>
                        @endif
                    </td>
                    <td><code>{{ $category->slug }}</code></td>
                    <td>{{ $category->products_count }}</td>
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
                                <button type="submit" class="btn btn--sm btn--outline">Set Inactive</button>
                            @else
                                <button type="submit" class="btn btn--sm btn--primary">Set Active</button>
                            @endif
                        </form>
                        @if ($category->products_count === 0)
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline-form" onsubmit="return confirm('Delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-cell">No categories yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
