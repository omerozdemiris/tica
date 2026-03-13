@extends('frontend.layouts.app')
@section('title', 'Bildirimlerim')
@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold mb-6 text-gray-900">Bildirimlerim</h1>

            <div class="bg-white rounded-xl shadow-sm border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} overflow-hidden">
                @forelse($notifications as $notification)
                    <div
                        class="p-4 border-b border-gray-100 flex items-start gap-4 {{ !$notification->read_at ? 'bg-blue-50/20' : '' }} hover:bg-gray-50 transition-colors">
                        <div
                            class="w-12 h-12 rounded-full {{ $theme->color ? 'bg-' . $theme->color . '/10' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0">
                            <i
                                class="ri-notification-3-line {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="font-semibold text-gray-900">{{ $notification->title }}</h3>
                                <span
                                    class="text-xs text-gray-400">{{ $notification->created_at->translatedFormat('d F Y H:i') }}</span>
                            </div>
                            <p class="text-gray-600 mt-1 text-sm">{{ $notification->text }}</p>
                            <div class="mt-3">
                                <a href="{{ route('user.notifications.show', $notification->id) }}"
                                    class="inline-flex items-center gap-1 text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:underline">
                                    Detayları Gör
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="ri-notification-off-line text-4xl text-gray-200"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Bildirim bulunamadı</h3>
                        <p class="text-gray-500 mt-1">Henüz size özel bir bildirim bulunmuyor.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
@endsection
