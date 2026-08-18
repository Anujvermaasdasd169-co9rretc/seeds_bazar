@if ($logoUrl = \App\Models\Setting::logoUrl())
    <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => 'site-logo__img']) }}>
@else
    <span {{ $attributes->merge(['class' => 'site-logo__emoji']) }} aria-hidden="true">🌱</span>
@endif
