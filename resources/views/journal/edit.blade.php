@extends('layouts.app')
@section('title', 'Edit Jurnal')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold gradient-text">✏️ Edit Entry Jurnal</h1>
            <p class="text-gray-400 mt-1 text-sm">{{ $journal->entry_date->format('d M Y') }} — {{ $journal->currency_pair ?? 'No Pair' }}</p>
        </div>
        <a href="{{ route('journal.index') }}" class="btn-secondary text-sm w-full sm:w-auto text-center justify-center shrink-0">
            <i class="fas fa-arrow-left mr-1"></i>Kembali
        </a>
    </div>

    <div class="tech-card rounded-2xl p-4 sm:p-6">
        <form action="{{ route('journal.update', $journal) }}" method="POST">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Tanggal Trade <span class="text-red-400">*</span></label>
                    <input type="date" name="entry_date"
                           value="{{ old('entry_date', $journal->entry_date->format('Y-m-d')) }}"
                           class="form-input" required>
                    @error('entry_date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Currency Pair</label>
                    <input type="text" name="currency_pair"
                           value="{{ old('currency_pair', $journal->currency_pair) }}"
                           class="form-input" placeholder="EUR/USD">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Tipe Trade <span class="text-red-400">*</span></label>
                    <select name="trade_type" class="form-select" required>
                        <option value="buy"      {{ old('trade_type', $journal->trade_type) === 'buy'      ? 'selected' : '' }}>📈 Buy</option>
                        <option value="sell"     {{ old('trade_type', $journal->trade_type) === 'sell'     ? 'selected' : '' }}>📉 Sell</option>
                        <option value="analysis" {{ old('trade_type', $journal->trade_type) === 'analysis' ? 'selected' : '' }}>🔍 Analysis Only</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Hasil Trade</label>
                    <select name="result" class="form-select">
                        <option value="">— Pilih Hasil —</option>
                        <option value="win"       {{ old('result', $journal->result) === 'win'       ? 'selected' : '' }}>✅ Win</option>
                        <option value="loss"      {{ old('result', $journal->result) === 'loss'      ? 'selected' : '' }}>❌ Loss</option>
                        <option value="breakeven" {{ old('result', $journal->result) === 'breakeven' ? 'selected' : '' }}>➖ Breakeven</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Profit / Loss</label>
                    <input type="number" name="profit_loss"
                           value="{{ old('profit_loss', $journal->profit_loss) }}"
                           step="0.01" class="form-input">
                </div>
                <div>
                    <label class="form-label">Kondisi Market</label>
                    <select name="market_condition" class="form-select">
                        <option value="">— Pilih —</option>
                        <option value="trending" {{ old('market_condition', $journal->market_condition) === 'trending' ? 'selected' : '' }}>📈 Trending</option>
                        <option value="ranging"  {{ old('market_condition', $journal->market_condition) === 'ranging'  ? 'selected' : '' }}>↔️ Ranging</option>
                        <option value="volatile" {{ old('market_condition', $journal->market_condition) === 'volatile' ? 'selected' : '' }}>⚡ Volatile</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Mood Trader</label>
                <div class="flex gap-2">
                    @for($i = 1; $i <= 5; $i++)
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="emotion_score" value="{{ $i }}"
                               {{ old('emotion_score', $journal->emotion_score) == $i ? 'checked' : '' }} class="sr-only peer">
                        <div class="text-center py-3 rounded-lg border border-gray-700
                                    peer-checked:border-yellow-400 peer-checked:bg-yellow-400/10
                                    hover:border-gray-500 transition-all">
                            <div class="text-lg">{{ ['😰','😐','🙂','😊','🤩'][$i-1] }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $i }}</div>
                        </div>
                    </label>
                    @endfor
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Analisis Trade</label>
                <textarea name="analysis" rows="4" class="form-input resize-none"
                    placeholder="Kenapa ambil trade ini?">{{ old('analysis', $journal->analysis) }}</textarea>
            </div>

            <div class="mb-6">
                <label class="form-label">Lesson Learned</label>
                <textarea name="lesson_learned" rows="3" class="form-input resize-none"
                    placeholder="Apa yang bisa diperbaiki?">{{ old('lesson_learned', $journal->lesson_learned) }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1">
                    <i class="fas fa-save mr-2"></i>Perbarui Entry
                </button>
                <a href="{{ route('journal.index') }}" class="btn-secondary px-6">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
