@extends('layouts.app')
@section('title', 'Trading Journal')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <h1 class="text-xl sm:text-2xl font-bold gradient-text">📓 Trading Journal</h1>
        <p class="text-gray-400 mt-1 text-sm">Catat, analisis, dan pelajari setiap trade Anda</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('journal.export.excel') }}"
           class="btn-secondary text-sm px-3 py-2.5" title="Export Excel 30 Hari">
            <i class="fas fa-file-excel mr-1.5 text-green-400"></i>
            <span class="hidden sm:inline">Excel</span>
        </a>
        <a href="{{ route('journal.export.pdf') }}"
           class="btn-secondary text-sm px-3 py-2.5" title="Export PDF 30 Hari">
            <i class="fas fa-file-pdf mr-1.5 text-red-400"></i>
            <span class="hidden sm:inline">PDF</span>
        </a>
        <a href="{{ route('journal.create') }}" class="btn-primary w-full sm:w-auto text-center justify-center">
            <i class="fas fa-plus mr-2"></i>Tambah Entry
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
    @php
        $totalWr = ($stats->total ?? 0) > 0
            ? round(($stats->wins / $stats->total) * 100, 1) : 0;
    @endphp
    <div class="stat-card text-center">
        <div class="text-gray-400 text-xs">Total Entry</div>
        <div class="text-2xl font-bold">{{ $stats->total ?? 0 }}</div>
    </div>
    <div class="stat-card text-center">
        <div class="text-gray-400 text-xs">Win Rate</div>
        <div class="text-2xl font-bold {{ $totalWr >= 50 ? 'text-green-400' : 'text-red-400' }}">{{ $totalWr }}%</div>
        <div class="text-xs text-gray-600">{{ $stats->wins ?? 0 }}W / {{ $stats->losses ?? 0 }}L</div>
    </div>
    <div class="stat-card text-center">
        <div class="text-gray-400 text-xs">Total P&L</div>
        <div class="text-2xl font-bold {{ ($stats->total_pnl ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' }}">
            {{ ($stats->total_pnl ?? 0) >= 0 ? '+' : '' }}{{ number_format($stats->total_pnl ?? 0, 0, ',', '.') }}
        </div>
    </div>
    <div class="stat-card text-center">
        <div class="text-gray-400 text-xs">Avg P&L / Trade</div>
        <div class="text-2xl font-bold {{ ($stats->avg_pnl ?? 0) >= 0 ? 'text-cyan-400' : 'text-red-400' }}">
            {{ number_format($stats->avg_pnl ?? 0, 0, ',', '.') }}
        </div>
    </div>
</div>

{{-- Table --}}
<div class="tech-card rounded-2xl p-4 sm:p-6">
    @if($entries->isEmpty())
        <div class="text-center py-16">
            <div class="w-20 h-20 rounded-2xl bg-gray-800 flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-book-open text-3xl text-gray-600"></i>
            </div>
            <h3 class="font-bold text-xl text-gray-500 mb-2">Belum Ada Entry Jurnal</h3>
            <p class="text-gray-600 text-sm mb-6 max-w-xs mx-auto">
                Mulai catat setiap trade untuk menganalisis dan meningkatkan performa trading Anda.
            </p>
            <a href="{{ route('journal.create') }}" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>Tambah Entry Pertama
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center w-24">Aksi</th>
                        <th>Tanggal</th>
                        <th>Pair</th>
                        <th>Tipe</th>
                        <th>Hasil</th>
                        <th>P&L</th>
                        <th class="hidden sm:table-cell">Market</th>
                        <th class="hidden sm:table-cell">Mood</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                    <tr>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('journal.show', $entry) }}"
                                   class="text-purple-400 hover:text-purple-300 text-sm p-2 transition-colors"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('journal.edit', $entry) }}"
                                   class="text-cyan-400 hover:text-cyan-300 text-sm p-2 transition-colors"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('journal.destroy', $entry) }}" method="POST"
                                      onsubmit="return confirm('Hapus entry ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-red-400 hover:text-red-300 text-sm p-2 transition-colors"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td class="text-gray-400 text-xs font-mono">{{ $entry->entry_date->format('d/m/Y') }}</td>
                        <td class="font-semibold">{{ $entry->currency_pair ?? '—' }}</td>
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
                                    {{ $entry->result === 'win' ? 'badge-completed' :
                                       ($entry->result === 'loss' ? 'badge-failed' : 'badge-pending') }}">
                                    {{ strtoupper($entry->result) }}
                                </span>
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="font-bold {{ $entry->profit_loss >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $entry->profit_loss >= 0 ? '+' : '' }}{{ number_format($entry->profit_loss, 0, ',', '.') }}
                        </td>
                        <td class="hidden sm:table-cell text-gray-500 text-xs capitalize">{{ $entry->market_condition ?? '—' }}</td>
                        <td class="hidden sm:table-cell">
                            @if($entry->emotion_score)
                                <span class="text-yellow-400 text-xs">{{ str_repeat('★', $entry->emotion_score) }}</span>
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-5">
            {{ $entries->links() }}
        </div>
    @endif
</div>
@endsection
