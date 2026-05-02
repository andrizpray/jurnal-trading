@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold gradient-text">⚙️ Pengaturan Akun</h1>
        <p class="text-gray-400 mt-1 text-sm">Kelola preferensi dan keamanan akun Anda</p>
    </div>

    {{-- Profile & Preferences --}}
    <div class="tech-card rounded-2xl p-6">
        <h2 class="font-bold text-lg mb-5 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                <i class="fas fa-user text-cyan-400 text-sm"></i>
            </div>
            Profil & Preferensi Trading
        </h2>

        <form action="{{ route('settings.update') }}" method="POST">
            @csrf @method('PATCH')

            <div class="mb-5">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name"
                       value="{{ old('name', $user->name) }}"
                       class="form-input" required>
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="form-input" required>
                @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label class="form-label">Mata Uang Default</label>
                <div class="flex gap-2">
                    <button type="button" value="IDR"
                        class="currency-btn {{ old('currency', $user->currency) === 'IDR' ? 'active' : '' }}">
                        🇮🇩 IDR (Rupiah)
                    </button>
                    <button type="button" value="USD"
                        class="currency-btn {{ old('currency', $user->currency) === 'USD' ? 'active' : '' }}">
                        🇺🇸 USD (Dollar)
                    </button>
                    <button type="button" value="USC"
                        class="currency-btn {{ old('currency', $user->currency) === 'USC' ? 'active' : '' }}">
                        ¢ USC (US Cent)
                    </button>
                </div>
                <input type="hidden" name="currency" id="currency-input"
                       value="{{ old('currency', $user->currency) }}">
            </div>

            <div class="mb-5">
                <label class="form-label">Modal Default</label>
                <input type="number" name="default_capital"
                       value="{{ old('default_capital', $user->default_capital) }}"
                       class="form-input" min="100000" placeholder="5000000">
                <p class="text-gray-600 text-xs mt-1">Digunakan sebagai nilai awal saat membuat challenge</p>
                @error('default_capital')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label class="form-label">Tipe Trader Default</label>
                <div class="flex gap-2">
                    @foreach([
                        'conservative' => ['emoji' => '🛡️', 'label' => 'Conservative', 'desc' => '1%/hari, risk rendah'],
                        'moderate'     => ['emoji' => '⚖️', 'label' => 'Moderate',     'desc' => '2%/hari, balanced'],
                        'aggressive'   => ['emoji' => '🔥', 'label' => 'Aggressive',   'desc' => '3%/hari, risk tinggi'],
                    ] as $type => $info)
                    <button type="button" data-value="{{ $type }}"
                        class="trader-btn {{ old('trader_type', $user->trader_type) === $type ? 'active' : '' }}">
                        <div class="text-xl mb-1">{{ $info['emoji'] }}</div>
                        <div class="text-xs font-semibold">{{ $info['label'] }}</div>
                        <div class="text-xs text-gray-500">{{ $info['desc'] }}</div>
                    </button>
                    @endforeach
                </div>
                <input type="hidden" name="trader_type" id="trader-type-input"
                       value="{{ old('trader_type', $user->trader_type) }}">
            </div>

            <div class="mb-6">
                <label class="form-label">Timezone</label>
                <select name="timezone" class="form-select">
                    <option value="Asia/Jakarta"  {{ $user->timezone === 'Asia/Jakarta'  ? 'selected' : '' }}>🕐 WIB — Jakarta (UTC+7)</option>
                    <option value="Asia/Makassar" {{ $user->timezone === 'Asia/Makassar' ? 'selected' : '' }}>🕑 WITA — Makassar (UTC+8)</option>
                    <option value="Asia/Jayapura" {{ $user->timezone === 'Asia/Jayapura' ? 'selected' : '' }}>🕒 WIT — Jayapura (UTC+9)</option>
                    <option value="Asia/Singapore"{{ $user->timezone === 'Asia/Singapore'? 'selected' : '' }}>🕐 SGT — Singapore (UTC+8)</option>
                    <option value="UTC"           {{ $user->timezone === 'UTC'           ? 'selected' : '' }}>🌍 UTC</option>
                </select>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-save mr-2"></i>Simpan Pengaturan
            </button>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="tech-card rounded-2xl p-6">
        <h2 class="font-bold text-lg mb-5 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                <i class="fas fa-lock text-yellow-400 text-sm"></i>
            </div>
            Ubah Password
        </h2>

        <form action="{{ route('settings.password') }}" method="POST">
            @csrf @method('PATCH')

            <div class="mb-4">
                <label class="form-label">Password Lama</label>
                <div class="password-wrapper">
                    <input type="password" name="current_password" id="current-password" class="form-input" required
                           placeholder="Masukkan password lama">
                    <button type="button" class="password-toggle" onclick="togglePassword('current-password', this)" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('current_password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label">Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="new-password" class="form-input" required minlength="8"
                           placeholder="Minimal 8 karakter">
                    <button type="button" class="password-toggle" onclick="togglePassword('new-password', this)" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-6">
                <label class="form-label">Konfirmasi Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="confirm-password" class="form-input" required
                           placeholder="Ulangi password baru">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm-password', this)" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-danger w-full">
                <i class="fas fa-key mr-2"></i>Update Password
            </button>
        </form>
    </div>

    {{-- Account Info --}}
    <div class="tech-card rounded-2xl p-6">
        <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                <i class="fas fa-info-circle text-blue-400 text-sm"></i>
            </div>
            Info Akun
        </h2>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-800">
                <span class="text-gray-400">Email</span>
                <span class="font-medium">{{ $user->email }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-800">
                <span class="text-gray-400">Bergabung Sejak</span>
                <span>{{ $user->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-800">
                <span class="text-gray-400">Tipe Trader</span>
                <span class="badge badge-active capitalize">{{ $user->trader_type }}</span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-gray-400">Mata Uang</span>
                <span class="font-semibold text-cyan-400">{{ $user->currency }}</span>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
<style>
    .password-wrapper { position: relative; }
    .password-wrapper input { padding-right: 2.75rem; }
    .password-toggle {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    .password-toggle:hover { color: #06b6d4; }
</style>
@endpush
@endsection
