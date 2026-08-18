@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')
<div class="page-header">
    <h1>Add Product</h1>
    <p>New seed listing for the store</p>
</div>

<form method="POST" action="{{ route('admin.products.store') }}" class="admin-form card" enctype="multipart/form-data">
    @csrf
    @include('admin.products._form')
    <div class="form-actions">
        <button type="submit" class="btn btn--primary">Save Product</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn--outline">Cancel</a>
    </div>
</form>
@endsection
