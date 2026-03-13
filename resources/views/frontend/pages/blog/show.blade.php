@extends('frontend.layouts.app')

@section('title', $post->title)

@section('content')
    <article class="py-12 md:py-20">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight mb-6">
                        {{ $post->title }}
                    </h1>
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span>{{ $post->created_at->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
                @if ($post->photo)
                    <div class="mb-12 aspect-[16/9] rounded-xl overflow-hidden bg-gray-100">
                        <img src="{{ asset($post->photo) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                    </div>
                @endif
                <div
                    class="prose prose-lg prose-gray max-w-none prose-headings:font-bold prose-headings:text-gray-900 prose-p:leading-relaxed prose-img:rounded-xl">
                    {!! $post->content !!}
                </div>
                <div
                    class="mt-12 pt-8 border-t border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <a href="{{ route('blog.index') }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-blue-600' }} hover:underline group">
                        <i class="ri-arrow-left-line transition-transform group-hover:-translate-x-1"></i>
                        <span>Tüm Yazılara Dön</span>
                    </a>

                    <div class="flex items-center gap-4 text-gray-400">
                        <span class="text-xs font-medium uppercase tracking-wide mr-2">Paylaş:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}"
                            target="_blank" class="hover:text-gray-900 transition-colors"><i
                                class="ri-facebook-fill text-xl"></i></a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($post->title) }}"
                            target="_blank" class="hover:text-gray-900 transition-colors"><i
                                class="ri-twitter-fill text-xl"></i></a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->fullUrl()) }}"
                            target="_blank" class="hover:text-gray-900 transition-colors"><i
                                class="ri-linkedin-fill text-xl"></i></a>
                        <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . request()->fullUrl()) }}"
                            target="_blank" class="hover:text-gray-900 transition-colors"><i
                                class="ri-whatsapp-line text-xl"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </article>
@endsection
