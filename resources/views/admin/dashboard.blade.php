@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1>Dashboard</h1>
    <p>Welcome, {{ auth()->user()->name }}</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-card__value">{{ $productCount }}</span>
        <span class="stat-card__label">Total Products</span>
    </div>
    <div class="stat-card">
        <span class="stat-card__value">{{ $activeCount }}</span>
        <span class="stat-card__label">Active on Store</span>
    </div>
    <div class="stat-card">
        <span class="stat-card__value">{{ $categoryCount }}</span>
        <span class="stat-card__label">Categories</span>
    </div>
</div>

<div class="quick-actions">
    <a href="{{ route('admin.products.create') }}" class="btn btn--primary">+ Add Product</a>
    <a href="{{ route('admin.products.index') }}" class="btn btn--outline">Manage Products</a>
    <a href="{{ route('admin.categories.index') }}" class="btn btn--outline">Manage Categories</a>
</div>
@endsection
