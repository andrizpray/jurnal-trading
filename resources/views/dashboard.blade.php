@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="tech-card rounded-2xl p-4 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-cyan-500/5 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-green-500/5 rounded-full translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold break-words">
                    Selamat Datang, <span class="gradient-text">{{ Auth::user()->name }}</span>! 👋
                </h1>
                <p class="text-gray-400 mt-1 text-sm">Dashboard Trading Journal — Pantau performa trading Anda secara real-time</p>
            </div>
            <div class="flex gap-3 shrink-0">
                <a href="{{ route('journal.create') }}" class="btn-primary text-sm">
                    <i class="fas fa-plus mr-2"></i>Catat Trade
                </a>
            </div>
        </div>
    </div>

    {{-- Enhanced Analytics Dashboard --}}
    @include('dashboard.analytics')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- P&L Chart --}}
        <div class="lg:col-span-2 tech-card rounded-2xl p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5">
            <h2 class="font-bold text-base sm:text-lg min-w-0">
                <i class="fas fa-chart-area text-cyan-400 mr-2"></i><span class="leading-snug">P&L Harian (14 Hari Terakhir)</span>
            </h2>
        </div>
            @if($chartData->isEmpty())
                <div class="flex flex-col items-center justify-center h-40 text-gray-600">
                    <i class="fas fa-chart-bar text-4xl mb-3"></i>
                    <p class="text-sm">Belum ada data chart. Mulai catat trade Anda!</p>
                </div>
            @else
                <canvas id="pnlChart" height="120"></canvas>
            @endif
        </div>

        {{-- Active Trading Plan --}}
        <div class="tech-card rounded-2xl p-6">
            <h2 class="font-bold text-lg mb-4">
                <i class="fas fa-calculator text-green-400 mr-2"></i>Trading Plan Aktif
            </h2>
            @if($plan)
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-800">
                        <span class="text-gray-400 text-sm">Modal</span>
                        <span class="font-semibold text-sm">
                            {{ $plan->currency === 'IDR' ? 'Rp ' . number_format($plan->capital, 0, ',', '.') : '$' . number_format($plan->capital, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-800">
                        <span class="text-gray-400 text-sm">Pair</span>
                        <span class="font-semibold text-cyan-400 text-sm">{{ $plan->currency_pair }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-800">
                        <span class="text-gray-400 text-sm">Tipe Trader</span>
                        <span class="badge badge-active capitalize">{{ $plan->trader_type }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-800">
                        <span class="text-gray-400 text-sm">SL / TP</span>
                        <span class="font-semibold text-sm">{{ $plan->stop_loss_pips }} / {{ $plan->take_profit_pips }} pips</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-400 text-sm">R:R Ratio</span>
                        <span class="font-semibold text-green-400 text-sm">1:{{ $plan->rr_ratio }}</span>
                    </div>
                </div>
                <a href="{{ route('trading-plan.index') }}" class="btn-secondary w-full text-center mt-4 block text-sm">
                    <i class="fas fa-edit mr-1"></i> Edit Trading Plan
                </a>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-calculator text-4xl text-gray-700 mb-3 block"></i>
                    <p class="text-gray-500 text-sm mb-4">Belum ada trading plan aktif</p>
                    <a href="{{ route('trading-plan.index') }}" class="btn-primary text-sm">
                        <i class="fas fa-plus mr-1"></i> Buat Trading Plan
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Journal Entries --}}
    <div class="tech-card rounded-2xl p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5">
            <h2 class="font-bold text-base sm:text-lg">
                <i class="fas fa-book text-purple-400 mr-2"></i>Jurnal Terbaru
            </h2>
            <a href="{{ route('journal.create') }}" class="btn-primary text-sm w-full sm:w-auto text-center justify-center">
                <i class="fas fa-plus mr-1"></i> Tambah Entry
            </a>
        </div>

        @if($recentEntries->isEmpty())
            <div class="text-center py-10 text-gray-500">
                <i class="fas fa-book-open text-5xl mb-4 block text-gray-700"></i>
                <p class="mb-2">Belum ada entry jurnal.</p>
                <p class="text-sm text-gray-600">Mulai catat setiap trade untuk analisis performa Anda!</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pair</th>
                            <th>Tipe</th>
                            <th>Hasil</th>
                            <th>P&L</th>
                            <th>Market</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentEntries as $entry)
                        <tr class="cursor-pointer hover:bg-gray-800/30 transition-colors" onclick="window.location='{{ route('journal.show', $entry) }}'">
                            <td class="text-gray-400 text-xs">{{ $entry->entry_date->format('d/m/Y') }}</td>
                            <td class="font-medium">{{ $entry->currency_pair ?? '—' }}</td>
                            <td>
                                <span class="badge text-xs
                                    {{ $entry->trade_type === 'buy'
                                        ? 'bg-green-500/20 text-green-400'
                                        : ($entry->trade_type === 'sell'
                                            ? 'bg-red-500/20 text-red-400'
                                            : 'bg-blue-500/20 text-blue-400') }}">
                                    {{ strtoupper($entry->trade_type) }}
                                </span>
                            </td>
                            <td>
                                @if($entry->result)
                                    <span class="badge text-xs
                                        {{ $entry->result === 'win'
                                            ? 'badge-completed'
                                            : ($entry->result === 'loss' ? 'badge-failed' : 'badge-pending') }}">
                                        {{ strtoupper($entry->result) }}
                                    </span>
                                @else
                                    <span class="text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="font-semibold {{ $entry->profit_loss >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $entry->profit_loss >= 0 ? '+' : '' }}{{ number_format($entry->profit_loss, 0, ',', '.') }}
                            </td>
                            <td class="text-gray-500 text-xs capitalize">{{ $entry->market_condition ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('journal.index') }}" class="text-cyan-400 hover:text-cyan-300 text-sm">
                    Lihat semua jurnal <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('pnlChart');
    if (!ctx) return;

    const chartData = @json($chartData);
    if (!chartData.length) return;

    const chartTheme = document.documentElement.getAttribute('data-theme');
    const isLightChart = chartTheme === 'light';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(d => {
                const date = new Date(d.entry_date);
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            }),
            datasets: [{
                label: 'P&L Harian',
                data: chartData.map(d => d.daily_pnl),
                backgroundColor: chartData.map(d => parseFloat(d.daily_pnl) >= 0
                    ? 'rgba(0,255,136,0.25)'
                    : 'rgba(255,59,59,0.25)'),
                borderColor: chartData.map(d => parseFloat(d.daily_pnl) >= 0
                    ? '#00ff88'
                    : '#ff3b3b'),
                borderWidth: 1.5,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + Number(ctx.raw).toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: {
                    grid: { color: isLightChart ? '#bfdbfe' : '#1f2937' },
                    ticks: { color: isLightChart ? '#64748b' : '#6b7280', font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: isLightChart ? '#64748b' : '#6b7280', font: { size: 10 }, maxRotation: 45 }
                }
            }
        }
    });
});
</script>
@endpush
