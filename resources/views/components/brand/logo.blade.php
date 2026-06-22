@props([
    'variant' => 'horizontal',
])

@php
    $source = match ($variant) {
        'stacked' => asset('branding/sdm-logo-stacked.svg'),
        default => asset('branding/sdm-logo-horizontal.svg'),
    };

    $defaultClass = $variant === 'stacked' ? 'h-auto w-full max-w-[240px]' : 'h-11 w-auto';
@endphp

<img
    src="{{ $source }}"
    alt="Logo Surya Duta Multindo"
    {{ $attributes->merge(['class' => $defaultClass]) }}
/>
