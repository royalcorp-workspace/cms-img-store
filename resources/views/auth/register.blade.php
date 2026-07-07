@extends('layouts.guest')

@section('title', 'Register - IMG')

@section('content')
    <div class="auth-card bg-white">
        <div class="auth-header">
            <div class="logo d-inline-flex align-items-center justify-content-center bg-primary rounded mb-3" style="width:48px;height:48px;">
                <i class="bi bi-lightning-fill text-white fs-4"></i>
            </div>
            <h5 class="mb-0 fw-bold">Create Account</h5>
            <small class="text-muted">Daftar untuk masuk ke IMG Admin</small>
        </div>
        <div class="auth-body">
            <form>
                <div class="mb-3">
                    <label class="form-label fw-medium">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Masukkan nama lengkap" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" placeholder="Masukkan email" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" placeholder="Masukkan password" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Konfirmasi Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" placeholder="Konfirmasi password" required>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label" for="terms">Saya setuju dengan syarat dan ketentuan</label>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-person-plus me-1"></i> Daftar</button>
            </form>
            <div class="text-center mt-4">
                <small class="text-muted">Sudah punya akun? <a href="{{ route('login') }}" class="text-decoration-none" style="color:var(--bs-primary)">Masuk</a></small>
            </div>
        </div>
    </div>
@endsection