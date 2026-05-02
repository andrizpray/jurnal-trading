<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trading Plan Export - {{ $plan->created_at->format('Y-m-d') }}</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #6B7280;
            font-size: 14px;
            margin-top: 5px;
        }
        .plan-info {
            background: #F9FAFB;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #4F46E5;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #4B5563;
            display: inline-block;
            width: 140px;
        }
        .info-value {
            color: #111827;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .table th {
            background: #4F46E5;
            color: white;
            text-align: left;
            padding: 10px;
            font-weight: bold;
        }
        .table td {
            padding: 8px 10px;
            border-bottom: 1px solid #E5E7EB;
        }
        .table tr:nth-child(even) {
            background: #F9FAFB;
        }
        .table tr:hover {
            background: #F3F4F6;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background: #F0F9FF;
            border-radius: 8px;
            border: 1px solid #BAE6FD;
        }
        .summary h3 {
            color: #0369A1;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .summary-item {
            padding: 10px;
            background: white;
            border-radius: 6px;
            border: 1px solid #E5E7EB;
        }
        .summary-label {
            font-size: 11px;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 11px;
        }
        .page-break {
            page-break-before: always;
        }
        .text-success {
            color: #059669;
        }
        .text-danger {
            color: #DC2626;
        }
        .text-warning {
            color: #D97706;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="margin-bottom: 10px;">
            <img src="{{ public_path('images/logo.png') }}" style="height: 60px;">
        </div>
        <h1>Trading Plan Report</h1>
        <div class="subtitle">
            Generated on {{ now()->format('d F Y H:i') }} • 
            Plan Date: {{ $plan->created_at->format('d F Y') }}
        </div>
    </div>

    <div class="plan-info">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Trader:</span>
                <span class="info-value">{{ $user->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $user->email }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Trader Type:</span>
                <span class="info-value">{{ ucfirst($plan->trader_type) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Currency:</span>
                <span class="info-value">{{ $plan->currency }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Currency Pair:</span>
                <span class="info-value">{{ $plan->currency_pair }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Risk per Trade:</span>
                <span class="info-value">{{ $plan->risk_per_trade }}%</span>
            </div>
            <div class="info-item">
                <span class="info-label">Stop Loss:</span>
                <span class="info-value">{{ $plan->stop_loss_pips }} pips</span>
            </div>
            <div class="info-item">
                <span class="info-label">Take Profit:</span>
                <span class="info-value">{{ $plan->take_profit_pips }} pips</span>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Day</th>
                <th>Capital</th>
                <th>Daily Target</th>
                <th>Risk Amount</th>
                <th>Lot Size</th>
                <th>Pair</th>
                <th>SL</th>
                <th>TP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($planData['days'] as $day)
            <tr>
                <td>{{ $day['day'] }}</td>
                <td>{{ $day['capital_formatted'] }}</td>
                <td>{{ $day['target_formatted'] }}</td>
                <td>{{ $day['risk_formatted'] }}</td>
                <td>{{ number_format($day['lot_size'], 4) }}</td>
                <td>{{ $plan->currency_pair }}</td>
                <td>{{ $plan->stop_loss_pips }}</td>
                <td>{{ $plan->take_profit_pips }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Plan Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Initial Capital</div>
                <div class="summary-value">{{ $calculator->formatCurrency($plan->capital, $plan->currency) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Final Capital</div>
                <div class="summary-value">{{ $calculator->formatCurrency($planData['final_capital'], $plan->currency) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Profit</div>
                <div class="summary-value text-success">{{ $calculator->formatCurrency($planData['total_profit'], $plan->currency) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Growth</div>
                <div class="summary-value text-success">{{ $planData['growth_pct'] }}%</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Daily Target</div>
                <div class="summary-value">{{ $planData['config']['dailyTarget'] }}%</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Max Risk</div>
                <div class="summary-value">{{ $planData['config']['maxRisk'] }}%</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Max Trades/Day</div>
                <div class="summary-value">{{ $planData['config']['maxTrades'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Risk/Reward Ratio</div>
                <div class="summary-value">1:{{ number_format($plan->take_profit_pips / $plan->stop_loss_pips, 1) }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>
            Trading Journal • Club Buy EA Community<br>
            Report ID: PLAN-{{ $plan->id }}-{{ $plan->created_at->format('Ymd') }}<br>
            This is an automated report. For support, contact: <a href="https://t.me/aloneeeeaja" style="color: #4F46E5; text-decoration: none;">t.me/aloneeeeaja</a>
        </p>
    </div>
</body>
</html>