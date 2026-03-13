@extends('admin.layouts.app')

@section('title', 'Menü Yönetimi')

@section('content')
<div class="flex items-center justify-between mb-4">
	<h1 class="text-lg font-semibold">Menü Yönetimi</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
	<div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4">
		<h2 class="font-semibold mb-3">Yeni Menü</h2>
		<form id="menu-create" class="space-y-3">
			@csrf
			<div>
				<label class="text-sm">Ad</label>
				<input type="text" name="name" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
			</div>
			<div>
				<label class="text-sm">Kategori</label>
				<select name="category_id" class="js-select w-full mt-1">
					<option value="">—</option>
					@foreach($categories as $cat)
						<option value="{{ $cat->id }}">{{ $cat->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
				<label class="flex items-center gap-3">
					<input type="checkbox" name="show_in_menu" value="1" class="toggle">
					<span>Menüde Göster</span>
				</label>
				<label class="flex items-center gap-3">
					<input type="checkbox" name="show_in_footer" value="1" class="toggle">
					<span>Footer'da Göster</span>
				</label>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
				<label class="flex items-center gap-3">
					<input type="checkbox" name="is_active" value="1" class="toggle" checked>
					<span>Aktif</span>
				</label>
				<div>
					<label class="text-sm">Sıra</label>
					<input type="number" min="0" name="sort_order" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black" value="0">
				</div>
			</div>
			<div class="flex items-center justify-end">
				<button class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Ekle</button>
			</div>
		</form>
	</div>

	<div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
		<table class="min-w-full text-sm" data-datatable>
			<thead class="bg-gray-50 dark:bg-gray-900">
				<tr>
					<th class="text-left px-3 py-2">Sıra</th>
					<th class="text-left px-3 py-2">Ad</th>
					<th class="text-left px-3 py-2">Kategori</th>
					<th class="text-left px-3 py-2">Gösterimler</th>
					<th class="text-left px-3 py-2">Aktif</th>
					<th class="text-right px-3 py-2">İşlemler</th>
				</tr>
			</thead>
			<tbody>
				@foreach($menus as $menu)
					<tr class="border-t border-gray-100 dark:border-gray-900">
						<td class="px-3 py-2">{{ $menu->sort_order }}</td>
						<td class="px-3 py-2">{{ $menu->name }}</td>
						<td class="px-3 py-2">{{ $menu->category?->name ?? '-' }}</td>
						<td class="px-3 py-2">
							@if($menu->show_in_menu) <span class="px-2 py-0.5 border rounded text-xs">Menü</span> @endif
							@if($menu->show_in_footer) <span class="px-2 py-0.5 border rounded text-xs">Footer</span> @endif
						</td>
						<td class="px-3 py-2">{{ $menu->is_active ? 'Evet' : 'Hayır' }}</td>
						<td class="px-3 py-2 text-right">
							<button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800" data-edit='@json($menu)'>Düzenle</button>
							<button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800" data-delete data-url="{{ route('admin.menu.destroy', $menu->id) }}">Sil</button>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>

<template id="menu-edit-template">
	<div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
		<div class="w-full max-w-xl bg-white dark:bg-black rounded-lg border border-gray-200 dark:border-gray-800 p-4">
			<h3 class="font-semibold mb-3">Menü Düzenle</h3>
			<form class="space-y-3" data-edit-form>
				@csrf
				@method('PUT')
				<input type="hidden" name="id">
				<div>
					<label class="text-sm">Ad</label>
					<input type="text" name="name" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
				</div>
				<div>
					<label class="text-sm">Kategori</label>
					<select name="category_id" class="js-select w-full mt-1">
						<option value="">—</option>
						@foreach($categories as $cat)
							<option value="{{ $cat->id }}">{{ $cat->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
					<label class="flex items-center gap-3">
						<input type="checkbox" name="show_in_menu" value="1" class="toggle">
						<span>Menüde Göster</span>
					</label>
					<label class="flex items-center gap-3">
						<input type="checkbox" name="show_in_footer" value="1" class="toggle">
						<span>Footer'da Göster</span>
					</label>
				</div>
				<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
					<label class="flex items-center gap-3">
						<input type="checkbox" name="is_active" value="1" class="toggle">
						<span>Aktif</span>
					</label>
					<div>
						<label class="text-sm">Sıra</label>
						<input type="number" min="0" name="sort_order" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
					</div>
				</div>
				<div class="flex items-center justify-end gap-2">
					<button type="button" data-close class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800">Kapat</button>
					<button class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Güncelle</button>
				</div>
			</form>
		</div>
	</div>
</template>

@push('scripts')
<script>
// Create
$('#menu-create').on('submit', function(e){
	e.preventDefault();
	$.ajax({
		url: "{{ route('admin.menu.store') }}",
		method: "POST",
		data: $(this).serialize(),
		success: function(res){
			showSuccess(res?.msg);
			setTimeout(function(){ location.reload(); }, 600);
		},
		error: function(xhr){
			const msg = xhr.responseJSON?.msg || 'Hata';
			showError(msg);
		}
	});
});

// Edit modal
$(document).on('click', '[data-edit]', function(){
	const data = $(this).data('edit');
	const tpl = $($('#menu-edit-template').html());
	const form = tpl.find('[data-edit-form]');
	form.attr('action', "{{ url('/admin/menu') }}/" + data.id);
	form.find('[name=id]').val(data.id);
	form.find('[name=name]').val(data.name || '');
	form.find('[name=category_id]').val(data.category_id || '');
	form.find('[name=show_in_menu]').prop('checked', !!data.show_in_menu);
	form.find('[name=show_in_footer]').prop('checked', !!data.show_in_footer);
	form.find('[name=is_active]').prop('checked', !!data.is_active);
	form.find('[name=sort_order]').val(data.sort_order || 0);
	$('body').append(tpl);
});

// Close modal
$(document).on('click', '[data-close]', function(){
	$(this).closest('.fixed.inset-0').remove();
});

// Update
$(document).on('submit', '[data-edit-form]', function(e){
	e.preventDefault();
	const id = $(this).find('[name=id]').val();
	const raw = $(this).serializeArray();
	['show_in_menu','show_in_footer','is_active'].forEach(function(name){
		if (!raw.find(function(x){ return x.name === name })) raw.push({ name, value: 0 });
	});
	$.ajax({
		url: "{{ url('/admin/menu') }}/" + id,
		method: "POST",
		data: $.param(raw),
		success: function(res){
			showSuccess(res?.msg);
			setTimeout(function(){ location.reload(); }, 600);
		},
		error: function(xhr){
			const msg = xhr.responseJSON?.msg || 'Hata';
			showError(msg);
		}
	});
});
</script>
@endpush
@endsection


