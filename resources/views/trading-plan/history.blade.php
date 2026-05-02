@extends('layouts.app')
@section('title', 'Riwayat Trading Plan')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <h1 class="text-xl sm:text-2xl font-bold gradient-text">📋 Riwayat Trading Plan</h1>
        <p class="text-gray-400 mt-1 text-sm">Semua trading plan yang pernah dibuat</p>
    </div>
    <a href="{{ route('trading-plan.index') }}" class="btn-secondary text-sm w-full sm:w-auto text-center justify-center shrink-0">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="tech-card rounded-2xl p-4 sm:p-6">
    @if($plans->isEmpty())
        <div class="text-center py-12 text-gray-600">
            <i class="fas fa-history text-4xl mb-3 block"></i>
            Belum ada riwayat trading plan.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Modal</th>
                        <th>Pair</th>
                        <th>Tipe</th>
                        <th>SL</th>
                        <th>TP</th>
                        <th>R:R</th>
                        <th>Status</th>
                        <th class="text-center">Export</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plans as $p)
                    <tr>
                        <td class="text-gray-400 text-xs">{{ $p->created_at->format('d/m/Y') }}</td>
                        <td class="font-semibold">
                            {{ $p->currency === 'IDR'
                                ? 'Rp ' . number_format($p->capital, 0, ',', '.')
                                : '$' . number_format($p->capital, 2) }}
                        </td>
                        <td class="text-cyan-400 font-medium">{{ $p->currency_pair }}</td>
                        <td class="capitalize">{{ $p->trader_type }}</td>
                        <td class="text-red-400">{{ $p->stop_loss_pips }}p</td>
                        <td class="text-green-400">{{ $p->take_profit_pips }}p</td>
                        <td class="font-semibold">1:{{ $p->rr_ratio }}</td>
                        <td>
                            @if($p->is_active)
                                <span class="badge badge-active">AKTIF</span>
                            @else
                                <span class="badge bg-gray-700 text-gray-400">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex gap-1">
                                <a href="{{ route('trading-plan.export.excel', $p) }}" 
                                   class="btn-icon text-green-400 hover:text-green-300" 
                                   title="Export Excel">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                                <a href="{{ route('trading-plan.export.pdf', $p) }}" 
                                   class="btn-icon text-red-400 hover:text-red-300" 
                                   title="Export PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $plans->links() }}</div>
    @endif
</div>
@endsection
