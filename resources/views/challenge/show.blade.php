@extends('layouts.app')
@section('title', 'Detail Challenge')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <h1 class="text-xl sm:text-2xl font-bold gradient-text">🏆 Challenge 30 Hari</h1>
        <p class="text-gray-400 mt-1 text-sm">Mulai: {{ $challenge->started_at?->format('d M Y') }}</p>
    </div>
    <a href="{{ route('challenge.index') }}" class="btn-secondary text-sm w-full sm:w-auto text-center justify-center shrink-0">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

{{-- Progress Overview --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="stat-card text-center">
        <div class="text-gray-400 text-xs mb-1">Modal Awal</div>
        <div class="font-bold text-white text-sm">{{ number_format($challenge->initial_capital, 0, ',', '.') }}</div>
    </div>
    <div class="stat-card text-center">
        <div class="text-gray-400 text-xs mb-1">Modal Saat Ini</div>
        <div class="font-bold text-cyan-400 text-sm">{{ number_format($challenge->current_capital, 0, ',', '.') }}</div>
        <div class="text-gray-500 text-xs">+{{ $challenge->growth_percent }}%</div>
    </div>
    <div class="stat-card text-center">
        <div class="text-gray-400 text-xs mb-1">Progress</div>
        <div class="font-bold text-yellow-400 text-2xl">{{ $challenge->progress_percent }}%</div>
        <div class="progress-bar mt-2">
            <div class="progress-fill" style="width: {{ $challenge->progress_percent }}%"></div>
        </div>
    </div>
    <div class="stat-card text-center">
        <div class="text-gray-400 text-xs mb-1">Total Profit</div>
        <div class="font-bold {{ $challenge->total_profit >= 0 ? 'text-green-400' : 'text-red-400' }} text-sm">
            {{ $challenge->total_profit >= 0 ? '+' : '' }}{{ number_format($challenge->total_profit, 0, ',', '.') }}
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Grid 30 Hari --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="tech-card rounded-2xl p-6">
            <h2 class="font-bold text-lg mb-4">
                <i class="fas fa-calendar-alt text-cyan-400 mr-2"></i>Grid 30 Hari
            </h2>
            <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-1.5 sm:gap-2 mb-4">
                @foreach($days as $day)
                @php
                    $isCurrent = $day->day_number === $challenge->current_day && $day->status === 'pending';
                    $badgeClass = $isCurrent ? 'current' : $day->status;
                @endphp
                <div class="day-badge {{ $badgeClass }} relative group cursor-default select-none"
                     title="Hari {{ $day->day_number }}: {{ ucfirst($day->status) }}{{ $day->actual_result != 0 ? ' | ' . number_format($day->actual_result, 0, ',', '.') : '' }}">
                    {{ $day->day_number }}
                    @if($day->status === 'completed')
                        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-green-400 rounded-full border border-gray-900"></span>
                    @elseif($day->status === 'failed')
                        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-400 rounded-full border border-gray-900"></span>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-4 text-xs text-gray-500 pt-3 border-t border-gray-800">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-green-500/20 border border-green-500/30 inline-block"></span>Completed
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-red-500/20 border border-red-500/30 inline-block"></span>Failed
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-cyan-500/20 border border-cyan-500 inline-block"></span>Hari Ini
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-gray-800 inline-block"></span>Pending
                </span>
            </div>
        </div>

        {{-- Progress Chart --}}
        <div class="tech-card rounded-2xl p-6">
            <h2 class="font-bold text-lg mb-4">
                <i class="fas fa-chart-bar text-purple-400 mr-2"></i>Progress Chart
            </h2>
            <canvas id="challengeChart" height="90"></canvas>
        </div>
    </div>

    {{-- Right: Input Hari Ini + Daftar Hari --}}
    <div class="space-y-4">

        {{-- Input Form Hari Ini --}}
        @php $today = $days->firstWhere('day_number', $challenge->current_day); @endphp
        @if($today && $today->status === 'pending' && $challenge->status === 'active')
        <div class="tech-card rounded-2xl p-5 border border-cyan-500/30 glow-cyan">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center">
                    <i class="fas fa-pencil-alt text-cyan-400 text-xs"></i>
                </div>
                <div>
                    <div class="font-bold text-sm">Catat Hari {{ $challenge->current_day }}</div>
                    <div class="text-gray-500 text-xs">Target: <span class="text-green-400 font-semibold">+{{ number_format($today->target_profit, 0, ',', '.') }}</span></div>
                </div>
            </div>

            <div class="bg-gray-800/50 rounded-lg p-3 mb-4 text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-gray-500">Modal Hari Ini</span>
                    <span class="font-semibold">{{ number_format($today->start_capital, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Target Profit</span>
                    <span class="text-green-400 font-semibold">+{{ number_format($today->target_profit, 0, ',', '.') }}</span>
                </div>
            </div>

            <form action="{{ route('challenge.updateDay', [$challenge, $challenge->current_day]) }}" method="POST">
                @csrf @method('PATCH')
                <div class="mb-3">
                    <label class="form-label text-xs">Actual Result (Profit/Loss)</label>
                    <input type="number" name="actual_result" step="0.01" class="form-input text-center font-bold text-lg"
                           placeholder="Masukkan hasil actual" required>
                    <p class="text-gray-600 text-xs mt-1">Gunakan angka negatif jika loss (contoh: -50000)</p>
                </div>
                <div class="mb-4">
                    <label class="form-label text-xs">Catatan (opsional)</label>
                    <input type="text" name="notes" class="form-input text-sm"
                           placeholder="Kondisi market, strategi...">
                </div>
                <button type="submit" class="btn-primary w-full text-sm">
                    <i class="fas fa-save mr-1"></i>Simpan Hari {{ $challenge->current_day }}
                </button>
            </form>
        </div>
        @elseif($challenge->status === 'completed')
        <div class="tech-card rounded-2xl p-6 text-center border border-green-500/30">
            <div class="text-5xl mb-3">🎉</div>
            <div class="font-bold text-green-400 text-xl mb-1">Challenge Selesai!</div>
            <div class="text-gray-400 text-sm">Selamat! Anda telah menyelesaikan Challenge 30 Hari.</div>
        </div>
        @endif

        {{-- Daftar Hari (scrollable) --}}
        <div class="tech-card rounded-2xl p-5">
            <h3 class="font-bold text-sm mb-3 text-gray-300">
                <i class="fas fa-list text-gray-500 mr-1"></i>Detail Per Hari
            </h3>
            <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                @foreach($days as $d)
                <div class="flex items-center gap-3 py-2 px-3 rounded-lg
                    {{ $d->day_number === $challenge->current_day && $d->status === 'pending'
                        ? 'bg-cyan-500/10 border border-cyan-500/20'
                        : 'bg-gray-800/40 hover:bg-gray-800/60' }} transition-colors">
                    <div class="w-7 h-7 rounded-md flex items-center justify-center text-xs font-bold shrink-0
                        {{ $d->status === 'completed' ? 'bg-green-500/20 text-green-400' :
                           ($d->status === 'failed' ? 'bg-red-500/20 text-red-400' :
                           ($d->day_number === $challenge->current_day ? 'bg-cyan-500/20 text-cyan-400' :
                           'bg-gray-700 text-gray-500')) }}">
                        {{ $d->day_number }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs text-gray-400">
                            Target: <span class="text-green-400">+{{ number_format($d->target_profit, 0, ',', '.') }}</span>
                        </div>
                        @if($d->status !== 'pending')
                        <div class="text-xs font-semibold {{ $d->actual_result >= 0 ? 'text-green-400' : 'text-red-400' }} truncate">
                            Actual: {{ $d->actual_result >= 0 ? '+' : '' }}{{ number_format($d->actual_result, 0, ',', '.') }}
                        </div>
                        @endif
                    </div>
                    <div class="shrink-0">
                        @if($d->status === 'completed')
                            <i class="fas fa-check-circle text-green-400 text-xs"></i>
                        @elseif($d->status === 'failed')
                            <i class="fas fa-times-circle text-red-400 text-xs"></i>
                        @elseif($d->day_number === $challenge->current_day)
                            <i class="fas fa-clock text-cyan-400 text-xs animate-pulse"></i>
                        @else
                            <i class="fas fa-circle text-gray-700 text-xs"></i>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('challengeChart');
    if (!ctx) return;

    const chartData = @json($chartData);
    const labels    = chartData.map(d => 'H' + d.day);
    const targets   = chartData.map(d => d.target);
    const actuals   = chartData.map(d => d.result !== 0 ? d.result : null);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Target',
                    data: targets,
                    backgroundColor: 'rgba(0,212,255,0.08)',
                    borderColor: 'rgba(0,212,255,0.3)',
                    borderWidth: 1,
                    borderRadius: 2,
                    order: 2,
                },
                {
                    label: 'Actual',
                    data: actuals,
                    backgroundColor: chartData.map(d =>
                        d.result > 0  ? 'rgba(0,255,136,0.35)' :
                        d.result < 0  ? 'rgba(255,59,59,0.35)'  : 'transparent'),
                    borderColor: chartData.map(d =>
                        d.result > 0  ? '#00ff88' :
                        d.result < 0  ? '#ff3b3b' : 'transparent'),
                    borderWidth: 1.5,
                    borderRadius: 3,
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#9ca3af', font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + Number(ctx.raw ?? 0).toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: { grid: { color: '#1f2937' }, ticks: { color: '#6b7280', font: { size: 10 } } },
                x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 9 }, maxRotation: 0 } }
            }
        }
    });
});
</script>
@endpush
