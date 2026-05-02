{{-- Enhanced Analytics Dashboard --}}

{{-- Quick Stats Row --}}
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3 mb-6">
    <div class="stat-card flex items-center gap-3 min-w-0">
        <div class="stat-icon bg-green-500/10 text-green-400 shrink-0">
            <i class="fas fa-trophy"></i>
        </div>
        <div class="stat-content min-w-0">
            <div class="stat-value">{{ $quickStats['win_rate'] }}%</div>
            <div class="stat-label">Win Rate</div>
        </div>
    </div>
    
    <div class="stat-card flex items-center gap-3 min-w-0">
        <div class="stat-icon bg-cyan-500/10 text-cyan-400 shrink-0">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content min-w-0">
            <div class="stat-value {{ $quickStats['total_profit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                {{ $quickStats['total_profit'] >= 0 ? '+' : '' }}{{ number_format($quickStats['total_profit'], 0) }}
            </div>
            <div class="stat-label">Total Profit</div>
        </div>
    </div>
    
    <div class="stat-card flex items-center gap-3 min-w-0">
        <div class="stat-icon bg-purple-500/10 text-purple-400 shrink-0">
            <i class="fas fa-exchange-alt"></i>
        </div>
        <div class="stat-content min-w-0">
            <div class="stat-value">{{ $quickStats['total_trades'] }}</div>
            <div class="stat-label">Total Trades</div>
        </div>
    </div>
    
    <div class="stat-card flex items-center gap-3 min-w-0">
        <div class="stat-icon bg-yellow-500/10 text-yellow-400 shrink-0">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="stat-content min-w-0">
            <div class="stat-value {{ $quickStats['profit_factor'] >= 1.5 ? 'text-green-400' : ($quickStats['profit_factor'] >= 1 ? 'text-yellow-400' : 'text-red-400') }}">
                {{ $quickStats['profit_factor'] }}
            </div>
            <div class="stat-label">Profit Factor</div>
        </div>
    </div>
    
    <div class="stat-card flex items-center gap-3 min-w-0 sm:col-span-2 md:col-span-3 xl:col-span-1">
        <div class="stat-icon bg-pink-500/10 text-pink-400 shrink-0">
            <i class="fas fa-fire"></i>
        </div>
        <div class="stat-content min-w-0">
            <div class="stat-value">{{ $quickStats['active_challenges'] }}</div>
            <div class="stat-label">Active Challenges</div>
        </div>
    </div>
</div>

