@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $hasLink = !empty($announcement?->link);
    $isExternal = $hasLink && Str::startsWith(Str::lower($announcement->link), 'https://');
    $target = $isExternal ? '_blank' : '_self';
    $href = '#';

    if ($hasLink) {
        if ($isExternal) {
            $href = $announcement->link;
        } else {
            $href = Route::has($announcement->link) ? route($announcement->link) : url($announcement->link);
        }
    }
@endphp

@if (!empty($announcement))
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-announcement-popup>
        <div class="absolute inset-0 bg-black/60" data-popup-close></div>
        <div
            class="relative w-full max-w-md {{ $theme->color ? 'bg-' . $theme->color : 'bg-white' }} dark:{{ $theme->color ? 'bg-' . $theme->color : 'bg-gray-900' }} rounded-2xl shadow-2xl overflow-hidden animate-pop">
            <button type="button"
                class="absolute top-3 right-3 {{ $theme->color ? 'text-white bg-' . $theme->color . 'rounded-full w-10 h-10 flex items-center justify-center' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }} transition"
                data-popup-close>
                <i
                    class="ri-close-line text-xl {{ $theme->color ? 'text-white bg-' . $theme->color . '/80 rounded-full w-10 h-10 flex items-center justify-center' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }} transition"></i>
            </button>
            @if (!empty($announcement->image))
                @php
                    $imageContent = $announcement->image;
                @endphp
                @if ($hasLink)
                    <a href="{{ $href }}" target="{{ $target }}"
                        @if ($isExternal) rel="noopener noreferrer" @endif>
                        <img src="{{ $announcement->image }}" alt="{{ $announcement->title }}"
                            class="w-full h-auto max-h-[30rem] object-cover">
                    </a>
                @else
                    <img src="{{ $announcement->image }}" alt="{{ $announcement->title }}"
                        class="w-full h-auto max-h-[30rem] object-cover">
                @endif
            @endif
            <div class="p-6 space-y-4 text-center">
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">
                        {{ $announcement->title }}
                    </h3>
                </div>
                @if ($hasLink)
                    <a href="{{ $href }}" target="{{ $target }}"
                        @if ($isExternal) rel="noopener noreferrer" @endif
                        class="inline-flex items-center justify-center px-5 py-3 rounded-full text-sm font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-white' }} bg-white transition">
                        İncele
                        <i class="ri-arrow-right-up-line ml-2 text-base"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
