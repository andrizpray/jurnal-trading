<?php

namespace App\Exports;

use App\Models\TradingPlan;
use App\Services\TradingCalculatorService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TradingPlanExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    private TradingPlan $plan;
    private TradingCalculatorService $calculator;
    private array $planData;

    public function __construct(TradingPlan $plan)
    {
        $this->plan = $plan;
        $this->calculator = new TradingCalculatorService();
        $this->planData = $this->calculator->generate($plan);
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->planData['days'] as $day) {
            $data[] = [
                $day['day'],
                $day['capital_formatted'],
                $day['target_formatted'],
                $day['risk_formatted'],
                $day['lot_size'],
                $this->plan->currency_pair,
                $this->plan->stop_loss_pips,
                $this->plan->take_profit_pips,
            ];
        }

        // Add summary rows
        $data[] = []; // Empty row
        $data[] = ['SUMMARY', '', '', '', '', '', '', ''];
        $data[] = ['Initial Capital', $this->calculator->formatCurrency($this->plan->capital, $this->plan->currency), '', '', '', '', '', ''];
        $data[] = ['Final Capital', $this->calculator->formatCurrency($this->planData['final_capital'], $this->plan->currency), '', '', '', '', '', ''];
        $data[] = ['Total Profit', $this->calculator->formatCurrency($this->planData['total_profit'], $this->plan->currency), '', '', '', '', '', ''];
        $data[] = ['Growth', $this->planData['growth_pct'] . '%', '', '', '', '', '', ''];
        $data[] = ['Trader Type', ucfirst($this->plan->trader_type), '', '', '', '', '', ''];
        $data[] = ['Risk per Trade', $this->plan->risk_per_trade . '%', '', '', '', '', '', ''];

        return $data;
    }

    public function headings(): array
    {
        return [
            'Day',
            'Capital',
            'Daily Target',
            'Risk Amount',
            'Lot Size',
            'Currency Pair',
            'SL (pips)',
            'TP (pips)',
        ];
    }

    public function title(): string
    {
        return 'Trading Plan ' . $this->plan->created_at->format('Y-m-d');
    }

    public function styles(Worksheet $sheet)
    {
        // Style for headers
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
        ]);

        // Style for summary section
        $summaryStart = count($this->planData['days']) + 3;
        $sheet->getStyle("A{$summaryStart}:H" . ($summaryStart + 7))->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F3F4F6']],
        ]);

        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}