@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex flex-col justify-between items-center p-4 sm:p-6 relative overflow-hidden bg-background">
    <!-- Ambient Light/Dark Glow Behind Card -->
    <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-96 sm:w-[540px] h-96 sm:h-[540px] bg-gradient-to-br from-sky-400/15 via-blue-500/10 dark:from-amber-500/15 dark:via-orange-600/10 to-transparent rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 left-1/2 -translate-x-1/2 w-80 sm:w-[480px] h-80 sm:h-[480px] bg-gradient-to-tr from-sky-500/10 dark:from-amber-600/10 to-transparent rounded-full blur-3xl pointer-events-none"></div>

    <!-- Top Navigation with Dark Mode Switcher -->
    <header class="w-full max-w-5xl flex justify-between items-center py-2 relative z-20">
        <div class="flex items-center gap-2">
            
        </div>
        <button id="darkModeToggle" type="button" class="p-2.5 rounded-xl border border-outline-variant bg-surface hover:bg-surface-container text-on-surface-variant hover:text-on-surface shadow-2xs transition-all cursor-pointer flex items-center gap-1.5 text-xs font-semibold" title="Beralih Tema Siang / Malam">
            <span class="material-symbols-outlined text-[18px]">contrast</span>
            <span class="hidden sm:inline">Tema Tampilan</span>
        </button>
    </header>

    <!-- Main Centered Authentication Card -->
    <main class="w-full max-w-[430px] my-auto relative z-20 py-4">
        <div class="w-full bg-surface rounded-2xl border border-outline-variant/60 shadow-xl p-7 sm:p-9 transition-all">
            <!-- Brand & Portal Header -->
            <div class="text-center mb-7">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 dark:from-amber-600 dark:to-orange-700 flex items-center justify-center p-2.5 shadow-md border border-white/20 mx-auto mb-4 transition-transform hover:scale-105">
                    <img alt="Royal Retail Hub" class="w-full h-full object-contain drop-shadow-sm" src="{{ asset('admin/assets/images/logo.png') }}">
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-on-surface tracking-tight leading-tight">
                    Royal Retail Hub
                </h1>
                <p class="text-[11px] font-bold text-primary dark:text-amber-400 uppercase tracking-widest mt-1">
                    Omnichannel Retail Platform
                </p>
                <p class="text-xs text-on-surface-variant mt-2 font-medium">
                    Masuk ke akun Anda
                </p>
            </div>

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Email / Username Input -->
                <div class="space-y-1.5 text-left">
                    <label class="block text-xs font-bold text-on-surface" for="email">
                        Email atau Username
                    </label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[18px] text-outline group-focus-within:text-primary dark:group-focus-within:text-amber-400 transition-colors pointer-events-none">
                            alternate_email
                        </span>
                        <input 
                            class="w-full pl-10 pr-3.5 py-2.5 bg-surface text-on-surface border border-outline-variant rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:focus:ring-amber-400/20 focus:border-primary dark:focus:border-amber-400 transition-all text-xs sm:text-sm placeholder:text-outline/70 @error('email') border-red-500 @enderror" 
                            id="email" 
                            name="email" 
                            placeholder="Masukkan email atau username..." 
                            type="text" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                        />
                    </div>
                    @error('email')
                        <p class="text-red-500 text-[11px] font-semibold mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">error</span>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5 text-left">
                    <div class="flex justify-between items-center">
                        <label class="block text-xs font-bold text-on-surface" for="password">
                            Password
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-primary dark:text-amber-400 hover:underline">
                                Lupa password?
                            </a>
                        @endif
                    </div>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[18px] text-outline group-focus-within:text-primary dark:group-focus-within:text-amber-400 transition-colors pointer-events-none">
                            lock
                        </span>
                        <input 
                            class="w-full pl-10 pr-10 py-2.5 bg-surface text-on-surface border border-outline-variant rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:focus:ring-amber-400/20 focus:border-primary dark:focus:border-amber-400 transition-all text-xs sm:text-sm placeholder:text-outline/70 @error('password') border-red-500 @enderror" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan kata sandi..." 
                            type="password" 
                            required
                        />
                        <button 
                            type="button" 
                            id="togglePasswordBtn" 
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors p-0.5 rounded cursor-pointer"
                            title="Tampilkan / Sembunyikan Password"
                        >
                            <span id="passwordToggleIcon" class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-[11px] font-semibold mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">error</span>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            id="remember" 
                            class="w-4 h-4 rounded text-primary dark:text-amber-500 focus:ring-primary dark:focus:ring-amber-400 border-outline-variant cursor-pointer"
                        />
                        <span class="text-xs text-on-surface-variant font-medium">
                            Ingat saya di perangkat ini
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full py-3 px-5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.98] bg-[#3FA2F6] hover:bg-[#258ae6] dark:bg-[#d97706] dark:hover:bg-[#b45309]"
                    >
                        <span>Masuk ke Dashboard</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Bottom Page Footer -->
    <footer class="w-full max-w-5xl py-3 text-center relative z-20">
        <p class="text-[11px] text-on-surface-variant/80 font-medium">
            &copy; {{ date('Y') }} Royal Retail Hub. All rights reserved.
        </p>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pwdInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const toggleIcon = document.getElementById('passwordToggleIcon');

    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
            toggleIcon.textContent = isPassword ? 'visibility_off' : 'visibility';
        });
    }
});
</script>
@endsection
