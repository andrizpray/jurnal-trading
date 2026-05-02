{{-- Loading Spinner Component --}}
@props(['size' => 'md', 'text' => null, 'fullscreen' => false])

@php
    $sizes = [
        'sm' => 'w-4 h-4',
        'md' => 'w-8 h-8', 
        'lg' => 'w-12 h-12',
        'xl' => 'w-16 h-16'
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($fullscreen)
    <div class="fixed inset-0 bg-gray-950/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="{{ $sizeClass }} border-4 border-gray-700 border-t-cyan-500 rounded-full animate-spin mx-auto mb-4"></div>
            @if($text)
                <p class="text-gray-300 font-medium">{{ $text }}</p>
            @endif
        </div>
    </div>
@else
    <div class="inline-flex items-center justify-center">
        <div class="{{ $sizeClass }} border-4 border-gray-700 border-t-cyan-500 rounded-full animate-spin"></div>
        @if($text)
            <span class="ml-3 text-gray-400">{{ $text }}</span>
        @endif
    </div>
@endif