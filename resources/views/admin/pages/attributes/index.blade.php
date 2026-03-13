@extends('admin.layouts.app')

@section('title', 'Nitelikler')

@section('content')
<div class="flex items-center justify-between mb-4">
	<h1 class="text-lg font-semibold">Nitelikler</h1>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
	<div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-black">
		<h2 class="font-semibold mb-3">Yeni Nitelik</h2>
		<form id="attribute-create" class="space-y-3">
			@csrf
			<div>
				<label class="text-sm">Başlık</label>
				<input type="text" name="name" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
			</div>
			
			<div class="flex items-center justify-end">
				<button class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black inline-flex items-center gap-2"><i class="ri-add-line"></i><span>Ekle</span></button>
			</div>
		</form>
	</div>
	<div class="rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-black">
		<table class="min-w-full text-sm" data-datatable>
			<thead class="bg-gray-50 dark:bg-gray-900">
				<tr>
					<th class="text-left px-3 py-2">ID</th>
					<th class="text-left px-3 py-2">Başlık</th>
					<th class="text-left px-3 py-2">Slug</th>
					<th class="text-right px-3 py-2">İşlemler</th>
				</tr>
			</thead>
			<tbody>
				@foreach($attributes as $attribute)
					<tr class="border-t border-gray-100 dark:border-gray-900">
						<td class="px-3 py-2">{{ $attribute->id }}</td>
						<td class="px-3 py-2">{{ $attribute->name }}</td>
						<td class="px-3 py-2">{{ Str::slug($attribute->name) }}</td>
						<td class="px-3 py-2 text-right">
							<button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1" data-edit='@json($attribute)'><i class="ri-pencil-line"></i><span>Düzenle</span></button>
							<button class="px-2 py-1 rounded border border-gray-200 dark:border-gray-800 inline-flex items-center gap-1" data-delete data-url="{{ route('admin.attributes.destroy', $attribute->id) }}"><i class="ri-delete-bin-line"></i><span>Sil</span></button>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>

<template id="attribute-edit-template">
	<div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
		<div class="w-full max-w-xl bg-white dark:bg-black rounded-lg border border-gray-200 dark:border-gray-800 p-4">
			<h3 class="font-semibold mb-3">Nitelik Düzenle</h3>
			<form class="space-y-3" data-edit-form>
				@csrf
				@method('PUT')
				<input type="hidden" name="id">
				<div>
					<label class="text-sm">Başlık</label>
					<input type="text" name="name" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
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
$('#attribute-create').on('submit', function(e){
	e.preventDefault();
	$.ajax({
		url: "{{ route('admin.attributes.store') }}",
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
	const tpl = $($('#attribute-edit-template').html());
	const form = tpl.find('[data-edit-form]');
	form.attr('action', "{{ url('/admin/attributes') }}/" + data.id);
	form.find('[name=id]').val(data.id);
	form.find('[name=name]').val(data.name || '');
	form.find('[name=slug]').val(data.slug || '');
	$('body').append(tpl);
});

$(document).on('click', '[data-close]', function(){
	$(this).closest('.fixed.inset-0').remove();
});

$(document).on('submit', '[data-edit-form]', function(e){
	e.preventDefault();
	const id = $(this).find('[name=id]').val();
	$.ajax({
		url: "{{ url('/admin/attributes') }}/" + id,
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


