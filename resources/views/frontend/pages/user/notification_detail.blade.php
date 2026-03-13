@extends('frontend.layouts.app')

@section('title', $notification->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('user.notifications.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900 mb-6 transition-colors">
            <i class="ri-arrow-left-line"></i>
            Bildirimlere Dön
        </a>

        <div class="bg-white rounded-xl shadow-sm border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} p-6 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full {{ $theme->color ? 'bg-' . $theme->color . '/10' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0">
                        <i class="ri-notification-3-line {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $notification->title }}</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ $notification->created_at->translatedFormat('d F Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="prose prose-blue max-w-none text-gray-700 leading-relaxed">
                {{ $notification->text }}
            </div>

            @if(!empty($contextData) && $contextData->count() > 0)
                <div class="mt-10 pt-10 border-t border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">İlgili İçerikler</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($contextData as $item)
                            @if($notification->context_type === 'product' || $notification->product_id)
                                <a href="{{ route('products.show', [$item->id, $item->slug]) }}" class="group block bg-gray-50 rounded-xl p-4 hover:bg-white hover:shadow-md transition-all border border-transparent hover:border-gray-100">
                                    <div class="aspect-square rounded-lg overflow-hidden mb-3 bg-white">
                                        <img src="{{ $item->photo }}" alt="{{ $item->title }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform">
                                    </div>
                                    <h3 class="font-medium text-gray-900 truncate">{{ $item->title }}</h3>
                                    <div class="mt-1">
                                        @if($item->discount_price > 0)
                                            <span class="text-xs text-gray-400 line-through mr-1">
                                                {{ number_format($item->price, 2, ',', '.') }} TL
                                            </span>
                                            <span class="text-sm {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} font-bold">
                                                {{ number_format($item->discount_price, 2, ',', '.') }} TL
                                            </span>
                                        @else
                                            <span class="text-sm {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} font-bold">
                                                {{ number_format($item->price, 2, ',', '.') }} TL
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            @elseif($notification->context_type === 'category')
                                <a href="{{ route('categories.show', [$item->id, $item->slug]) }}" class="block bg-gray-50 rounded-xl p-4 hover:bg-white hover:shadow-md transition-all border border-transparent hover:border-gray-100 text-center">
                                    <h3 class="font-bold text-gray-900">{{ $item->name }}</h3>
                                    <p class="text-xs text-gray-500 mt-1">Kategoriyi İncele</p>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
