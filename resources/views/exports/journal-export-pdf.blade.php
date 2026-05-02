<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Trading Journal — PDF Report</title>
    <style>
        @page { margin: 15mm 15mm 20mm 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; }

        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #06b6d4; padding-bottom: 12px; }
        .header h1 { font-size: 22px; color: #0f172a; margin-bottom: 4px; }
        .header .subtitle { color: #6b7280; font-size: 12px; }
        .header .period { color: #06b6d4; font-size: 11px; font-weight: 600; margin-top: 4px; }

        .stats-grid { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .stat-box { flex: 1; min-width: 100px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 8px 10px; text-align: center; }
        .stat-box .label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-box .value { font-size: 16px; font-weight: 700; color: #0f172a; }
        .stat-box .value.green { color: #16a34a; }
        .stat-box .value.red { color: #dc2626; }
        .stat-box .value.cyan { color: #0891b2; }
        .stat-box .sub { font-size: 9px; color: #9ca3af; }

        table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 16px; }
        thead tr { background: #0f172a; color: white; }
        thead th { padding: 7px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; white-space: nowrap; }
        tbody tr { border-bottom: 1px solid #e5e7eb; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 6px 8px; vertical-align: top; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-muted { color: #6b7280; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 9px; font-weight: 600; }
        .badge-win { background: #dcfce7; color: #16a34a; }
        .badge-loss { background: #fee2e2; color: #dc2626; }
        .badge-be { background: #f3f4f6; color: #6b7280; }
        .badge-buy { background: #dbeafe; color: #2563eb; }
        .badge-sell { background: #fee2e2; color: #dc2626; }
        .mood { color: #f59e0b; letter-spacing: 1px; }

        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #9ca3af; }

        .page-break { page-break-before: always; }

        .analysis-section { margin-top: 12px; }
        .analysis-entry { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; margin-bottom: 8px; page-break-inside: avoid; }
        .analysis-entry .date { font-weight: 700; color: #0f172a; font-size: 11px; margin-bottom: 4px; }
        .analysis-entry .pair { color: #0891b2; font-weight: 600; }
        .analysis-entry .label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-top: 4px; }
        .analysis-entry .content { font-size: 10px; color: #374151; margin-top: 2px; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>📓 Trading Journal Report</h1>
        <div class="subtitle">Club Buy EA Community</div>
        <div class="period">{{ $periodLabel }}</div>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-box">
            <div class="label">Total Entry</div>
            <div class="value cyan">{{ $stats->total ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Win Rate</div>
            <div class="value {{ ($winRate) >= 50 ? 'green' : 'red' }}">{{ $winRate }}%</div>
            <div class="sub">{{ $stats->wins ?? 0 }}W / {{ $stats->losses ?? 0 }}L</div>
        </div>
        <div class="stat-box">
            <div class="label">Total P&L</div>
            <div class="value {{ ($stats->total_pnl ?? 0) >= 0 ? 'green' : 'red' }}">
                {{ ($stats->total_pnl ?? 0) >= 0 ? '+' : '' }}{{ number_format($stats->total_pnl ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <div class="stat-box">
            <div class="label">Avg P&L / Trade</div>
            <div class="value cyan">{{ number_format($stats->avg_pnl ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Tanggal</th>
                <th>Pair</th>
                <th>Tipe</th>
                <th class="text-center">Hasil</th>
                <th class="text-right">P&L</th>
                <th class="text-center">Market</th>
                <th>Mood</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $i => $entry)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $entry->entry_date?->format('d/m/Y') }}</td>
                <td class="font-bold">{{ $entry->currency_pair ?? '—' }}</td>
                <td>
                    <span class="badge {{ $entry->trade_type === 'buy' ? 'badge-buy' : ($entry->trade_type === 'sell' ? 'badge-sell' : 'badge-be') }}">
                        {{ strtoupper($entry->trade_type) }}
                    </span>
                </td>
                <td class="text-center">
                    @if($entry->result)
                        <span class="badge {{ $entry->result === 'win' ? 'badge-win' : ($entry->result === 'loss' ? 'badge-loss' : 'badge-be') }}">
                            {{ strtoupper($entry->result) }}
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-right font-bold {{ ($entry->profit_loss ?? 0) >= 0 ? 'text-green' : 'text-red' }}">
                    {{ ($entry->profit_loss ?? 0) >= 0 ? '+' : '' }}{{ number_format($entry->profit_loss ?? 0, 0, ',', '.') }}
                </td>
                <td class="text-center">
                    {{ $entry->market_condition ? ucfirst($entry->market_condition) : '—' }}
                </td>
                <td>
                    <span class="mood">{{ $entry->emotion_score ? str_repeat('★', $entry->emotion_score) : '—' }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Analysis & Lessons --}}
    @if($entriesWithAnalysis->count() > 0)
    <div class="page-break"></div>
    <h2 style="font-size: 16px; color: #0f172a; margin-bottom: 12px; border-bottom: 2px solid #06b6d4; padding-bottom: 6px;">
        📝 Analisis & Pelajaran
    </h2>
    <div class="analysis-section">
        @foreach($entriesWithAnalysis as $entry)
        <div class="analysis-entry">
            <div class="date">
                {{ $entry->entry_date?->format('d/m/Y') }}
                <span class="pair">{{ $entry->currency_pair ?? '' }}</span>
                —
                <span class="{{ ($entry->profit_loss ?? 0) >= 0 ? 'text-green' : 'text-red' }}">
                    {{ ($entry->profit_loss ?? 0) >= 0 ? '+' : '' }}{{ number_format($entry->profit_loss ?? 0, 0, ',', '.') }}
                </span>
            </div>
            @if($entry->analysis)
                <div class="label">Analisis</div>
                <div class="content">{{ $entry->analysis }}</div>
            @endif
            @if($entry->lesson_learned)
                <div class="label">Pelajaran</div>
                <div class="content">{{ $entry->lesson_learned }}</div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Dicetak pada {{ now()->format('d F Y, H:i') }} — Trading Journal Pro &copy; {{ now()->year }}
    </div>

</body>
</html>
