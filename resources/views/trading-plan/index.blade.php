@extends('layouts.app')
@section('title', 'Trading Plan')

@section('content')
<div class="mb-6">
    <h1 class="text-xl sm:text-2xl font-bold gradient-text">⚡ Kalkulator Trading Plan</h1>
    <p class="text-gray-400 mt-1 text-sm">Generate rencana trading 30 hari dengan compound interest otomatis</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

    {{-- Form Konfigurasi --}}
    <div class="tech-card rounded-2xl p-4 sm:p-6">
        <h2 class="text-lg font-bold mb-5">
            <i class="fas fa-sliders-h text-cyan-400 mr-2"></i>Konfigurasi Trading
        </h2>

        <form action="{{ route('trading-plan.store') }}" method="POST" id="planForm" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
            @csrf

            {{-- Mata Uang --}}
            <div class="mb-5">
                <label class="form-label">Mata Uang</label>
                <div class="flex gap-2">
                    <button type="button" value="IDR"
                        class="currency-btn {{ old('currency', $plan?->currency ?? 'IDR') === 'IDR' ? 'active' : '' }}">
                        🇮🇩 IDR
                    </button>
                    <button type="button" value="USD"
                        class="currency-btn {{ old('currency', $plan?->currency) === 'USD' ? 'active' : '' }}">
                        🇺🇸 USD
                    </button>
                    <button type="button" value="USC"
                        class="currency-btn {{ old('currency', $plan?->currency) === 'USC' ? 'active' : '' }}">
                        ¢ USC
                    </button>
                </div>
                <input type="hidden" name="currency" id="currency-input"
                    value="{{ old('currency', $plan?->currency ?? 'IDR') }}">
                @error('currency')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Modal --}}
            <div class="mb-5">
                <label for="capital" class="form-label">Modal Awal</label>
                <input type="number" id="capital" name="capital"
                    value="{{ old('capital', $plan?->capital ?? 5000000) }}"
                    class="form-input" placeholder="5000000" min="50">
                <p class="text-gray-600 text-xs mt-1">Min: IDR 100.000 | USD $50 | USC 5.000¢</p>
                @error('capital')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Tipe Trader --}}
            <div class="mb-5">
                <label class="form-label">Tipe Trader</label>
                <div class="flex gap-2">
                    @foreach([
                        'conservative' => ['emoji' => '🛡️', 'label' => 'Conservative', 'desc' => '1%/hari'],
                        'moderate'     => ['emoji' => '⚖️', 'label' => 'Moderate',     'desc' => '2%/hari'],
                        'aggressive'   => ['emoji' => '🔥', 'label' => 'Aggressive',   'desc' => '3%/hari'],
                    ] as $type => $info)
                    <button type="button" data-value="{{ $type }}"
                        class="trader-btn {{ old('trader_type', $plan?->trader_type ?? 'moderate') === $type ? 'active' : '' }}">
                        <div class="text-base">{{ $info['emoji'] }}</div>
                        <div class="text-xs font-semibold">{{ $info['label'] }}</div>
                        <div class="text-xs text-gray-500">{{ $info['desc'] }}</div>
                    </button>
                    @endforeach
                </div>
                <input type="hidden" name="trader_type" id="trader-type-input"
                    value="{{ old('trader_type', $plan?->trader_type ?? 'moderate') }}">
                @error('trader_type')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Currency Pair --}}
            <div class="mb-5">
                <label for="currency_pair" class="form-label">Currency Pair</label>
                <select id="currency_pair" name="currency_pair" class="form-select">
                    @foreach($pairs as $pair)
                        <option value="{{ $pair }}"
                            {{ old('currency_pair', $plan?->currency_pair ?? 'EUR/USD') === $pair ? 'selected' : '' }}>
                            {{ $pair }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- SL & TP --}}
            <div class="grid grid-cols-2 gap-3 mb-5">
                <div>
                    <label for="stop_loss_pips" class="form-label">Stop Loss (pips)</label>
                    <input type="number" id="stop_loss_pips" name="stop_loss_pips"
                        value="{{ old('stop_loss_pips', $plan?->stop_loss_pips ?? 20) }}"
                        class="form-input" min="5" max="200">
                    @error('stop_loss_pips')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="take_profit_pips" class="form-label">Take Profit (pips)</label>
                    <input type="number" id="take_profit_pips" name="take_profit_pips"
                        value="{{ old('take_profit_pips', $plan?->take_profit_pips ?? 40) }}"
                        class="form-input" min="5" max="500">
                    @error('take_profit_pips')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Risk Per Trade --}}
            <div class="mb-6">
                <label for="risk_per_trade" class="form-label">
                    Risk Per Trade (%) <span class="text-gray-600">— opsional</span>
                </label>
                <input type="number" id="risk_per_trade" name="risk_per_trade"
                    value="{{ old('risk_per_trade', $plan?->risk_per_trade ?? 2) }}"
                    class="form-input" min="0.5" max="10" step="0.5">
            </div>

            <div class="mt-6">
                <button type="submit" 
                    class="btn-primary w-full py-3.5 text-lg"
                    :disabled="isSubmitting"
                    :class="{ 'opacity-75 cursor-not-allowed': isSubmitting }">
                    <template x-if="!isSubmitting">
                        <span><i class="fas fa-bolt mr-2"></i>Generate Trading Plan</span>
                    </template>
                    <template x-if="isSubmitting">
                        <span><i class="fas fa-spinner fa-spin mr-2"></i>Generating...</span>
                    </template>
                </button>
                <p class="text-xs text-gray-500 mt-2 text-center">
                    Plan akan tersimpan dan bisa diakses kapan saja
                </p>
            </div>
        </form>
    </div>

    {{-- Hasil Trading Plan --}}
    <div class="lg:col-span-2 space-y-4">
        @if($planData)

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
                <div class="stat-card text-center">
                    <div class="text-gray-400 text-xs mb-1">Modal Awal</div>
                    <div class="font-bold text-white text-sm">
                        @if($plan->currency === 'IDR')
                            Rp {{ number_format($plan->capital, 0, ',', '.') }}
                        @elseif($plan->currency === 'USC')
                            {{ number_format($plan->capital, 0, ',', '.') }} ¢
                        @else
                            ${{ number_format($plan->capital, 2) }}
                        @endif
                    </div>
                </div>
                <div class="stat-card text-center">
                    <div class="text-gray-400 text-xs mb-1">Modal Hari-30</div>
                    <div class="font-bold text-green-400 text-sm">
                        @if($plan->currency === 'IDR')
                            Rp {{ number_format($planData['final_capital'], 0, ',', '.') }}
                        @elseif($plan->currency === 'USC')
                            {{ number_format($planData['final_capital'], 0, ',', '.') }} ¢
                        @else
                            ${{ number_format($planData['final_capital'], 2) }}
                        @endif
                    </div>
                    <div class="text-gray-500 text-xs">+{{ round($planData['growth_pct'], 1) }}%</div>
                </div>
                <div class="stat-card text-center">
                    <div class="text-gray-400 text-xs mb-1">R:R Ratio</div>
                    <div class="font-bold text-cyan-400 text-sm">1:{{ $plan->rr_ratio }}</div>
                    <div class="text-gray-500 text-xs capitalize">{{ $plan->trader_type }}</div>
                </div>
                <div class="stat-card text-center">
                    <div class="text-gray-400 text-xs mb-1">Export Plan</div>
                    <div class="flex justify-center gap-2 mt-1">
                        <a href="{{ route('trading-plan.export.excel', $plan) }}" 
                           class="btn-icon-sm text-green-400 hover:text-green-300" 
                           title="Export Excel">
                            <i class="fas fa-file-excel"></i>
                        </a>
                        <a href="{{ route('trading-plan.export.pdf', $plan) }}" 
                           class="btn-icon-sm text-red-400 hover:text-red-300" 
                           title="Export PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <a href="{{ route('trading-plan.history') }}" 
                           class="btn-icon-sm text-blue-400 hover:text-blue-300" 
                           title="View History">
                            <i class="fas fa-history"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tabel 30 Hari --}}
            <div class="tech-card rounded-2xl p-4 sm:p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
                    <h2 class="text-sm sm:text-base font-bold min-w-0">
                        <i class="fas fa-table text-green-400 mr-2"></i>Rencana 30 Hari
                    </h2>
                    <a href="{{ route('trading-plan.history') }}" class="text-xs text-gray-500 hover:text-gray-300 shrink-0">
                        <i class="fas fa-history mr-1"></i>Riwayat
                    </a>
                </div>
                <div class="overflow-auto max-h-[480px] rounded-lg">
                    <table class="data-table text-xs">
                        <thead class="sticky top-0 bg-gray-900 z-10">
                            <tr>
                                <th class="text-center w-12">Hari</th>
                                <th>Modal</th>
                                <th>Target Profit</th>
                                <th>Risk Amount</th>
                                <th>Lot Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($planData['days'] as $day)
                            <tr>
                                <td class="text-center text-cyan-400 font-bold">{{ $day['day'] }}</td>
                                <td class="font-medium">{{ $day['capital_formatted'] }}</td>
                                <td class="text-green-400 font-semibold">+{{ $day['target_formatted'] }}</td>
                                <td class="text-red-400">{{ $day['risk_formatted'] }}</td>
                                <td class="text-yellow-400 font-mono">{{ $day['lot_size'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="sticky bottom-0 bg-gray-900 border-t-2 border-cyan-500/30">
                            <tr>
                                <td class="text-center text-white font-bold">30</td>
                                <td class="font-bold text-green-400" colspan="4">
                                    Total: +@if($plan->currency === 'IDR')Rp {{ number_format($planData['total_profit'], 0, ',', '.') }}@elseif($plan->currency === 'USC'){{ number_format($planData['total_profit'], 0, ',', '.') }} ¢@else${{ number_format($planData['total_profit'], 2) }}@endif
                                    ({{ round($planData['growth_pct'], 1) }}% growth)
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        @else
            <div class="tech-card rounded-2xl p-16 text-center h-full flex flex-col items-center justify-center">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-cyan-500/10 to-green-500/10 border border-cyan-500/20 flex items-center justify-center mx-auto mb-5">
                    <i class="fas fa-chart-line text-3xl text-cyan-400/50"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-400 mb-2">Belum Ada Trading Plan</h3>
                <p class="text-gray-600 text-sm max-w-xs">
                    Isi konfigurasi di sebelah kiri dan klik "Generate Trading Plan" untuk melihat rencana 30 hari Anda.
                </p>
            </div>
        @endif
    </div>

</div>

{{-- Real-time Preview Container (will be populated by JavaScript) --}}
<div id="previewContainer" class="hidden">
    <!-- Preview will be loaded here dynamically -->
</div>

@push('scripts')
<script src="{{ asset('js/trading-preview.js') }}"></script>
<style>
    .preview-card {
        @apply bg-gray-800/50 border border-gray-700 rounded-xl p-3;
    }
    .preview-label {
        @apply text-xs text-gray-500 uppercase tracking-wider mb-1;
    }
    .preview-value {
        @apply text-lg font-bold;
    }
    .preview-table {
        @apply w-full text-sm;
    }
    .preview-table th {
        @apply bg-gray-800 text-gray-400 text-left px-3 py-2 font-semibold;
    }
    .preview-table td {
        @apply border-t border-gray-800 px-3 py-2;
    }
    .preview-table tr:hover {
        @apply bg-gray-800/30;
    }
</style>
@endpush
@endsection
