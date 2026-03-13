@extends('admin.layouts.app')

@section('title', 'Yeni Kategori')

@section('content')
    <h1 class="text-lg font-semibold mb-4">Yeni Kategori</h1>
    <form id="category-create" class="space-y-4">
        @csrf
        <div>
            <label class="text-sm">Ad</label>
            <input type="text" name="name"
                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
        </div>
        <div>
            <label class="text-sm">Üst Kategori</label>
            <select name="category_id" class="js-select w-full mt-1">
                <option value="">—</option>
                @foreach ($parents ?? [] as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm">Açıklama</label>
            <textarea name="description" rows="4"
                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black"></textarea>
        </div>
        <div>
            <label class="text-sm">Meta Başlığı</label>
            <input type="text" name="meta_title"
                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
        </div>
        <div>
            <label class="text-sm">Meta Açıklaması</label>
            <input name="meta_description" rows="4"
                class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
            <div>
                <label class="text-sm">Kategori Fotoğrafı</label>
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
                    class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Kaydet</button>
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

            $('#category-create').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.categories.store') }}",
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
