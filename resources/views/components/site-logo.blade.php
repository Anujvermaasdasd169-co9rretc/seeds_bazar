@if ($logoUrl = \App\Models\Setting::logoUrl())
    <img src="{{ $logoUrl }}"
         alt="{{ config('app.name') }}"
         width="48"
         height="48"
         decoding="async"
         {{ $attributes->merge(['class' => 'site-logo__img']) }}>
@else
    <span {{ $attributes->merge(['class' => 'site-logo__emoji']) }} aria-hidden="true">🌱</span>
@endif
