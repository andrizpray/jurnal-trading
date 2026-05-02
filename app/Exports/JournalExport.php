<?php

namespace App\Exports;

use App\Models\JournalEntry;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JournalExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    private $entries;
    private $stats;
    private string $periodLabel;

    public function __construct($entries, $stats, string $periodLabel)
    {
        $this->entries = $entries;
        $this->stats = $stats;
        $this->periodLabel = $periodLabel;
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->entries as $entry) {
            $emotion = '';
            if ($entry->emotion_score) {
                $emotion = str_repeat('★', $entry->emotion_score) . str_repeat('☆', 5 - $entry->emotion_score);
            }

            $data[] = [
                $entry->entry_date?->format('d/m/Y') ?? '-',
                $entry->currency_pair ?? '-',
                strtoupper($entry->trade_type ?? '-'),
                $entry->result ? strtoupper($entry->result) : '-',
                $entry->profit_loss !== null ? number_format((float) $entry->profit_loss, 0, ',', '.') : '-',
                $entry->market_condition ? ucfirst($entry->market_condition) : '-',
                $emotion,
                $entry->analysis ?? '-',
                $entry->lesson_learned ?? '-',
            ];
        }

        // Summary rows
        $total = $this->stats->total ?? 0;
        $wins = $this->stats->wins ?? 0;
        $losses = $this->stats->losses ?? 0;
        $winRate = $total > 0 ? round(($wins / $total) * 100, 1) : 0;
        $totalPnl = $this->stats->total_pnl ?? 0;
        $avgPnl = $this->stats->avg_pnl ?? 0;

        $data[] = []; // empty separator
        $data[] = ['RINGKASAN', '', '', '', '', '', '', '', ''];
        $data[] = ['Total Entry', $total, '', '', '', '', '', '', ''];
        $data[] = ['Win / Loss', "{$wins}W / {$losses}L", '', '', '', '', '', '', ''];
        $data[] = ['Win Rate', $winRate . '%', '', '', '', '', '', '', ''];
        $data[] = ['Total P&L', ($totalPnl >= 0 ? '+' : '') . number_format((float) $totalPnl, 0, ',', '.'), '', '', '', '', '', '', ''];
        $data[] = ['Avg P&L / Trade', number_format((float) $avgPnl, 0, ',', '.'), '', '', '', '', '', '', ''];

        return $data;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Pair',
            'Tipe',
            'Hasil',
            'P&L',
            'Market',
            'Mood',
            'Analisis',
            'Pelajaran',
        ];
    }

    public function title(): string
    {
        return 'Trading Journal - ' . $this->periodLabel;
    }

    public function styles(Worksheet $sheet)
    {
        // Header row styling
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '06B6D4']],
        ]);

        $totalEntries = count($this->entries);
        $summaryStart = $totalEntries + 3;

        // Summary header
        $sheet->getStyle("A{$summaryStart}:I{$summaryStart}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '06B6D4']],
        ]);

        // Summary data rows
        $sheet->getStyle("A" . ($summaryStart + 1) . ":I" . ($summaryStart + 6))->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F0F9FF']],
        ]);

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Alternating row colors for data
        for ($i = 2; $i <= $totalEntries + 1; $i++) {
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$i}:I{$i}")->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F9FAFB']],
                ]);
            }
        }

        // Center certain columns
        $sheet->getStyle('D2:D' . ($totalEntries + 1))->getAlignment()->setHorizontal('center');
        $sheet->getStyle('F2:F' . ($totalEntries + 1))->getAlignment()->setHorizontal('center');
        $sheet->getStyle('G2:G' . ($totalEntries + 1))->getAlignment()->setHorizontal('center');

        return [];
    }
}
