<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\TradingPlan;
use App\Services\TradingCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradingCalculatorController extends Controller
{
    public function __construct(private TradingCalculatorService $calculator) {}

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'currency'         => 'required|in:IDR,USD',
            'capital'          => 'required|numeric|min:1',
            'trader_type'      => 'required|in:conservative,moderate,aggressive',
            'currency_pair'    => 'required|string',
            'stop_loss_pips'   => 'required|integer|min:1',
            'take_profit_pips' => 'required|integer|min:1',
        ]);

        $plan = new TradingPlan($validated);
        $plan->id = 0; // Signals calculator to skip caching
        $plan->updated_at = \Carbon\Carbon::now();
        $data = $this->calculator->generate($plan);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function chartData(Challenge $challenge)
    {
        abort_if($challenge->user_id !== Auth::id(), 403);

        $days = $challenge->days->map(fn ($d) => [
            'day'    => $d->day_number,
            'result' => (float) $d->actual_result,
            'target' => (float) $d->target_profit,
            'status' => $d->status,
        ]);

        return response()->json(['data' => $days]);
    }
}
