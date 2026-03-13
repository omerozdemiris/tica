<div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white flex items-center justify-center font-bold text-sm shadow-inner">
                {{ $comment->user->initials ?? '?' }}
            </div>
            <div>
                <h4 class="text-sm font-semibold text-gray-900 leading-none mb-1">
                    {{ $comment->user->name ?? 'Anonim Müşteri' }}
                </h4>
                <div class="flex text-yellow-400 text-xs">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="{{ $i <= $comment->rating ? 'ri-star-fill' : 'ri-star-line' }}"></i>
                    @endfor
                </div>
            </div>
        </div>
        <span class="text-[10px] font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-md">
            {{ $comment->created_at->translatedFormat('d F Y') }}
        </span>
    </div>

    <div class="relative">
        <i class="ri-double-quotes-l absolute -top-2 -left-2 text-2xl text-gray-100 -z-10"></i>
        <p
            class="text-sm text-gray-600 leading-relaxed italic line-clamp-4 hover:line-clamp-none transition-all cursor-default">
            "{{ $comment->comment }}"
        </p>
    </div>
</div>
