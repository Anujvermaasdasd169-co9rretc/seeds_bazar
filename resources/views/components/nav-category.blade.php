@props(['category', 'level' => 1])

@php
    $hasChildren = $category->children->isNotEmpty();
    $href = route('shop.category', $category);
    $linkClass = $level === 1 ? 'header-nav__link' : 'header-nav__drop-item';
@endphp

@if ($hasChildren)
    <div class="header-nav__item {{ $level > 1 ? 'header-nav__item--sub' : '' }}" data-nav-item>
        <a href="{{ $href }}"
           class="{{ $linkClass }} {{ $level === 1 ? 'header-nav__link--parent' : '' }}"
           data-nav-category="{{ $category->slug }}"
           aria-haspopup="true">
            <span>{{ $category->displayName() }}</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true">
                @if ($level === 1)
                    <path d="M6 9l6 6 6-6"/>
                @else
                    <path d="M9 6l6 6-6 6"/>
                @endif
            </svg>
        </a>
        <div class="{{ $level === 1 ? 'header-nav__drop' : 'header-nav__flyout' }}" hidden>
            @foreach ($category->children as $child)
                <x-nav-category :category="$child" :level="$level + 1" />
            @endforeach
        </div>
    </div>
@else
    <a href="{{ $href }}" class="{{ $linkClass }}" data-nav-category="{{ $category->slug }}">{{ $category->displayName() }}</a>
@endif
