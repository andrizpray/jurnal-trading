<?php

namespace App\Http\Controllers;

use App\Models\TradingPlan;
use App\Services\TradingCalculatorService;
use App\Services\NotificationService;
use App\Exports\TradingPlanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TradingPlanController extends Controller
{
    public function __construct(
        private TradingCalculatorService $calculator,
        private NotificationService $notificationService
    ) {}

    public function index()
    {
        $plan     = TradingPlan::where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();
        $planData = $plan ? $this->calculator->generate($plan) : null;
        $pairs    = $this->calculator->getCurrencyPairs();
        $configs  = $this->calculator->getTraderConfigs();

        return view('trading-plan.index', compact('plan', 'planData', 'pairs', 'configs'));
    }

    public function store(Request $request)
    {
        $minCapital = $this->calculator->getMinCapital($request->input('currency', 'IDR'));

        $validated = $request->validate([
            'currency'         => 'required|in:IDR,USD,USC',
            'capital'          => "required|numeric|min:{$minCapital}",
            'trader_type'      => 'required|in:conservative,moderate,aggressive',
            'currency_pair'    => 'required|string',
            'stop_loss_pips'   => 'required|integer|min:5|max:200',
            'take_profit_pips' => 'required|integer|min:5|max:500',
            'risk_per_trade'   => 'nullable|numeric|min:0.5|max:10',
        ]);

        // Nonaktifkan plan lama dan clear cache
        $oldPlans = TradingPlan::where('user_id', Auth::id())->get();
        foreach ($oldPlans as $oldPlan) {
            $this->calculator->clearCache($oldPlan);
        }
        
        TradingPlan::where('user_id', Auth::id())->update(['is_active' => false]);

        // Buat plan baru
        $newPlan = TradingPlan::create([
            ...$validated,
            'user_id'        => Auth::id(),
            'is_active'      => true,
            'risk_per_trade' => $validated['risk_per_trade'] ?? 2.0,
        ]);

        // Clear cache for the new plan (in case it gets queried immediately)
        $this->calculator->clearCache($newPlan);

        // Send notification
        $this->notificationService->sendPlanUpdateNotifications($newPlan);

        return redirect()->route('trading-plan.index')
            ->with('success', '✅ Trading plan berhasil disimpan!');
    }

    public function history()
    {
        $plans = TradingPlan::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('trading-plan.history', compact('plans'));
    }

    /**
     * Export trading plan as Excel file
     */
    public function exportExcel(TradingPlan $plan)
    {
        abort_if($plan->user_id !== Auth::id(), 403);
        
        $fileName = 'trading-plan-' . $plan->created_at->format('Y-m-d') . '.xlsx';
        
        return Excel::download(new TradingPlanExport($plan), $fileName);
    }

    /**
     * Export trading plan as PDF file
     */
    public function exportPdf(TradingPlan $plan)
    {
        abort_if($plan->user_id !== Auth::id(), 403);
        
        $planData = $this->calculator->generate($plan);
        $user = Auth::user();
        
        $pdf = Pdf::loadView('exports.trading-plan-pdf', [
            'plan' => $plan,
            'planData' => $planData,
            'calculator' => $this->calculator,
            'user' => $user,
        ]);
        
        $fileName = 'trading-plan-' . $plan->created_at->format('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }
}
