@extends('admin.layouts.app')

@section('title', 'Tema Seçimi')

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
@endpush

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Tema Seçimi</h1>
        <p class="text-gray-500">Mağazanız için bir tema seçin. Temaları incelemek için üzerlerine tıklayabilirsiniz.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($templates as $template)
            <div
                class="group relative bg-white dark:bg-gray-900 rounded-2xl border {{ $theme && $theme->thene === $template->path ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-800' }} overflow-hidden transition-all hover:shadow-xl">
                {{-- Template Preview Image --}}
                <div class="aspect-[16/10] bg-gray-100 dark:bg-gray-800 relative overflow-hidden">
                    @if (!empty($template->images))
                        <img src="{{ asset('assets/img/themes/' . $template->path . '/' . $template->images[0]) }}"
                            alt="{{ $template->title }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 cursor-pointer"
                            data-fancybox="gallery-{{ $template->id }}" data-caption="{{ $template->title }} - Önizleme">

                        {{-- Hidden images for Fancybox gallery --}}
                        @foreach (array_slice($template->images, 1) as $image)
                            <a href="{{ asset('assets/img/themes/' . $template->path . '/' . $image) }}"
                                data-fancybox="gallery-{{ $template->id }}"
                                data-caption="{{ $template->title }} - Görsel {{ $loop->iteration + 1 }}"
                                class="hidden"></a>
                        @endforeach
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <i class="ri-image-line text-4xl"></i>
                        </div>
                    @endif

                    {{-- Active Badge --}}
                    @if ($theme && $theme->thene === $template->path)
                        <div
                            class="absolute top-3 right-3 bg-blue-500 text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider shadow-lg">
                            Aktif Tema
                        </div>
                    @endif
                </div>

                {{-- Template Info --}}
                <div class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">{{ $template->title }}</h3>
                            <p class="text-xs text-gray-500 mt-1 uppercase tracking-tighter">{{ $template->path }}</p>
                        </div>
                        @if (!empty($template->images))
                            <span
                                class="text-[10px] bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-lg">
                                {{ count($template->images) }} Görsel
                            </span>
                        @endif
                    </div>

                    <button type="button" data-path="{{ $template->path }}"
                        class="select-template-btn w-full py-2.5 rounded-xl text-sm font-bold transition-all {{ $theme && $theme->thene === $template->path ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed' : 'bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50' }}"
                        {{ $theme && $theme->thene === $template->path ? 'disabled' : '' }}>
                        {{ $theme && $theme->thene === $template->path ? 'Şu Anki Temanız' : 'Bu Temayı Kullan' }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    @if ($templates->isEmpty())
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-8 text-center">
            <i class="ri-information-line text-4xl text-blue-500 mb-3 block"></i>
            <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100">Henüz Tema Bulunmuyor</h3>
            <p class="text-blue-700 dark:text-blue-300 max-w-md mx-auto mt-2">
                Sisteme kayıtlı herhangi bir tema şablonu bulunamadı. Lütfen <code>public/img/themes/</code> klasörüne tema
                klasörlerinizi ve içlerine önizleme görsellerini ekleyin.
            </p>
        </div>
    @endif
    <div id="themeLoadingModal"
        class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/60 backdrop-blur-md">
        <div
            class="bg-white dark:bg-gray-900 rounded-3xl p-10 max-w-md w-full mx-4 shadow-[0_0_50px_-12px_rgba(0,0,0,0.5)] transform transition-all">
            <div class="flex flex-col items-center">
                <div class="w-20 h-20 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-6">
                    <i class="ri-palette-line text-4xl text-blue-600 animate-pulse"></i>
                </div>

                <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-2 text-center uppercase tracking-tighter">
                    Temanız uygulanıyor</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm text-center mb-8 font-medium">Lütfen bekleyin, mağaza
                    ayarları yeni temaya göre yapılandırılıyor...</p>

                <div class="w-full bg-gray-100 dark:bg-gray-800 h-3 rounded-full overflow-hidden shadow-inner">
                    <div id="themeProgressBar"
                        class="bg-gradient-to-r from-blue-600 to-blue-400 h-full w-0 transition-all duration-100 ease-linear rounded-full">
                    </div>
                </div>

                <div class="flex justify-between w-full mt-4">
                    <span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">İşlem Durumu</span>
                    <span class="text-[10px] text-blue-600 uppercase tracking-widest font-black"
                        id="themeProgressText">0%</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script>
        Fancybox.bind("[data-fancybox]", {
            // Fancybox options
        });

        $(document).on('click', '.select-template-btn', function() {
            const $btn = $(this);
            const path = $btn.data('path');

            showConfirmModal('Bu temayı aktif etmek istediğinize emin misiniz? Mağaza görünümü değişecektir.',
                function() {
                    const $modal = $('#themeLoadingModal');
                    const $progressBar = $('#themeProgressBar');
                    const $progressText = $('#themeProgressText');

                    $modal.removeClass('hidden').addClass('flex');

                    let progress = 0;
                    const duration = 5000; // 5 saniye
                    const intervalTime = 50; // Her 50ms'de güncelle
                    const increment = (intervalTime / duration) * 100;

                    const interval = setInterval(() => {
                        progress += increment;
                        if (progress >= 95) { // 95'te durup AJAX sonucunu beklesin
                            progress = 95;
                            clearInterval(interval);
                        }
                        $progressBar.css('width', progress + '%');
                        $progressText.text(Math.round(progress) + '%');
                    }, intervalTime);

                    $.ajax({
                        url: '{{ route('admin.theme.updateSelection') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            path: path
                        },
                        success: function(response) {
                            if (response.code === 1) {
                                // 5 saniye dolana kadar veya en azından biraz daha bekle
                                setTimeout(() => {
                                    clearInterval(interval);
                                    $progressBar.css('width', '100%');
                                    $progressText.text('100%');

                                    setTimeout(() => {
                                        $modal.fadeOut(300, function() {
                                            $(this).addClass('hidden')
                                                .removeClass('flex').show();
                                            showSuccess(response.msg);
                                            setTimeout(() => {
                                                window.location
                                                    .reload();
                                            }, 1000);
                                        });
                                    }, 500);
                                }, 1000); // 95'ten sonra 1 sn daha bekle ki 100 görünüp bitsin
                            } else {
                                $modal.addClass('hidden').removeClass('flex');
                                clearInterval(interval);
                                showError(response.msg || 'Bir hata oluştu.');
                            }
                        },
                        error: function(xhr) {
                            $modal.addClass('hidden').removeClass('flex');
                            clearInterval(interval);
                            const msg = xhr.responseJSON?.msg || 'Bir hata oluştu.';
                            showError(msg);
                        }
                    });
                });
        });
    </script>
@endpush
