<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Exports\JournalExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

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

    /**
     * Export journal entries to Excel (last 30 days)
     */
    public function exportExcel()
    {
        $startDate = now()->subDays(30)->startOfDay();

        $entries = JournalEntry::where('user_id', Auth::id())
            ->where('entry_date', '>=', $startDate)
            ->orderBy('entry_date', 'desc')
            ->get();

        $stats = JournalEntry::where('user_id', Auth::id())
            ->where('entry_date', '>=', $startDate)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN result = "win" THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN result = "loss" THEN 1 ELSE 0 END) as losses,
                SUM(profit_loss) as total_pnl,
                AVG(profit_loss) as avg_pnl
            ')
            ->first();

        $periodLabel = $startDate->format('d M Y') . ' — ' . now()->format('d M Y');

        return Excel::download(
            new JournalExport($entries, $stats, $periodLabel),
            'trading-journal-30days-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export journal entries to PDF (last 30 days)
     */
    public function exportPdf()
    {
        $startDate = now()->subDays(30)->startOfDay();

        $entries = JournalEntry::where('user_id', Auth::id())
            ->where('entry_date', '>=', $startDate)
            ->orderBy('entry_date', 'desc')
            ->get();

        $stats = JournalEntry::where('user_id', Auth::id())
            ->where('entry_date', '>=', $startDate)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN result = "win" THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN result = "loss" THEN 1 ELSE 0 END) as losses,
                SUM(profit_loss) as total_pnl,
                AVG(profit_loss) as avg_pnl
            ')
            ->first();

        $total = $stats->total ?? 0;
        $wins = $stats->wins ?? 0;
        $winRate = $total > 0 ? round(($wins / $total) * 100, 1) : 0;

        $entriesWithAnalysis = JournalEntry::where('user_id', Auth::id())
            ->where('entry_date', '>=', $startDate)
            ->where(function ($q) {
                $q->whereNotNull('analysis')
                  ->where('analysis', '!=', '');
            })
            ->orderBy('entry_date', 'desc')
            ->get();

        $periodLabel = $startDate->format('d M Y') . ' — ' . now()->format('d M Y');

        $pdf = Pdf::loadView('exports.journal-export-pdf', compact(
            'entries', 'stats', 'winRate', 'entriesWithAnalysis', 'periodLabel'
        ))
        ->setPaper('a4', 'portrait')
        ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('trading-journal-30days-' . now()->format('Y-m-d') . '.pdf');
    }
}
