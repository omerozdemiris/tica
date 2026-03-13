<div id="dz-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
    <div class="w-full max-w-3xl bg-white dark:bg-black rounded-lg border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">Galeri</h3>
            <button type="button" id="dz-close"
                class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800">Kapat</button>
        </div>

        <div class="mt-4">
            <form action="{{ route('admin.photos.upload') }}"
                class="dropzone dz-clickable rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-black p-8 flex items-center justify-center text-center"
                id="dz-form">
                @csrf
                <input type="hidden" name="type" id="dz-type">
                <input type="hidden" name="parent" id="dz-parent">
                <div class="dz-message" data-dz-message>
                    <div class="flex flex-col items-center gap-2 text-gray-500">
                        <i class="ri-cloud-upload-line text-4xl"></i>
                        <div class="text-sm">Görselleri sürükleyip bırakın ya da tıklayarak seçin</div>
                        <div class="text-xs text-gray-400">Desteklenen: JPG, PNG, WEBP. Maks 2MB</div>
                    </div>
                </div>
            </form>
        </div>

        <div class="mt-4">
            <div id="dz-list" class="grid grid-cols-2 md:grid-cols-4 gap-3"></div>
        </div>
    </div>
</div>

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.css">
    <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"></script>
@endpush

@push('scripts')
    <script>
        Dropzone.autoDiscover = false;
        let dzInstance = null;

        function renderItem(item) {
            const url = `/upload/${item.dirFolder}/${item.name}`;
            return `
		<div class="rounded border border-gray-200 dark:border-gray-800 overflow-hidden">
			<div class="bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-2">
				<img src="${url}" alt="" class="h-28 w-full object-cover rounded-md">
			</div>
			<div class="p-2 flex items-center justify-between gap-2">
				<input type="number" min="0" value="${item.queue}" data-id="${item.id}" class="w-16 px-2 py-1 text-sm rounded border border-gray-200 dark:border-gray-800" data-queue>
				<button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800" data-remove data-id="${item.id}">
					<i class="ri-delete-bin-line"></i>
				</button>
			</div>
		</div>
	`;
        }

        function refreshList() {
            const type = $('#dz-type').val(),
                parent = $('#dz-parent').val();
            $.getJSON(
                `{{ route('admin.photos.list') }}?type=${encodeURIComponent(type)}&parent=${encodeURIComponent(parent)}`,
                function(resp) {
                    const list = resp?.data?.items || [];
                    const html = list.map(renderItem).join('');
                    $('#dz-list').html(html);
                });
        }
        $(document).on('click', '[data-gallery]', function() {
            const kind = $(this).data('kind'),
                parent = $(this).data('id');
            const type = (window.AppConfig && window.AppConfig.files && window.AppConfig.files.types && window
                .AppConfig.files.types[kind]) ? window.AppConfig.files.types[kind] : kind;
            $('#dz-type').val(type);
            $('#dz-parent').val(parent);
            if (!dzInstance) {
                dzInstance = new Dropzone('#dz-form', {
                    paramName: 'file',
                    maxFilesize: 10,
                    acceptedFiles: 'image/*',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
                    },
                    init: function() {}
                });
                dzInstance.on('success', function() {
                    refreshList();
                });
            }
            $('#dz-modal').removeClass('hidden').addClass('flex');
            refreshList();
        });
        $('#dz-close').on('click', function() {
            $('#dz-modal').removeClass('flex').addClass('hidden');
        });
        $(document).on('change', '[data-queue]', function() {
            const id = $(this).data('id');
            $.post(`{{ route('admin.photos.order') }}`, {
                    id,
                    queue: this.value,
                    _token: $('meta[name=\"csrf-token\"]').attr('content')
                })
                .done(function(res) {
                    showSuccess(res?.msg);
                })
                .fail(function(xhr) {
                    showError(xhr.responseJSON?.msg || 'Hata');
                });
        });
        $(document).on('click', '[data-remove]', function() {
            const id = $(this).data('id');
            $.ajax({
                    url: `{{ route('admin.photos.delete') }}`,
                    method: 'DELETE',
                    data: {
                        id,
                        _token: $('meta[name=\"csrf-token\"]').attr('content')
                    }
                })
                .done(function(res) {
                    showSuccess(res?.msg);
                    refreshList();
                })
                .fail(function(xhr) {
                    showError(xhr.responseJSON?.msg || 'Hata');
                });
        });
    </script>
@endpush
