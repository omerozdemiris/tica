@extends('admin.layouts.app')

@section('title', 'Ürün Excel Toplu Güncelleme')

@section('content')
    <div x-data="bulkOperations" class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold">Ürün Excel Toplu Güncelleme</h1>
                <p class="text-sm text-gray-500">Basit ve nitelikli tüm ürünlerinizi Excel ile dışa aktarın, düzenleyip
                    tekrar
                    içe aktarın.</p>
            </div>
            <a href="{{ route('admin.products.index') }}"
                class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 inline-flex items-center gap-2">
                <i class="ri-arrow-left-line"></i>
                <span>Ürün listesine dön</span>
            </a>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded border border-green-200 bg-green-50 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="px-4 py-3 rounded border border-red-200 bg-red-50 text-sm text-red-800">
                {{ $errors->first('msg') ?? $errors->first() }}
            </div>
        @endif

        <div
            class="rounded-xl border border-blue-100 dark:border-blue-900 bg-blue-50/80 dark:bg-blue-900/40 p-4 flex gap-4">
            <div
                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-600 text-white shadow-sm">
                <i class="ri-information-line text-lg"></i>
            </div>
            <div class="space-y-2 text-sm text-blue-900 dark:text-blue-100">
                <h2 class="font-semibold">Kullanım rehberi</h2>
                <ul class="list-disc list-inside space-y-1 text-xs md:text-sm">
                    <li>Önce <strong>Excel Şablonu İndir</strong> butonu ile güncel şablonu indirin.</li>
                    <li><strong>Başlıkları ve sütun sırasını değiştirmeyin</strong>; yalnızca hücre değerlerini güncelleyin.
                    </li>
                    <li><strong>Ürün ID</strong> sütununu değiştirmeyin; eşleşme bu alanla yapılır.</li>
                    <li><strong>Varyant ve Term ID</strong> sütununu değiştirmeyin; nitelik eşleşme bu alanla yapılır.</li>
                    <li>Fiyat/Stok alanlarında <strong>negatif değer girmeyin</strong>; hatalı satırlar uygulanmaz ve uyarı
                        düşer.</li>
                    <li>Dosya boyutu en fazla <strong>50MB</strong>; büyük dosyalar için parça parça yükleyin.</li>
                    <li>Şablonda ürün satırı ekleme/silme yapmayın; satır sayısı değişirse işlem iptal edilir.</li>
                    <li>Varyantlı ürünlerde şablonda “Varyant Adı/Değeri/Stoğu/Vergi Davranışı/Vergi Oranı” sütunları
                        bilgisel amaçlıdır; şu an sadece temel ürün alanları (ad, fiyat, stok, stok tipi, aktif)
                        güncellenir.</li>
                </ul>
            </div>
        </div>

        <!-- Password Verification Card -->
        <div x-show="!verified"
            class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/40 p-6 flex flex-col items-center justify-center text-center space-y-4">
            <div
                class="w-12 h-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 flex items-center justify-center">
                <i class="ri-lock-password-line text-2xl"></i>
            </div>
            <div>
                <h2 class="text-sm font-semibold">İşlem Onayı Gerekli</h2>
                <p class="text-xs text-gray-500 mt-1">Excel yükleme ve yedekten geri yükleme işlemleri için yönetici
                    şifrenizi girmeniz gerekmektedir.</p>
            </div>
            <div class="w-full max-w-xs space-y-3">
                <input type="password" x-model="password" @keydown.enter="verifyPassword"
                    class="w-full px-3 py-2 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm text-center"
                    placeholder="Yönetici Şifreniz">
                <button @click="verifyPassword" :disabled="verifying"
                    class="w-full px-4 py-2 rounded-md bg-black text-white dark:bg-white dark:text-black text-sm font-medium transition-opacity disabled:opacity-50">
                    <span x-show="!verifying">Şifreyi Doğrula ve Devam Et</span>
                    <span x-show="verifying">Doğrulanıyor...</span>
                </button>
            </div>
        </div>

        <!-- Hidden when not verified -->
        <div x-show="verified" x-cloak class="space-y-6">
            <div
                class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/40 p-4 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-semibold">Şablon İndir</h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Sistem mevcut ürünlerinizden örnek alınmış bir Excel şablonu üretir. Sadece belirtilen sütunları
                        düzenleyin,
                        satır ve sütun sıralamasını değiştirmeyin.
                    </p>
                </div>
                <form action="{{ route('admin.products.bulk-excel.download') }}" method="GET">
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-green-600 text-white dark:bg-green-500 dark:text-black text-sm inline-flex items-center gap-2">
                        <i class="ri-download-line"></i>
                        <span>Excel Şablonu İndir</span>
                    </button>
                </form>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/40 p-4">
                <h2 class="text-sm font-semibold mb-2">Excel Yükle</h2>
                <p class="text-xs text-gray-500 mb-4">
                    Yalnızca sistemden indirilen şablon dosyasını kullanın. Dosya boyutu en fazla 50MB olabilir.
                </p>
                <form action="{{ route('admin.products.bulk-excel.upload') }}" method="POST" enctype="multipart/form-data"
                    x-on:submit="loading = true">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Yedek adı
                                (opsiyonel)</label>
                            <input type="text" name="label" maxlength="100"
                                class="mt-1 w-full px-3 py-2 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm"
                                placeholder="Örn: Kampanya öncesi fiyatlar">
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                        <input type="file" name="file" accept=".xlsx,.xls" required
                            class="w-full md:w-1/2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-gray-100 file:text-gray-700 dark:file:bg-gray-800 dark:file:text-gray-200">
                        <button type="submit"
                            class="px-4 py-2 rounded-md bg-black text-white dark:bg-white dark:text-black text-sm inline-flex items-center gap-2">
                            <i class="ri-upload-line"></i>
                            <span>Excel Yükle ve Güncelle</span>
                        </button>
                    </div>
                </form>
                @if (session('import_errors') && count(session('import_errors')))
                    <div class="mt-4 rounded border border-yellow-200 bg-yellow-50 p-3">
                        <h3 class="text-xs font-semibold text-yellow-800 mb-2">Uyarılar</h3>
                        <ul class="text-xs text-yellow-800 space-y-1 max-h-40 overflow-auto">
                            @foreach (session('import_errors') as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            @if (isset($backups) && $backups->count())
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black/40 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-sm font-semibold">Geçmiş Excel Yedekleri</h2>
                            <p class="text-xs text-gray-500">Yüklenmiş Excel versiyonlarına buradan dönebilirsiniz.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($backups as $backup)
                            <div
                                class="border border-gray-200 dark:border-gray-800 rounded-lg p-3 bg-gray-50 dark:bg-gray-900/40">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5">
                                        <i class="ri-file-excel-2-line text-2xl text-green-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-medium">{{ $backup->label ?? 'İsimsiz yedek' }}</p>
                                            @if ($backup->is_default)
                                                <span
                                                    class="text-[10px] px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">Aktif</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500">{{ $backup->created_at->format('d.m.Y H:i') }}</p>
                                        <p class="text-[11px] text-gray-500 break-words mt-1">{{ $backup->file_name }}</p>
                                    </div>
                                </div>
                                <form action="{{ route('admin.products.bulk-excel.restore', $backup->id) }}" method="POST"
                                    class="mt-3"
                                    onsubmit="return confirm('Seçili yedeğe dönülsün mü? Satır/sütun uyuşmazlığı varsa işlem yapılmaz.');">
                                    @csrf
                                    <button type="submit"
                                        class="w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 text-sm bg-white dark:bg-black hover:bg-gray-50 dark:hover:bg-gray-800 inline-flex items-center justify-center gap-2">
                                        <i class="ri-history-line"></i>
                                        <span>Bu yedeğe dön</span>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div x-show="loading" x-cloak
            class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-900 rounded-xl px-6 py-4 flex items-center gap-3 shadow-lg">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium">İşlem devam ediyor...</p>
                    <p class="text-xs text-gray-500">Lütfen sayfayı kapatmayın, işlem tamamlanana kadar bekleyin.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bulkOperations', () => ({
                verified: false,
                verifying: false,
                password: '',
                loading: false,

                async verifyPassword() {
                    if (!this.password) return;

                    this.verifying = true;
                    try {
                        const response = await fetch(
                            "{{ route('admin.products.bulk-excel.verify-password') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    password: this.password
                                })
                            });

                        const data = await response.json();

                        if (data.success) {
                            this.verified = true;
                            this.password = '';
                        } else {
                            alert(data.msg || 'Hatalı şifre!');
                        }
                    } catch (e) {
                        alert('Bir hata oluştu!');
                    } finally {
                        this.verifying = false;
                    }
                }
            }))
        })
    </script>
@endpush
