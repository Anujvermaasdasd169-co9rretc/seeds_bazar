@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
<div class="page-header">
    <h1>Edit Product</h1>
    <p>{{ $product->name }}</p>
</div>

<form method="POST" action="{{ route('admin.products.update', $product) }}" class="admin-form card" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.products._form', ['product' => $product])
    <div class="form-actions">
        <button type="submit" class="btn btn--primary">Update Product</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn--outline">Cancel</a>
    </div>
</form>
@endsection
