{{-- Alert Component --}}
@props(['type' => 'info', 'dismissible' => false, 'icon' => true])

@php
    $types = [
        'success' => ['bg' => 'bg-green-500/10', 'border' => 'border-green-500/30', 'text' => 'text-green-400', 'icon' => 'check-circle'],
        'error' => ['bg' => 'bg-red-500/10', 'border' => 'border-red-500/30', 'text' => 'text-red-400', 'icon' => 'exclamation-circle'],
        'warning' => ['bg' => 'bg-yellow-500/10', 'border' => 'border-yellow-500/30', 'text' => 'text-yellow-400', 'icon' => 'exclamation-triangle'],
        'info' => ['bg' => 'bg-blue-500/10', 'border' => 'border-blue-500/30', 'text' => 'text-blue-400', 'icon' => 'info-circle'],
    ];
    
    $config = $types[$type] ?? $types['info'];
@endphp

<div class="{{ $config['bg'] }} {{ $config['border'] }} {{ $config['text'] }} rounded-xl p-4 mb-4 border" role="alert">
    <div class="flex items-start">
        @if($icon)
            <div class="flex-shrink-0 mr-3">
                <i class="fas fa-{{ $config['icon'] }} text-lg"></i>
            </div>
        @endif
        <div class="flex-1">
            {{ $slot }}
        </div>
        @if($dismissible)
            <button type="button" class="ml-3 text-gray-400 hover:text-white" data-dismiss="alert" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        @endif
    </div>
</div>