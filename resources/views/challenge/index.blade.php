@extends('layouts.app')
@section('title', 'Challenge 30 Hari')

@section('content')
<div class="mb-6">
    <h1 class="text-xl sm:text-2xl font-bold gradient-text">🏆 Challenge 30 Hari</h1>
    <p class="text-gray-400 mt-1 text-sm">Double your capital dalam 30 hari dengan disiplin trading</p>
</div>

@if($challenge)
    {{-- Active Challenge --}}
    <div class="tech-card rounded-2xl p-4 sm:p-6 border border-cyan-500/20 mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center mb-5">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center shrink-0">
                    <i class="fas fa-fire text-cyan-400"></i>
                </div>
                <div class="min-w-0">
                    <div class="font-bold text-base sm:text-lg">Challenge Sedang Berjalan</div>
                    <div class="text-gray-400 text-sm">Mulai: {{ $challenge->started_at?->format('d M Y') }}</div>
                </div>
            </div>
            <span class="badge badge-active self-start sm:ml-auto sm:self-center">AKTIF</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            <div class="stat-card text-center">
                <div class="text-gray-400 text-xs">Modal Awal</div>
                <div class="font-bold text-white">{{ number_format($challenge->initial_capital, 0, ',', '.') }}</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-gray-400 text-xs">Target</div>
                <div class="font-bold text-yellow-400">{{ number_format($challenge->target_capital, 0, ',', '.') }}</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-gray-400 text-xs">Hari</div>
                <div class="font-bold text-cyan-400 text-2xl">{{ $challenge->current_day }}<span class="text-sm text-gray-500">/30</span></div>
            </div>
            <div class="stat-card text-center">
                <div class="text-gray-400 text-xs">Total Profit</div>
                <div class="font-bold {{ $challenge->total_profit >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $challenge->total_profit >= 0 ? '+' : '' }}{{ number_format($challenge->total_profit, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="mb-5">
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-400">Progress Keseluruhan</span>
                <span class="text-cyan-400 font-bold">{{ $challenge->progress_percent }}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $challenge->progress_percent }}%"></div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('challenge.show', $challenge) }}" class="btn-primary">
                <i class="fas fa-eye mr-2"></i>Lihat & Update Challenge
            </a>
            <form action="{{ route('challenge.reset', $challenge) }}" method="POST"
                  onsubmit="return confirm('Reset challenge? Semua progress akan hilang!')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">
                    <i class="fas fa-redo mr-2"></i>Reset Challenge
                </button>
            </form>
        </div>
    </div>

@else
    {{-- Start New Challenge --}}
    <div class="max-w-lg mx-auto mb-6">
        <div class="tech-card rounded-2xl p-8 text-center border border-yellow-500/20">
            <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center mx-auto mb-6 shadow-lg shadow-yellow-500/20">
                <i class="fas fa-trophy text-4xl text-white"></i>
            </div>
            <h2 class="text-2xl font-bold mb-2">Mulai Challenge Baru!</h2>
            <p class="text-gray-400 text-sm mb-6 max-w-xs mx-auto">
                Tantang diri Anda untuk double capital dalam 30 hari dengan disiplin dan konsistensi trading.
            </p>

            <form action="{{ route('challenge.start') }}" method="POST">
                @csrf
                <div class="mb-5 text-left">
                    <label class="form-label text-center block">Modal Awal (opsional)</label>
                    <input type="number" name="initial_capital" class="form-input text-center text-lg font-semibold"
                           placeholder="{{ number_format(Auth::user()->default_capital, 0, ',', '.') }}"
                           min="100000">
                    <p class="text-gray-600 text-xs mt-2 text-center">
                        Kosongkan untuk pakai modal default dari Settings
                    </p>
                </div>

                <div class="bg-gray-800/50 rounded-xl p-4 mb-5 text-left space-y-2">
                    <div class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-2">Aturan Challenge</div>
                    @foreach(['Trading plan berdasarkan tipe trader Anda', 'Catat hasil setiap hari tanpa skip', 'Target: double capital dalam 30 hari', 'Disiplin = kunci sukses challenge'] as $rule)
                    <div class="flex items-center gap-2 text-sm text-gray-300">
                        <i class="fas fa-check-circle text-green-400 text-xs"></i>
                        {{ $rule }}
                    </div>
                    @endforeach
                </div>

                <button type="submit" class="btn-primary w-full text-lg py-4">
                    <i class="fas fa-play mr-2"></i>Mulai Challenge 30 Hari!
                </button>
            </form>
        </div>
    </div>
@endif

{{-- History --}}
@if($history->count() > 0)
<div class="tech-card rounded-2xl p-6">
    <h2 class="font-bold text-lg mb-4">
        <i class="fas fa-history text-gray-400 mr-2"></i>Riwayat Challenge
    </h2>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mulai</th>
                    <th>Modal Awal</th>
                    <th>Target</th>
                    <th>Total Profit</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($history as $h)
                <tr>
                    <td class="text-gray-400 text-xs">{{ $h->started_at?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ number_format($h->initial_capital, 0, ',', '.') }}</td>
                    <td class="text-yellow-400">{{ number_format($h->target_capital, 0, ',', '.') }}</td>
                    <td class="{{ $h->total_profit >= 0 ? 'text-green-400' : 'text-red-400' }} font-semibold">
                        {{ $h->total_profit >= 0 ? '+' : '' }}{{ number_format($h->total_profit, 0, ',', '.') }}
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="progress-bar w-20">
                                <div class="progress-fill" style="width: {{ $h->progress_percent }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400">{{ $h->progress_percent }}%</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $h->status === 'completed' ? 'badge-completed' : 'badge-failed' }}">
                            {{ strtoupper($h->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
