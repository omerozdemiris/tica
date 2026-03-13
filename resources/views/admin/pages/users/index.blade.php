@extends('admin.layouts.app')

@section('title', 'Kullanıcılar')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-lg font-semibold">Kullanıcılar</h1>
            <p class="text-sm text-gray-500">Admin ve süper admin hesapları</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black inline-flex items-center gap-2">
            <i class="ri-add-line"></i>
            <span>Yeni Kullanıcı</span>
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded border border-green-200 bg-green-50 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded border border-red-200 bg-red-50 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="divide-y divide-gray-100 dark:divide-gray-900">
            @forelse ($users as $user)
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-900 flex items-center justify-center text-sm font-semibold">
                            {{ $user->initials }}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $user->email }} @ {{ $user->username }}</div>
                        </div>
                        <span
                            class="ml-3 inline-flex items-center px-2 py-0.5 rounded-full text-xs border {{ $user->isSuperAdmin() ? 'border-purple-200 bg-purple-50 text-purple-700' : 'border-blue-200 bg-blue-50 text-blue-700' }}">
                            {{ $user->isSuperAdmin() ? 'Süper Admin' : 'Admin' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.users.edit', $user->id) }}"
                            class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            <i class="ri-pencil-line"></i>
                            <span>Düzenle</span>
                        </a>
                        @if (request()->session()->get('admin_user_id') != $user->id)
                            <button data-delete data-url="{{ route('admin.users.destroy', $user->id) }}"
                                data-confirm="Bu kullanıcıyı silmek istediğinize emin misiniz?"
                                class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <i class="ri-delete-bin-line"></i>
                                <span>Sil</span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-sm text-gray-500">
                    Henüz kullanıcı bulunmuyor.
                </div>
            @endforelse
        </div>
    </div>
@endsection
