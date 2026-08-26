@props([
    'title',
    'subtitle' => 'Last 6 months',
    'labels' => [],
    'values' => [],
    'max' => 1,
    'color' => '#2d6a4f',
])

@php
    $count = count($values);
    $width = 320;
    $height = 110;
    $padX = 16;
    $padY = 14;
    $innerW = $width - ($padX * 2);
    $innerH = $height - ($padY * 2);
    $points = [];
    $area = '';

    foreach ($values as $i => $value) {
        $x = $padX + ($count > 1 ? ($i / ($count - 1)) * $innerW : $innerW / 2);
        $y = $padY + $innerH - (max(0, (int) $value) / max(1, (int) $max)) * $innerH;
        $points[] = round($x, 1).','.round($y, 1);
    }

    $polyline = implode(' ', $points);
    if ($points) {
        $first = explode(',', $points[0]);
        $last = explode(',', $points[count($points) - 1]);
        $area = $polyline.' '.$last[0].','.($height - $padY).' '.$first[0].','.($height - $padY);
    }
@endphp

<article class="chart-card">
    <div class="chart-card__head">
        <div>
            <h2>{{ $title }}</h2>
            <p>{{ $subtitle }}</p>
        </div>
        <strong class="chart-card__sum">{{ array_sum($values) }}</strong>
    </div>

    <svg class="mini-chart" viewBox="0 0 {{ $width }} {{ $height }}" role="img" aria-label="{{ $title }} monthly chart">
        <defs>
            <linearGradient id="{{ $attributes->get('id', 'g'.md5($title)) }}" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="{{ $color }}" stop-opacity="0.32"/>
                <stop offset="100%" stop-color="{{ $color }}" stop-opacity="0.03"/>
            </linearGradient>
        </defs>
        @foreach ($values as $i => $value)
            @php
                $barW = $count > 0 ? $innerW / ($count * 1.7) : 8;
                $x = $padX + ($count > 1 ? ($i / ($count - 1)) * $innerW : $innerW / 2);
                $barH = (max(0, (int) $value) / max(1, (int) $max)) * $innerH;
                $y = $padY + $innerH - $barH;
            @endphp
            <rect x="{{ round($x - $barW / 2, 1) }}" y="{{ round($y, 1) }}" width="{{ round($barW, 1) }}" height="{{ round(max($barH, 2), 1) }}" rx="4" fill="{{ $color }}" opacity="0.18"/>
        @endforeach
        @if ($area)
            <polygon points="{{ $area }}" fill="url(#{{ $attributes->get('id', 'g'.md5($title)) }})"/>
            <polyline points="{{ $polyline }}" fill="none" stroke="{{ $color }}" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
        @endif
        @foreach ($values as $i => $value)
            @php
                $x = $padX + ($count > 1 ? ($i / ($count - 1)) * $innerW : $innerW / 2);
                $y = $padY + $innerH - (max(0, (int) $value) / max(1, (int) $max)) * $innerH;
            @endphp
            <circle cx="{{ round($x, 1) }}" cy="{{ round($y, 1) }}" r="4" fill="#fff" stroke="{{ $color }}" stroke-width="2.2"/>
        @endforeach
    </svg>

    <div class="mini-chart__axis">
        @foreach ($labels as $i => $label)
            <span>
                {{ $label }}
                <small>{{ $values[$i] ?? 0 }}</small>
            </span>
        @endforeach
    </div>
</article>
