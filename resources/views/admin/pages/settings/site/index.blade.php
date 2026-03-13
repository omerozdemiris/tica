@extends('admin.layouts.app')

@section('title', 'Site Ayarları')

@section('content')
    <div class="flex flex-col md:flex-row gap-8 relative items-start">
        <div class="w-full md:w-64 md:sticky md:top-24 space-y-1">
            <h1 class="text-xl font-bold mb-6 px-3">Site Ayarları</h1>
            <nav id="settings-nav" class="flex flex-col gap-1">
                <a href="#section-images"
                    class="nav-link px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 border border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-3 active-nav"
                    data-section="section-images">
                    <i class="ri-image-line text-lg"></i>
                    Görseller
                </a>
                <a href="#section-general"
                    class="nav-link px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 border border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-3"
                    data-section="section-general">
                    <i class="ri-information-line text-lg"></i>
                    Genel Bilgiler
                </a>
                <a href="#section-social"
                    class="nav-link px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 border border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-3"
                    data-section="section-social">
                    <i class="ri-share-line text-lg"></i>
                    Sosyal Medya
                </a>
                <a href="#section-map"
                    class="nav-link px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 border border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-3"
                    data-section="section-map">
                    <i class="ri-map-2-line text-lg"></i>
                    Google Harita
                </a>
            </nav>

            <div class="pt-20 px-3">
                <button form="site-settings-form" type="submit"
                    class="w-full px-4 py-2.5 rounded-lg text-sm font-semibold bg-black text-white dark:bg-white dark:text-black hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                    <i class="ri-save-line"></i>
                    Değişiklikleri Kaydet
                </button>
            </div>
        </div>
        <div class="flex-1 w-full max-w-5xl">
            <form id="site-settings-form" class="space-y-12 pb-24">
                @csrf
                @method('PUT')
                <section id="section-images" class="scroll-mt-24">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 p-6">
                        <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                            <i class="ri-image-line"></i> Görseller
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Logo</label>
                                <label
                                    class="mt-2 flex items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <i class="ri-upload-line text-xl text-gray-400"></i>
                                        <span class="text-sm text-gray-600 dark:text-gray-300" data-file-label
                                            data-default-text="Logo seçin...">Logo seçin...</span>
                                    </div>
                                    <input type="file" name="logo_file" accept="image/*" class="sr-only" data-file-input>
                                </label>
                                @if ($settings->logo)
                                    <div class="mt-3 p-2 rounded-lg bg-gray-50 dark:bg-gray-900 inline-block">
                                        <img src="{{ $settings->logo }}" alt="logo" class="h-10 w-auto object-contain">
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Beyaz Logo</label>
                                <label
                                    class="mt-2 flex items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <i class="ri-upload-line text-xl text-gray-400"></i>
                                        <span class="text-sm text-gray-600 dark:text-gray-300" data-file-label
                                            data-default-text="Logo seçin...">Logo seçin...</span>
                                    </div>
                                    <input type="file" name="white_logo_file" accept="image/*" class="sr-only"
                                        data-file-input>
                                </label>
                                @if ($settings->white_logo)
                                    <div class="mt-3 p-2 rounded-lg bg-black inline-block">
                                        <img src="{{ $settings->white_logo }}" alt="white logo"
                                            class="h-10 w-auto object-contain">
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Favicon</label>
                                <label
                                    class="mt-2 flex items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <i class="ri-upload-line text-xl text-gray-400"></i>
                                        <span class="text-sm text-gray-600 dark:text-gray-300" data-file-label
                                            data-default-text="Favicon seçin...">Favicon seçin...</span>
                                    </div>
                                    <input type="file" name="favicon_file" accept=".ico,.png" class="sr-only"
                                        data-file-input>
                                </label>
                                @if ($settings->favicon)
                                    <div class="mt-3 p-2 rounded-lg bg-gray-50 dark:bg-gray-900 inline-block">
                                        <img src="{{ $settings->favicon }}" alt="favicon" class="h-8 w-8 object-contain">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
                <section id="section-general" class="scroll-mt-24">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 p-6">
                        <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                            <i class="ri-information-line"></i> Genel Bilgiler
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-sm font-medium">Site Başlığı</label>
                                <input type="text" name="title" value="{{ $settings->title ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-medium">E‑posta</label>
                                <input type="email" name="email" value="{{ $settings->email ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-medium">Sipariş Bildirim E‑postası</label>
                                <input type="email" name="notify_mail" value="{{ $settings->notify_mail ?? '' }}"
                                    placeholder="Sipariş bildirimleri için"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-medium">Telefon</label>
                                <input type="text" name="phone" value="{{ $settings->phone ?? '' }}"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium">Adres</label>
                            <input type="text" name="address" value="{{ $settings->address ?? '' }}"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                        </div>
                    </div>
                </section>
                <section id="section-social" class="scroll-mt-24">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 p-6">
                        <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                            <i class="ri-share-line"></i> Sosyal Medya
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach (['instagram', 'twitter', 'facebook', 'youtube', 'linkedin'] as $social)
                                <div class="space-y-1">
                                    <label class="text-sm font-medium capitalize">{{ $social }}</label>
                                    <div class="relative group">
                                        <i
                                            class="ri-{{ $social === 'twitter' ? 'twitter-x-line' : ($social === 'facebook' ? 'facebook-box-line' : ($social === 'linkedin' ? 'linkedin-box-line' : ($social === 'instagram' ? 'instagram-line' : ($social === 'youtube' ? 'youtube-line' : $social)))) }} absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-black dark:group-focus-within:text-white transition-colors"></i>
                                        <input type="url" name="{{ $social }}"
                                            value="{{ $settings->$social ?? '' }}"
                                            class="pl-12 w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                                    </div>
                                </div>
                            @endforeach
                            <div class="space-y-1">
                                <label class="text-sm font-medium">WhatsApp</label>
                                <div class="relative group">
                                    <i
                                        class="ri-whatsapp-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-black dark:group-focus-within:text-white transition-colors"></i>
                                    <input type="text" name="whatsapp" value="{{ $settings->whatsapp ?? '' }}"
                                        placeholder="https://wa.me/5XXXXXXXXX"
                                        class="pl-12 w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="section-map" class="scroll-mt-24">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/20 p-6">
                        <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                            <i class="ri-map-2-line"></i> Google Harita
                        </h2>
                        <div class="space-y-1">
                            <label class="text-sm font-medium">Google Iframe Kodu</label>
                            <textarea name="google_iframe" rows="10"
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black focus:ring-2 focus:ring-black dark:focus:ring-white transition-all outline-none">{{ $settings->google_iframe ?? '' }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">Harita paylaşım kodunu buraya yapıştırın. Genişlik
                                (width) değerini 100% yapmanız önerilir.</p>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>

    <style>
        .active-nav {
            background-color: #fff;
            color: rgb(0 0 0);
            font-weight: 600;
        }

        .dark .active-nav {
            background-color: rgb(31 41 55);
            color: rgb(255 255 255);
        }

        html {
            scroll-behavior: smooth;
        }
    </style>

    @push('scripts')
        <script>
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.nav-link');

            window.addEventListener('scroll', () => {
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.clientHeight;
                    if (window.pageYOffset >= sectionTop - 150) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.classList.remove('active-nav');
                    if (link.getAttribute('data-section') === current) {
                        link.classList.add('active-nav');
                    }
                });
            });

            $('#site-settings-form').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                const fd = new FormData(form);
                $.ajax({
                    url: "{{ route('admin.site-settings.update') }}",
                    method: "POST",
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        showSuccess(res?.msg);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.msg || 'Hata';
                        showError(msg);
                    }
                });
            });

            $('[data-file-input]').on('change', function() {
                const fileName = this.files[0] ? this.files[0].name : $(this).siblings('[data-file-label]').data(
                    'default-text');
                $(this).closest('label').find('[data-file-label]').text(fileName);
            });
        </script>
    @endpush
@endsection
