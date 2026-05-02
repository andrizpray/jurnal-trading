<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TradingCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TradingPreviewController extends Controller
{
    public function __construct(
        private TradingCalculatorService $calculator
    ) {}

    /**
     * Calculate trading plan preview without saving
     */
    public function preview(Request $request)
    {
        $minCapital = $this->calculator->getMinCapital($request->input('currency', 'IDR'));

        $validator = Validator::make($request->all(), [
            'currency'         => 'required|in:IDR,USD,USC',
            'capital'          => "required|numeric|min:{$minCapital}",
            'trader_type'      => 'required|in:conservative,moderate,aggressive',
            'currency_pair'    => 'required|string',
            'stop_loss_pips'   => 'required|integer|min:5|max:200',
            'take_profit_pips' => 'required|integer|min:5|max:500',
            'risk_per_trade'   => 'nullable|numeric|min:0.5|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Create temporary plan object for calculation
        $plan = new \App\Models\TradingPlan();
        $plan->id = 0;
        $plan->trader_type = $request->trader_type;
        $plan->capital = $request->capital;
        $plan->currency = $request->currency;
        $plan->currency_pair = $request->currency_pair;
        $plan->stop_loss_pips = $request->stop_loss_pips;
        $plan->take_profit_pips = $request->take_profit_pips;
        $plan->risk_per_trade = $request->risk_per_trade ?? 2.0;
        $plan->updated_at = \Carbon\Carbon::now();

        // Calculate
        $result = $this->calculator->generate($plan);

        // Return preview (first 7 days for quick preview)
        $previewDays = array_slice($result['days'], 0, 7);

        return response()->json([
            'success' => true,
            'data' => [
                'preview_days'  => $previewDays,
                'summary'       => [
                    'initial_capital' => $request->capital,
                    'final_capital'   => $result['final_capital'],
                    'total_profit'    => $result['total_profit'],
                    'growth_pct'      => $result['growth_pct'],
                    'daily_target'    => $this->calculator->getTraderConfigs()[$request->trader_type]['dailyTarget'],
                    'max_risk'        => $this->calculator->getTraderConfigs()[$request->trader_type]['maxRisk'],
                    'max_trades'      => $this->calculator->getTraderConfigs()[$request->trader_type]['maxTrades'],
                ],
                'risk_reward'   => [
                    'stop_loss'   => $request->stop_loss_pips,
                    'take_profit' => $request->take_profit_pips,
                    'ratio'       => round($request->take_profit_pips / $request->stop_loss_pips, 1),
                ],
            ],
            'message' => 'Trading plan preview generated successfully',
        ]);
    }

    /**
     * Calculate lot size based on risk
     */
    public function calculateLotSize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'risk_amount'    => 'required|numeric|min:0',
            'stop_loss_pips' => 'required|integer|min:1',
            'currency_pair'  => 'required|string',
            'currency'       => 'nullable|in:IDR,USD,USC',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $lotSize = $this->calculator->calculateLotSize(
            $request->risk_amount,
            $request->stop_loss_pips,
            $request->currency_pair,
            $request->input('currency', 'USD')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'lot_size'      => $lotSize,
                'risk_amount'   => $request->risk_amount,
                'stop_loss'     => $request->stop_loss_pips,
                'currency_pair' => $request->currency_pair,
            ],
            'message' => 'Lot size calculated successfully',
        ]);
    }

    /**
     * Get available currency pairs
     */
    public function currencyPairs()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pairs' => $this->calculator->getCurrencyPairs(),
            ],
        ]);
    }

    /**
     * Get trader configurations
     */
    public function traderConfigs()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'configs' => $this->calculator->getTraderConfigs(),
            ],
        ]);
    }
}