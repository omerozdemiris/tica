@extends($template . '.layouts.app')

@section('title', 'Blog')

@section('content')
    <section class="py-12 md:py-20">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h1 class="text-3xl md:text-5xl font-bold text-gray-900 mb-4">Blog</h1>
                <p class="text-gray-500 max-w-2xl text-lg">En yeni yazılarımız, haberler ve mutfak sırları.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-16">
                @forelse($posts as $post)
                    <a href="{{ route('blog.show', [$post->id, $post->slug]) }}" class="group block">
                        <div class="aspect-[4/3] rounded-[2rem] overflow-hidden bg-gray-100 mb-6 relative">
                            @if ($post->photo)
                                <img src="{{ asset($post->photo) }}" alt="{{ $post->title }}"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <i class="ri-article-line text-5xl"></i>
                                </div>
                            @endif
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-xs font-bold text-gray-400 uppercase tracking-widest">
                                <span>{{ $post->created_at->translatedFormat('d F Y') }}</span>
                            </div>
                            <h3
                                class="text-2xl font-bold text-gray-900 leading-tight group-hover:text-gray-600 transition-colors">
                                {{ $post->title }}
                            </h3>
                            @if ($post->excerpt)
                                <p class="text-gray-500 line-clamp-3 leading-relaxed">
                                    {{ $post->excerpt }}
                                </p>
                            @endif
                            <div
                                class="pt-2 flex items-center text-sm font-bold text-gray-900 uppercase tracking-wider gap-2">
                                <span>Devamını Oku</span>
                                <i class="ri-arrow-right-line transition-transform group-hover:translate-x-1"></i>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-20 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="ri-article-line text-3xl text-gray-300"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Henüz yazı bulunmuyor.</h3>
                        <p class="text-gray-500 mt-2">Daha sonra tekrar kontrol edin.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-16">
                {{ $posts->links() }}
            </div>
        </div>
    </section>
@endsection
