<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    public function __construct() {}

    public function index()
    {
        $entries = JournalEntry::where('user_id', Auth::id())
            ->orderBy('entry_date', 'desc')
            ->paginate(15);

        $stats = JournalEntry::where('user_id', Auth::id())
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN result = "win" THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN result = "loss" THEN 1 ELSE 0 END) as losses,
                SUM(profit_loss) as total_pnl,
                AVG(profit_loss) as avg_pnl
            ')
            ->first();

        return view('journal.index', compact('entries', 'stats'));
    }

    public function create()
    {
        return view('journal.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date'       => 'required|date',
            'currency_pair'    => 'nullable|string|max:20',
            'trade_type'       => 'required|in:buy,sell,analysis',
            'profit_loss'      => 'nullable|numeric',
            'result'           => 'nullable|in:win,loss,breakeven',
            'analysis'         => 'nullable|string',
            'lesson_learned'   => 'nullable|string',
            'emotion_score'    => 'nullable|integer|min:1|max:5',
            'market_condition' => 'nullable|in:trending,ranging,volatile',
        ]);

        JournalEntry::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('journal.index')
            ->with('success', '📓 Jurnal berhasil ditambahkan!');
    }

    public function show(JournalEntry $journal)
    {
        abort_if($journal->user_id !== Auth::id(), 403);
        return view('journal.show', compact('journal'));
    }

    public function edit(JournalEntry $journal)
    {
        abort_if($journal->user_id !== Auth::id(), 403);
        return view('journal.edit', compact('journal'));
    }

    public function update(Request $request, JournalEntry $journal)
    {
        abort_if($journal->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'entry_date'       => 'required|date',
            'currency_pair'    => 'nullable|string|max:20',
            'trade_type'       => 'required|in:buy,sell,analysis',
            'profit_loss'      => 'nullable|numeric',
            'result'           => 'nullable|in:win,loss,breakeven',
            'analysis'         => 'nullable|string',
            'lesson_learned'   => 'nullable|string',
            'emotion_score'    => 'nullable|integer|min:1|max:5',
            'market_condition' => 'nullable|in:trending,ranging,volatile',
        ]);

        $journal->update($validated);

        return redirect()->route('journal.index')
            ->with('success', '✏️ Jurnal berhasil diperbarui!');
    }

    public function destroy(JournalEntry $journal)
    {
        abort_if($journal->user_id !== Auth::id(), 403);
        $journal->delete();

        return back()->with('success', '🗑️ Jurnal berhasil dihapus.');
    }
}
