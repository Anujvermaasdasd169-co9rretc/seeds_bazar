@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="dash">
    <section class="dash-hero">
        <div>
            <p class="dash-hero__kicker">{{ now()->format('l, d M Y') }}</p>
            <h1>Welcome back, {{ auth()->user()->name }}</h1>
            <p>Here’s a live snapshot of your store, products, and Contact Us activity.</p>
        </div>
        <div class="dash-hero__pills">
            <span>{{ $productsThisMonth }} products added this month</span>
            <span>{{ $contactsThisMonth }} contact queries this month</span>
        </div>
    </section>

    <div class="stats-grid">
        <article class="stat-card stat-card--green">
            <span class="stat-card__icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h12v4H6z"/><path d="M6 6l-2 14h16L18 6"/><path d="M9 10v2"/><path d="M15 10v2"/></svg>
            </span>
            <span class="stat-card__value">{{ $productCount }}</span>
            <span class="stat-card__label">Total Products</span>
        </article>
        <article class="stat-card stat-card--mint">
            <span class="stat-card__icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </span>
            <span class="stat-card__value">{{ $activeCount }}</span>
            <span class="stat-card__label">Active on Store</span>
        </article>
        <article class="stat-card stat-card--gold">
            <span class="stat-card__icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16"/><path d="M4 4h7v7H4z"/><path d="M13 4h7v4h-7z"/><path d="M13 10h7v10h-7z"/></svg>
            </span>
            <span class="stat-card__value">{{ $categoryCount }}</span>
            <span class="stat-card__label">Categories</span>
        </article>
        <article class="stat-card stat-card--coral">
            <span class="stat-card__icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v12H5.17L4 17.17V4z"/><path d="M8 8h8"/><path d="M8 12h5"/></svg>
            </span>
            <span class="stat-card__value">{{ $contactCount }}</span>
            <span class="stat-card__label">Contact Us</span>
        </article>
    </div>

    <div class="charts-grid">
        <x-admin-mini-chart
            id="chart-contacts"
            title="Contact Us"
            :labels="$contactMonthly['labels']"
            :values="$contactMonthly['values']"
            :max="$contactMonthly['max']"
            color="#e76f51"
        />
        <x-admin-mini-chart
            id="chart-products"
            title="Total Products"
            :labels="$productMonthly['labels']"
            :values="$productMonthly['values']"
            :max="$productMonthly['max']"
            color="#2d6a4f"
        />
    </div>

   
</div>
@endsection
