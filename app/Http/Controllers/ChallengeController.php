<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Services\TradingCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChallengeController extends Controller
{
    public function __construct(private TradingCalculatorService $calculator) {}

    public function index()
    {
        $user      = Auth::user();
        $challenge = $user->activeChallenge();
        $history   = Challenge::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'failed'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('challenge.index', compact('challenge', 'history'));
    }

    public function start(Request $request)
    {
        $user = Auth::user();
        $minCapital = $this->calculator->getMinCapital($user->currency ?? 'IDR');

        $request->validate([
            'initial_capital' => "nullable|numeric|min:{$minCapital}",
        ]);

        // Nonaktifkan challenge lama
        Challenge::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'failed']);

        $capital = $request->initial_capital ?? $user->default_capital ?? 1000000;
        $traderType = $user->trader_type ?? 'moderate';
        $config  = $this->calculator->getTraderConfigs()[$traderType];

        $challenge = Challenge::create([
            'user_id'         => $user->id,
            'initial_capital' => $capital,
            'target_capital'  => $capital * 2,
            'status'          => 'active',
            'started_at'      => now(),
            'current_day'     => 1,
        ]);

        // Generate 30 hari dengan target compound
        $cap = (float) $capital;
        for ($day = 1; $day <= 30; $day++) {
            $target = $cap * ($config['dailyTarget'] / 100);
            $challenge->days()->create([
                'day_number'    => $day,
                'start_capital' => round($cap, 2),
                'target_profit' => round($target, 2),
                'status'        => 'pending',
            ]);
            $cap += $target;
        }

        return redirect()->route('challenge.show', $challenge)
            ->with('success', '🚀 Challenge 30 Hari dimulai! Semangat Trader!');
    }

    public function show(Challenge $challenge)
    {
        abort_if($challenge->user_id !== Auth::id(), 403);

        $days      = $challenge->days;
        $chartData = $days->map(fn ($d) => [
            'day'    => $d->day_number,
            'result' => (float) $d->actual_result,
            'target' => (float) $d->target_profit,
            'status' => $d->status,
        ]);

        return view('challenge.show', compact('challenge', 'days', 'chartData'));
    }

    public function updateDay(Request $request, Challenge $challenge, int $day)
    {
        abort_if($challenge->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'actual_result' => 'required|numeric',
            'notes'         => 'nullable|string|max:500',
        ]);

        $challengeDay = $challenge->days()->where('day_number', $day)->firstOrFail();

        $challengeDay->update([
            ...$validated,
            'status'       => $validated['actual_result'] > 0 ? 'completed' : ($validated['actual_result'] == 0 ? 'completed' : 'failed'),
            'completed_at' => now()->toDateString(),
        ]);

        // Recalculate progress
        $completed = $challenge->days()->whereIn('status', ['completed', 'failed'])->count();
        $profit    = $challenge->days()->sum('actual_result');

        $challenge->update([
            'current_day'      => min($day + 1, 30),
            'progress_percent' => round(($completed / 30) * 100),
            'total_profit'     => $profit,
            'status'           => $completed >= 30 ? 'completed' : 'active',
        ]);

        return back()->with('success', "✅ Hari {$day} berhasil dicatat!");
    }

    public function reset(Challenge $challenge)
    {
        abort_if($challenge->user_id !== Auth::id(), 403);
        $challenge->update(['status' => 'failed']);

        return redirect()->route('challenge.index')
            ->with('success', 'Challenge telah direset.');
    }
}
