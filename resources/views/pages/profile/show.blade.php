@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">My Profile</h1>
            <p class="text-body-md text-on-surface-variant mt-1">Manage your personal information and account settings.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-container-gap mb-8">
        <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30 text-center">
            <div class="w-20 h-20 rounded-full bg-primary text-white flex items-center justify-center text-[32px] font-headline-lg mx-auto mb-4">A</div>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-1">Admin User</h3>
            <p class="text-body-md text-secondary mb-4">Store Manager</p>
            <p class="text-label-sm text-on-surface-variant mb-6">admin@example.com</p>
            <div class="flex justify-center gap-2 mb-4">
                <button class="w-9 h-9 rounded-lg border border-outline-variant flex items-center justify-center text-on-surface-variant hover:bg-surface-container transition-colors">
                    <span class="material-symbols-outlined text-[18px]">share</span>
                </button>
            </div>
            <button class="flex items-center gap-2 w-full px-4 py-2.5 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors justify-center">
                <span class="material-symbols-outlined text-[18px]">download</span>
                Download Profile
            </button>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="border-b border-outline-variant">
                <nav class="flex">
                    <button class="flex-1 px-6 py-3.5 text-center font-label-md font-bold text-primary border-b-2 border-primary bg-surface-container-low/30">Personal Information</button>
                    <button class="flex-1 px-6 py-3.5 text-center font-label-md text-on-surface-variant hover:text-on-surface transition-colors">Security</button>
                </nav>
            </div>
            <div class="p-gutter">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">First Name</label>
                        <input type="text" value="Admin" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Last Name</label>
                        <input type="text" value="User" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Email Address</label>
                        <input type="email" value="admin@example.com" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Phone Number</label>
                        <input type="tel" value="+1-555-0123" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-outline-variant/30 flex justify-end">
                    <button class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white rounded-lg font-label-md text-label-md hover:opacity-90 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
