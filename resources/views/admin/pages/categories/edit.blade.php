@extends('admin.layouts.app')

@section('title', 'Kategori Düzenle')

@section('content')
    <h1 class="text-lg font-semibold mb-4">Kategori Düzenle</h1>
    <form id="category-edit" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="text-sm">Ad</label>
            <input type="text" name="name" value="{{ $category->name }}"
                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
        </div>
        <div>
            <label class="text-sm">Üst Kategori</label>
            <select name="category_id" class="js-select w-full mt-1">
                <option value="">—</option>
                @foreach ($parents ?? [] as $parent)
                    <option value="{{ $parent->id }}" @if ($category->category_id === $parent->id) selected @endif>
                        {{ $parent->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm">Açıklama</label>
            <textarea name="description" rows="4"
                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">{{ $category->description }}</textarea>
        </div>
        <div>
            <label class="text-sm">Meta Başlığı</label>
            <input type="text" name="meta_title" value="{{ $category->meta_title }}"
                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
        </div>
        <div>
            <label class="text-sm">Meta Açıklaması</label>
            <input name="meta_description" rows="4"
                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"
                value="{{ $category->meta_description }}">
        </div>
        @if ($category->photo)
            <div class="flex items-center gap-4 p-4 bg-white border border-gray-200 dark:border-gray-800 rounded-md">
                <img src="{{ asset($category->photo) }}" class="w-20 h-20 object-cover rounded shadow-sm">
                <div>
                    <p class="text-xs text-gray-500 mb-2">Mevcut Fotoğraf</p>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remove_photo" value="1" class="toggle">
                        <span class="text-xs text-red-600 font-bold uppercase">Fotoğrafı Kaldır</span>
                    </label>
                </div>
            </div>
        @endif
        <div>
            <label class="text-sm">Yeni Fotoğraf Yükle (Opsiyonel)</label>
            <label
                class="mt-1 flex bg-white items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900">
                <div class="flex items-center gap-3">
                    <i class="ri-image-line text-xl text-gray-500"></i>
                    <span class="text-sm text-gray-600 dark:text-gray-300" data-file-label
                        data-default-text="Dosya seçin...">Dosya seçin...</span>
                </div>
                <span class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-700 rounded-md">Gözat</span>
                <input type="file" name="photo_file" accept="image/*" class="sr-only" data-file-input>
            </label>
        </div>
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.categories.index') }}"
                class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800">İptal</a>
            <button type="submit"
                class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Güncelle</button>
        </div>
    </form>
    @push('scripts')
        <script>
            $(document).on('change', 'input[data-file-input]', function() {
                const file = this.files[0];
                const $label = $(this).parent().find('span[data-file-label]');

                if (file) {
                    $label.text(file.name);
                } else {
                    $label.text($label.data('default-text'));
                }
            });

            $('#category-edit').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.categories.update', $category->id) }}",
                    method: "POST",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        showSuccess(res?.msg);
                        setTimeout(function() {
                            window.location = "{{ route('admin.categories.index') }}";
                        }, 600);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.msg || 'Hata';
                        showError(msg);
                    }
                });
            });
        </script>
    @endpush
@endsection
