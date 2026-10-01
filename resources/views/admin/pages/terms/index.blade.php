@extends('admin.layouts.app')

@section('title', 'Terimler')

@section('content')
<div class="flex items-center justify-between mb-4">
	<h1 class="text-lg font-semibold">Terimler</h1>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
	<div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black">
		<h2 class="font-semibold mb-3">Yeni Terim</h2>
		<form id="term-create" class="space-y-3">
			@csrf
			<div>
				<label class="text-sm">Nitelik</label>
				<select name="attribute_id" class="js-select w-full mt-1">
					@foreach($attributes as $attr)
						<option value="{{ $attr->id }}">{{ $attr->name }}</option>
					@endforeach
				</select>
			</div>
			<div>
				<label class="text-sm">Başlık</label>
				<input type="text" name="name" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
			</div>
			<div>
				<label class="text-sm">Değer</label>
				<input type="text" name="value" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
			</div>
			<div class="flex items-center justify-end">
				<button class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Ekle</button>
			</div>
		</form>
	</div>
	<div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-black">
		<table class="min-w-full text-sm" data-datatable>
			<thead class="bg-gray-50 dark:bg-gray-900">
				<tr>
					<th class="text-left px-3 py-2">ID</th>
					<th class="text-left px-3 py-2">Nitelik</th>
					<th class="text-left px-3 py-2">Başlık</th>
					<th class="text-left px-3 py-2">Değer</th>
					<th class="text-right px-3 py-2">İşlemler</th>
				</tr>
			</thead>
			<tbody>
				@foreach($terms as $term)
					<tr class="border-t border-gray-100 dark:border-gray-900">
						<td class="px-3 py-2">{{ $term->id }}</td>
						<td class="px-3 py-2">{{ $term->attribute?->name }}</td>
						<td class="px-3 py-2">{{ $term->name }}</td>
						<td class="px-3 py-2">{{ $term->value }}</td>
						<td class="px-3 py-2 text-right">
							<button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800" data-edit='@json($term)'>Düzenle</button>
							<button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800" data-delete data-url="{{ route('admin.terms.destroy', $term->id) }}">Sil</button>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>

<template id="term-edit-template">
	<div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
		<div class="w-full max-w-xl bg-white dark:bg-black rounded-lg border border-gray-200 dark:border-gray-800 p-4">
			<h3 class="font-semibold mb-3">Terim Düzenle</h3>
			<form class="space-y-3" data-edit-form>
				@csrf
				@method('PUT')
				<input type="hidden" name="id">
				<div>
					<label class="text-sm">Nitelik</label>
					<select name="attribute_id" class="js-select w-full mt-1">
						@foreach($attributes as $attr)
							<option value="{{ $attr->id }}">{{ $attr->name }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label class="text-sm">Başlık</label>
					<input type="text" name="name" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
				</div>
				<div>
					<label class="text-sm">Değer</label>
					<input type="text" name="value" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
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
$('#term-create').on('submit', function(e){
	e.preventDefault();
	$.ajax({
		url: "{{ route('admin.terms.store') }}",
		method: "POST",
		data: $(this).serialize(),
		success: function(res){
			showSuccess(res?.msg);
			setTimeout(function(){ location.reload(); }, 600);
		},
		error: function(xhr){
			showError(xhr.responseJSON?.msg || 'Hata');
		}
	});
});

$(document).on('click', '[data-edit]', function(){
	const data = $(this).data('edit');
	const tpl = $($('#term-edit-template').html());
	const form = tpl.find('[data-edit-form]');
	form.attr('action', "{{ url('/admin/terms') }}/" + data.id);
	form.find('[name=id]').val(data.id);
	form.find('[name=attribute_id]').val(data.attribute_id || '');
	form.find('[name=name]').val(data.name || '');
	form.find('[name=value]').val(data.value || '');
	$('body').append(tpl);
});

$(document).on('click', '[data-close]', function(){
	$(this).closest('.fixed.inset-0').remove();
});

$(document).on('submit', '[data-edit-form]', function(e){
	e.preventDefault();
	const id = $(this).find('[name=id]').val();
	$.ajax({
		url: "{{ url('/admin/terms') }}/" + id,
		method: "POST",
		data: $(this).serialize(),
		success: function(res){
			showSuccess(res?.msg);
			setTimeout(function(){ location.reload(); }, 600);
		},
		error: function(xhr){
			showError(xhr.responseJSON?.msg || 'Hata');
		}
	});
});
</script>
@endpush
@endsection