{{-- Performance Metrics --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Performance Overview --}}
    <div class="tech-card rounded-2xl p-6">
        <h2 class="text-lg font-bold mb-5 flex items-center">
            <i class="fas fa-chart-bar text-cyan-400 mr-2"></i>Performance Metrics
        </h2>
        
        <div class="space-y-4">
            @foreach($analytics['performance'] as $metric => $value)
                @php
                    $metricConfig = [
                        'win_rate' => ['label' => 'Win Rate', 'icon' => 'fa-trophy', 'color' => 'green', 'suffix' => '%', 'good' => '>50'],
                        'profit_factor' => ['label' => 'Profit Factor', 'icon' => 'fa-balance-scale', 'color' => 'yellow', 'suffix' => '', 'good' => '>1.5'],
                        'avg_rr_ratio' => ['label' => 'Avg R:R Ratio', 'icon' => 'fa-exchange-alt', 'color' => 'purple', 'suffix' => '', 'good' => '>1.5'],
                        'consistency_score' => ['label' => 'Consistency', 'icon' => 'fa-chart-line', 'color' => 'blue', 'suffix' => '%', 'good' => '>70'],
                        'emotional_control' => ['label' => 'Emotional Control', 'icon' => 'fa-brain', 'color' => 'pink', 'suffix' => '%', 'good' => '>60'],
                    ][$metric] ?? ['label' => ucfirst(str_replace('_', ' ', $metric)), 'icon' => 'fa-chart-pie', 'color' => 'gray', 'suffix' => ''];
                    
                    $isGood = false;
                    if (isset($metricConfig['good'])) {
                        $threshold = (float) str_replace('>', '', $metricConfig['good']);
                        $isGood = $value >= $threshold;
                    }
                @endphp
                
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-3 rounded-lg bg-gray-800/30 hover:bg-gray-800/50 transition">
                    <div class="flex items-center min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-{{ $metricConfig['color'] }}-500/10 flex items-center justify-center mr-3 shrink-0">
                            <i class="fas {{ $metricConfig['icon'] }} text-{{ $metricConfig['color'] }}-400"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium text-sm sm:text-base">{{ $metricConfig['label'] }}</div>
                            <div class="text-xs text-gray-500">{{ $metricConfig['good'] ?? 'Higher is better' }}</div>
                        </div>
                    </div>
                    <div class="text-lg font-bold shrink-0 sm:text-right {{ $isGood ? 'text-green-400' : 'text-gray-400' }}">
                        {{ number_format($value, $metric === 'profit_factor' || $metric === 'avg_rr_ratio' ? 2 : 0) }}{{ $metricConfig['suffix'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    {{-- Trading Habits --}}
    <div class="tech-card rounded-2xl p-6">
        <h2 class="text-lg font-bold mb-5 flex items-center">
            <i class="fas fa-chart-pie text-purple-400 mr-2"></i>Trading Habits
        </h2>
        
        <div class="space-y-4">
            {{-- Top Currency Pairs --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-400 mb-2">Top Currency Pairs</h3>
                <div class="space-y-2">
                    @forelse($analytics['trading_habits']['top_pairs'] as $pair)
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between p-2 rounded bg-gray-800/30">
                            <div class="font-medium text-sm sm:text-base truncate">{{ $pair['pair'] }}</div>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-sm">
                                <span class="text-gray-400">{{ $pair['count'] }} trades</span>
                                <span class="{{ $pair['win_rate'] >= 50 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $pair['win_rate'] }}% win rate
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-gray-500">
                            <i class="fas fa-chart-pie text-2xl mb-2 block"></i>
                            No trading data yet
                        </div>
                    @endforelse
                </div>
            </div>
            
            {{-- Trading Times --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-400 mb-2">Preferred Trading Times</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach(['morning' => '🌅 Morning', 'afternoon' => '☀️ Afternoon', 'evening' => '🌙 Evening', 'night' => '🌌 Night'] as $key => $label)
                        <div class="text-center p-2 rounded bg-gray-800/30">
                            <div class="text-xs text-gray-500">{{ $label }}</div>
                            <div class="font-bold">{{ $analytics['trading_habits']['trading_times'][$key] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Monthly Progress & Recommendations --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Monthly Progress --}}
    <div class="tech-card rounded-2xl p-6">
        <h2 class="text-lg font-bold mb-5 flex items-center">
            <i class="fas fa-calendar-alt text-green-400 mr-2"></i>Monthly Progress
        </h2>
        
        <div class="space-y-3">
            @foreach(array_reverse($analytics['monthly_progress']) as $month)
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-3 rounded-lg bg-gray-800/30 hover:bg-gray-800/50 transition">
                    <div class="font-medium text-sm sm:text-base">{{ $month['month'] }}</div>
                    <div class="flex flex-wrap items-stretch justify-between sm:justify-end gap-3 sm:gap-4 w-full sm:w-auto">
                        <div class="text-center min-w-[4rem]">
                            <div class="text-xs text-gray-500">Trades</div>
                            <div class="font-bold">{{ $month['total_trades'] }}</div>
                        </div>
                        <div class="text-center min-w-[4rem]">
                            <div class="text-xs text-gray-500">Win Rate</div>
                            <div class="font-bold {{ $month['win_rate'] >= 50 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $month['win_rate'] }}%
                            </div>
                        </div>
                        <div class="text-center min-w-[4rem]">
                            <div class="text-xs text-gray-500">Profit</div>
                            <div class="font-bold {{ $month['total_profit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $month['total_profit'] >= 0 ? '+' : '' }}{{ number_format($month['total_profit'], 0) }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    {{-- Recommendations --}}
    <div class="tech-card rounded-2xl p-6">
        <h2 class="text-lg font-bold mb-5 flex items-center">
            <i class="fas fa-lightbulb text-yellow-400 mr-2"></i>Personalized Recommendations
        </h2>
        
        <div class="space-y-3">
            @forelse($analytics['recommendations'] as $rec)
                <div class="p-3 rounded-lg border-l-4 
                    {{ $rec['type'] === 'success' ? 'border-green-500 bg-green-500/5' : 
                       ($rec['type'] === 'warning' ? 'border-yellow-500 bg-yellow-500/5' : 
                       'border-blue-500 bg-blue-500/5') }}">
                    <div class="flex items-start">
                        <i class="fas 
                            {{ $rec['type'] === 'success' ? 'fa-check-circle text-green-400' : 
                               ($rec['type'] === 'warning' ? 'fa-exclamation-triangle text-yellow-400' : 
                               'fa-info-circle text-blue-400') }} 
                            mt-1 mr-3"></i>
                        <div class="flex-1">
                            <h4 class="font-bold text-white">{{ $rec['title'] }}</h4>
                            <p class="text-sm text-gray-400 mt-1">{{ $rec['message'] }}</p>
                            <div class="mt-2 text-sm text-cyan-400">
                                <i class="fas fa-bullseye mr-1"></i>{{ $rec['action'] }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-chart-line text-3xl mb-3 block"></i>
                    Start trading to get personalized recommendations
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Challenge Status --}}
@if($analytics['challenge_status']['has_active'])
    <div class="tech-card rounded-2xl p-6 mt-6">
        <h2 class="text-lg font-bold mb-5 flex items-center">
            <i class="fas fa-fire text-orange-400 mr-2"></i>Active Challenge Progress
        </h2>
        
        @php $challenge = $analytics['challenge_status']['challenge']; @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 rounded-xl bg-gradient-to-br from-orange-500/10 to-red-500/10 border border-orange-500/20">
                <div class="text-2xl font-bold text-orange-400">{{ $challenge['days_completed'] }}/{{ $challenge['total_days'] }}</div>
                <div class="text-sm text-gray-400 mt-1">Days Completed</div>
                <div class="mt-2">
                    <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-orange-500 rounded-full" style="width: {{ $challenge['progress'] }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ $challenge['progress'] }}% complete</div>
                </div>
            </div>
            
            <div class="text-center p-4 rounded-xl bg-gradient-to-br from-green-500/10 to-cyan-500/10 border border-green-500/20">
                <div class="text-2xl font-bold text-green-400">
                    {{ number_format($challenge['current_capital'], 0) }}
                </div>
                <div class="text-sm text-gray-400 mt-1">Current Capital</div>
                <div class="mt-2">
                    <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full" style="width: {{ min(100, $challenge['profit_progress']) }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $challenge['profit_progress'] }}% of target
                    </div>
                </div>
            </div>
            
            <div class="text-center p-4 rounded-xl bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-purple-500/20">
                <div class="text-2xl font-bold {{ $challenge['total_profit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $challenge['total_profit'] >= 0 ? '+' : '' }}{{ number_format($challenge['total_profit'], 0) }}
                </div>
                <div class="text-sm text-gray-400 mt-1">Total Profit</div>
                <div class="mt-2 text-sm">
                    <div class="text-gray-400">Target: {{ number_format($challenge['target_profit'], 0) }}</div>
                    <div class="text-cyan-400">{{ $challenge['remaining_days'] }} days remaining</div>
                </div>
            </div>
        </div>
    </div>
@endif