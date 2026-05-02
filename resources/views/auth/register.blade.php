<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Trading Journal</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            inter: ['Inter', 'sans-serif'],
                        },
                    },
                },
            }
        </script>
        <style>
            .tech-card {
                background: linear-gradient(135deg, rgba(17,24,39,0.95) 0%, rgba(9,14,28,0.95) 100%);
                border: 1px solid rgba(55,65,81,0.5);
                backdrop-filter: blur(4px);
            }
            .form-input {
                width: 100%;
                background: #1f2937;
                border: 1px solid #374151;
                border-radius: 0.75rem;
                padding: 0.75rem 1rem;
                color: white;
                transition: all 0.2s;
            }
            .form-input::placeholder { color: #4b5563; }
            .form-input:focus {
                outline: none;
                border-color: #06b6d4;
                box-shadow: 0 0 0 2px rgba(6,182,212,0.3);
            }
            .form-label {
                display: block;
                font-size: 0.875rem;
                font-weight: 500;
                color: #9ca3af;
                margin-bottom: 0.375rem;
            }
            .btn-primary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(to right, #06b6d4, #4ade80);
                color: black;
                font-weight: 700;
                padding: 0.625rem 1.25rem;
                border-radius: 0.75rem;
                cursor: pointer;
                transition: all 0.2s;
                border: none;
            }
            .btn-primary:hover { opacity: 0.9; }
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
    @endif
</head>
<body class="bg-gray-950 text-gray-100 font-inter min-h-screen flex items-center justify-center p-4">

    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-green-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 left-1/4 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500 to-green-400 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-green-500/20">
                <i class="fas fa-chart-line text-2xl text-black font-bold"></i>
            </div>
            <h1 class="text-2xl font-bold">Trading Journal</h1>
            <p class="text-gray-400 text-sm mt-1">Mulai perjalanan trading Anda hari ini!</p>
        </div>

        <div class="tech-card rounded-2xl p-8">
            <h2 class="text-xl font-bold mb-6 text-center">Buat Akun Baru</h2>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-input" placeholder="John Trader" required autofocus>
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-input" placeholder="trader@example.com" required>
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="reg-password"
                               class="form-input" placeholder="Minimal 8 karakter" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('reg-password', this)" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mb-6">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password_confirmation" id="reg-password-confirm"
                               class="form-input" placeholder="Ulangi password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('reg-password-confirm', this)" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full text-base py-3.5">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                </button>
            </form>

            <p class="text-center text-gray-500 text-sm mt-6">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold">
                    Login di sini
                </a>
            </p>
        </div>
    </div>

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
</body>
</html>
