@extends('admin.layouts.app')

@section('title', 'İzin Yönetimi')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-lg font-semibold">İzin Yönetimi</h1>
            <p class="text-sm text-gray-500">Admin kullanıcıların erişimlerini düzenle</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 inline-flex items-center gap-2">
            <i class="ri-group-line"></i>
            <span>Kullanıcılar</span>
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

    <form action="{{ route('admin.permissions.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="text-sm font-medium">Admin</label>
                    <select name="user_id" class="mt-1 w-full px-3 py-2 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"
                        onchange="this.form.submit()">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected($selectedUser && $selectedUser->id === $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Sadece role=1 adminler listelenir.</p>
                </div>
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold">Modül İzinleri</h2>
                        <button type="button" id="toggle-all"
                            class="text-xs px-3 py-1 rounded border border-gray-200 dark:border-gray-800">Tümünü seç</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach ($routes as $route)
                            @continue(in_array($route->name, config('admin.excluded_admin_routes', [])))
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" name="routes[]" value="{{ $route->id }}" class="rounded border-gray-300"
                                    {{ in_array($route->id, $selectedRoutes) ? 'checked' : '' }}>
                                <span class="capitalize">{{ $route->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2">
            <button type="submit"
                class="px-4 py-2 rounded-md bg-black text-white dark:bg-white dark:text-black text-sm">Kaydet</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleAll = document.getElementById('toggle-all');
            const panel = document.querySelector('[name="routes[]"]')?.closest('div');

            if (toggleAll && panel) {
                toggleAll.addEventListener('click', function() {
                    const inputs = panel.parentElement.querySelectorAll('input[type="checkbox"]');
                    const shouldCheck = Array.from(inputs).some(i => !i.checked);
                    inputs.forEach(i => i.checked = shouldCheck);
                });
            }
        });
    </script>
@endpush

