<div id="notification-modal"
    class="fixed inset-0 bg-black/40 dark:bg-black/70 flex items-center justify-center px-4 py-6 hidden z-50">
    <div class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] flex flex-col">
        <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:white">Web Bildirimi Gönder</h3>
            <button type="button" data-notification-modal-close
                class="text-gray-500 hover:text-gray-900 dark:hover:white">
                <i class="ri-close-line text-2xl"></i>
            </button>
        </div>
        <form id="notification-form" class="p-6 space-y-4 overflow-y-auto">
            @csrf
            <div>
                <label class="text-sm font-medium">Bildirim Başlığı (Zorunlu)</label>
                <input type="text" name="title" required
                    class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="text-sm font-medium">İçerik Türü</label>
                    <select name="context_type" id="context_type"
                        class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                        <option value="product">Ürün</option>
                        <option value="category">Kategori</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium">İçerik Seçin (Arama ile Ekle)</label>
                <div id="context-selector"
                    class="mt-1 p-4 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-lg min-h-[100px] flex flex-wrap gap-2">
                    <!-- Selected items -->
                </div>
                <div class="mt-2 flex gap-2">
                    <input type="text" id="context-search" placeholder="Ürün veya kategori ara..."
                        class="flex-1 px-3 py-1 text-sm rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
                </div>
                <div id="search-results"
                    class="mt-1 hidden border border-gray-200 dark:border-gray-800 rounded-md bg-white dark:bg-black max-h-40 overflow-y-auto z-10">
                    <!-- Results -->
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <button type="button" data-notification-modal-close
                    class="px-4 py-2 rounded-md border border-gray-200 dark:border-gray-700 text-sm">Vazgeç</button>
                <button type="submit"
                    class="px-4 py-2 rounded-md text-sm font-semibold bg-blue-600 text-white">Gönder</button>
            </div>
        </form>
    </div>
</div>
