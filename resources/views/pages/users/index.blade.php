@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Users</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Users</span>
        </nav>
    </div>
    <div class="flex items-center gap-3">
        <div class="relative group">
            <button type="button" class="flex items-center gap-2 px-3 py-1.5 text-on-surface-variant hover:text-on-surface rounded-lg transition-colors">
                <span class="material-symbols-outlined text-[18px]">info</span>
                <span class="text-label-md font-label-md">Tutorial</span>
            </button>
            <div class="hidden group-hover:block absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 z-10">
                <h4 class="font-label-md text-label-md text-on-surface mb-2">Users Management:</h4>
                <ul class="text-body-md text-on-surface-variant space-y-1 list-disc list-inside">
                    <li>Click Add User to create new user account</li>
                    <li>Assign roles to control user permissions</li>
                    <li>Use search to filter users by name or email</li>
                </ul>
            </div>
        </div>
        <button class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md text-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Add User
        </button>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-gray">
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider" style="width: 40px;">
                        <input class="w-4 h-4" type="checkbox" id="selectAll">
                    </th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">User</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Email</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Role</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                @foreach($users as $user)
                <tr class="hover:bg-surface-container/30 transition-colors">
                    <td class="px-gutter py-4"><input class="w-4 h-4" type="checkbox"></td>
                    <td class="px-gutter py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white text-label-sm font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                            <span class="font-body-md text-body-md text-on-surface">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $user->email }}</td>
                    <td class="px-gutter py-4">
                        @if($user->roles->isNotEmpty())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-label-sm font-label-sm bg-primary-container/20 text-primary">{{ $user->roles->first()->name }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-label-sm font-label-sm bg-surface-container-low text-on-surface-variant">No Role</span>
                        @endif
                    </td>
                    <td class="px-gutter py-4"><span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-success/10 text-success rounded-full text-label-sm font-label-sm">Active</span></td>
                    <td class="px-gutter py-4">
                        <div class="flex gap-2">
                            <button class="text-on-surface-variant hover:text-on-surface transition-colors" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                            <button class="text-danger hover:opacity-70 transition-opacity" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-gutter py-4 border-t border-outline-variant/20">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection