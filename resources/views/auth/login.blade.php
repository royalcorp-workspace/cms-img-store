@extends('layouts.guest')

@section('title', 'Login - IMG')

@section('content')
<div class="flex min-h-screen flex-col md:flex-row">
    <!-- Left Panel: Branding -->
    <div class="brand-panel relative w-full md:w-1/2 flex flex-col justify-between p-8 md:p-16 lg:p-24 text-white overflow-hidden">
        <!-- Header -->
        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <img alt="IMG" class="h-10 w-auto" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCw1ZGF6302w4lWIVt_kcT4ogN19MHVhH30g8u7R7QqSJxbjNkjzMlYLRkTKnhZFcK9Zb2PRTEnC5f0XHJlp4HbGntruKwSNZaHNYqdR6caShbMgT2nzJLg0AjJLQ2wqGJs7plSGiVyo_pkR0MQRWukfoas2qrcsRkYecZcdZ7nRUFjo9DaqQTNjNhiNtpTe_W-ngzqOTw_UK0yV2Nn_dUKwTTnMBj4LSNnmvj-t3p9DK_njHJGxJbXo">
                <span class="font-headline-md text-headline-md font-bold tracking-tight text-white">IMG</span>
            </div>
        </div>

        <!-- Hero Content -->
        <div class="relative z-10 max-w-lg">
            <div class="mb-6">
                <span class="font-mono-sm text-mono-sm uppercase tracking-widest text-brand-gold">IMG Admin Dashboard</span>
            </div>
            <h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg mb-6 leading-tight text-white">
                Kelola produk dan pesanan dengan mudah.
            </h1>
            <p class="font-body-lg text-body-lg text-white/80 leading-relaxed">
                Platform admin yang dirancang untuk kebutuhan dealer IMG.
            </p>
        </div>

        <!-- Footer -->
        <div class="relative z-10">
            <p class="font-mono-sm text-mono-sm uppercase tracking-widest text-white/40">
                &copy; 2026 IMG
            </p>
        </div>
    </div>

    <!-- Right Panel: Login Form -->
    <div class="w-full md:w-1/2 bg-surface flex flex-col">
        <!-- Top Nav -->
        <header class="flex justify-between items-center px-6 py-6 md:px-12">
            <a href="#" class="flex items-center gap-2 text-on-surface-variant hover:text-brand-gold transition-colors font-body-md text-body-md group">
                <!-- <span class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span> -->
                
            </a>
            <span class="text-on-surface-variant font-body-md text-body-md">
                Baru di sini? <a href="#" class="text-brand-gold font-bold hover:underline">Buat akun</a>
            </span>
        </header>

        <!-- Form -->
        <main class="flex-grow flex items-center justify-center px-6 pb-16">
            <div class="w-full max-w-[420px] space-y-8">
                <div class="space-y-2">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface font-bold">Selamat datang</h2>
                    <p class="font-body-md text-body-md text-on-surface">
                        Masuk ke dashboard admin IMG.
                    </p>
                </div>

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="space-y-2">
                        <label class="block font-label-sm text-label-sm text-on-surface font-bold" for="email">Email</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline transition-colors group-focus-within:text-brand-gold">mail</span>
                            <input class="w-full pl-12 pr-4 py-3 bg-surface border border-outline-variant rounded-lg focus:ring-2 focus:ring-brand-gold/20 focus:border-brand-gold transition-all font-body-md text-body-md placeholder:text-outline-variant @error('email') border-danger @enderror" id="email" name="email" placeholder="email@contoh.com" type="email" value="{{ old('email') }}" required autofocus/>
                        </div>
                        @error('email')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="block font-label-sm text-label-sm text-on-surface font-bold" for="password">Password</label>
                            <a href="#" class="font-label-sm text-label-sm text-brand-gold hover:underline">Lupa password?</a>
                        </div>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline transition-colors group-focus-within:text-brand-gold">lock</span>
                            <input class="w-full pl-12 pr-4 py-3 bg-surface border border-outline-variant rounded-lg focus:ring-2 focus:ring-brand-gold/20 focus:border-brand-gold transition-all font-body-md text-body-md placeholder:text-outline-variant @error('password') border-danger @enderror" id="password" name="password" placeholder="Masukkan password" type="password" required/>
                        </div>
                        @error('password')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <input class="w-5 h-5 rounded border-outline-variant text-brand-gold focus:ring-brand-gold transition-colors" id="remember" name="remember" type="checkbox"/>
                        <label class="font-body-md text-body-md text-on-surface-variant cursor-pointer select-none" for="remember">
                            Ingat saya selama 30 hari
                        </label>
                    </div>

                    <button class="w-full bg-brand-gold text-brand-brown py-3.5 rounded-lg font-bold flex items-center justify-center gap-2 hover:brightness-110 active:scale-[0.98] transition-all group" type="submit">
                        Masuk
                        <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </button>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection
