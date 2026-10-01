@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span
                    class="inline-flex items-center px-3 py-1.5 text-[10px] leading-none text-gray-400 border border-black/50 bg-white min-h-[30px]">
                    Önceki
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="inline-flex items-center px-3 py-1.5 text-[10px] leading-none text-gray-800 border border-black/50 bg-white hover:bg-black/5 transition min-h-[30px]">
                    Önceki
                </a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="inline-flex items-center px-3 py-1.5 ml-2 text-[10px] leading-none text-gray-800 border border-black/50 bg-white hover:bg-black/5 transition min-h-[30px]">
                    Sonraki
                </a>
            @else
                <span
                    class="inline-flex items-center px-3 py-1.5 ml-2 text-[10px] leading-none text-gray-400 border border-black/50 bg-white min-h-[30px]">
                    Sonraki
                </span>
            @endif
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-gray-500">
                    {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} / {{ $paginator->total() }}
                </p>
            </div>
            <div>
                <span class="relative z-0 inline-flex rtl:flex-row-reverse gap-1">
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 text-[10px] leading-none text-gray-400 border border-black/50 bg-white"
                                aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                            class="inline-flex items-center justify-center w-8 h-8 text-[10px] leading-none text-gray-800 border border-black/50 bg-white hover:bg-black/5 transition"
                            aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span
                                    class="inline-flex items-center px-3 py-1.5 text-[10px] leading-none text-gray-400 border border-black/50 bg-white min-h-[30px]">{{ $element }}</span>
                            </span>
                        @endif
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span
                                            class="inline-flex items-center justify-center min-w-8 h-8 px-2 text-[10px] leading-none text-white bg-black border border-black/50">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}"
                                        class="inline-flex items-center justify-center min-w-8 h-8 px-2 text-[10px] leading-none text-gray-800 border border-black/50 bg-white hover:bg-black/5 transition"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                            class="inline-flex items-center justify-center w-8 h-8 text-[10px] leading-none text-gray-800 border border-black/50 bg-white hover:bg-black/5 transition"
                            aria-label="{{ __('pagination.next') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 text-[10px] leading-none text-gray-400 border border-black/50 bg-white"
                                aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif