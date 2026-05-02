@extends('layouts.app')
@section('title', 'Detail Jurnal')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold gradient-text">📓 Detail Entry Jurnal</h1>
            <p class="text-gray-400 mt-1 text-sm">{{ $journal->entry_date->format('d F Y') }}</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto shrink-0">
            <a href="{{ route('journal.edit', $journal) }}" class="btn-primary text-sm flex-1 sm:flex-none text-center justify-center">
                <i class="fas fa-edit mr-1"></i>Edit
            </a>
            <a href="{{ route('journal.index') }}" class="btn-secondary text-sm flex-1 sm:flex-none text-center justify-center">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
        </div>
    </div>

    <div class="tech-card rounded-2xl p-4 sm:p-6 space-y-5">

        {{-- Header Info --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="stat-card text-center">
                <div class="text-gray-400 text-xs mb-1">Pair</div>
                <div class="font-bold text-cyan-400">{{ $journal->currency_pair ?? '—' }}</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-gray-400 text-xs mb-1">Tipe</div>
                <span class="badge text-xs
                    {{ $journal->trade_type === 'buy'
                        ? 'bg-green-500/20 text-green-400'
                        : ($journal->trade_type === 'sell'
                            ? 'bg-red-500/20 text-red-400'
                            : 'bg-blue-500/20 text-blue-400') }}">
                    {{ strtoupper($journal->trade_type) }}
                </span>
            </div>
            <div class="stat-card text-center">
                <div class="text-gray-400 text-xs mb-1">Hasil</div>
                @if($journal->result)
                    <span class="badge text-xs
                        {{ $journal->result === 'win' ? 'badge-completed' :
                           ($journal->result === 'loss' ? 'badge-failed' : 'badge-pending') }}">
                        {{ strtoupper($journal->result) }}
                    </span>
                @else
                    <span class="text-gray-600">—</span>
                @endif
            </div>
            <div class="stat-card text-center">
                <div class="text-gray-400 text-xs mb-1">P&L</div>
                <div class="font-bold {{ $journal->profit_loss >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $journal->profit_loss >= 0 ? '+' : '' }}{{ number_format($journal->profit_loss, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800"></div>

        {{-- Market Condition & Emotion --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-gray-400 text-xs mb-1">Kondisi Market</div>
                <div class="font-medium capitalize">{{ $journal->market_condition ?? '—' }}</div>
            </div>
            <div>
                <div class="text-gray-400 text-xs mb-1">Mood Trader</div>
                @if($journal->emotion_score)
                    <div class="text-yellow-400">
                        {{ str_repeat('★', $journal->emotion_score) }}{{ str_repeat('☆', 5 - $journal->emotion_score) }}
                        <span class="text-gray-500 text-xs ml-1">({{ $journal->emotion_score }}/5)</span>
                    </div>
                @else
                    <div class="text-gray-600">—</div>
                @endif
            </div>
        </div>

        {{-- Analysis --}}
        @if($journal->analysis)
        <div>
            <div class="text-gray-400 text-xs mb-2 flex items-center">
                <i class="fas fa-chart-line mr-1"></i>Analisis Trade
            </div>
            <div class="bg-gray-800/40 rounded-xl p-4 text-sm text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $journal->analysis }}</div>
        </div>
        @endif

        {{-- Lesson Learned --}}
        @if($journal->lesson_learned)
        <div>
            <div class="text-gray-400 text-xs mb-2 flex items-center">
                <i class="fas fa-graduation-cap mr-1"></i>Lesson Learned
            </div>
            <div class="bg-cyan-500/5 border border-cyan-500/20 rounded-xl p-4 text-sm text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $journal->lesson_learned }}</div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="border-t border-gray-800 pt-4 flex items-center justify-between text-xs text-gray-600">
            <span>Dibuat: {{ $journal->created_at->format('d M Y H:i') }}</span>
            @if($journal->updated_at != $journal->created_at)
                <span>Diperbarui: {{ $journal->updated_at->diffForHumans() }}</span>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 pt-2">
            <a href="{{ route('journal.edit', $journal) }}" class="btn-primary flex-1 text-center">
                <i class="fas fa-edit mr-2"></i>Edit Entry
            </a>
            <form action="{{ route('journal.destroy', $journal) }}" method="POST"
                  onsubmit="return confirm('Hapus entry ini secara permanen?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger px-5">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
