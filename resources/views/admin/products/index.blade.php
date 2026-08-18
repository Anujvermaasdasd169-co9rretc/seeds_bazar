@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="page-header page-header--row">
    <div>
        <h1>Products</h1>
        <p>Manage seeds shown on the storefront</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn--primary">+ Add Product</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td class="cell-thumb">
                        @if ($product->image)
                            <img src="{{ $product->image_url }}" alt="" class="admin-thumb">
                        @else
                            <span class="admin-thumb-emoji">{{ $product->emoji }}</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $product->name }}</strong>
                        <small>{{ $product->unit }}</small>
                    </td>
                    <td>{{ $product->category->name }}</td>
                    <td>₹{{ number_format($product->price) }}</td>
                    <td>
                        @if ($product->is_active)
                            <span class="badge badge--green">Active</span>
                        @else
                            <span class="badge badge--gray">Hidden</span>
                        @endif
                    </td>
                    <td class="cell-actions">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn--sm btn--outline">Edit</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline-form" onsubmit="return confirm('Delete this product?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-cell">No products yet. <a href="{{ route('admin.products.create') }}">Add one</a></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
