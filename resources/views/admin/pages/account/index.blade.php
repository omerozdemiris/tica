@extends('admin.layouts.app')

@section('title', 'Hesap Ayarları')

@section('content')
<h1 class="text-lg font-semibold mb-4">Hesap Ayarları</h1>
<form id="account-form" class="space-y-4 max-w-lg">
	@csrf
	@method('PUT')
	<div>
		<label class="text-sm">E‑posta</label>
		<input type="email" name="email" value="{{ $user->email }}" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
	</div>
	<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
		<div>
			<label class="text-sm">Yeni Şifre</label>
			<input type="password" name="password" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
		</div>
		<div>
			<label class="text-sm">Yeni Şifre (Tekrar)</label>
			<input type="password" name="password_confirmation" class="w-full mt-1 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800 bg-white dark:bg-black">
		</div>
	</div>
	<div class="flex items-center justify-end gap-2">
		<button class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Kaydet</button>
	</div>
</form>
@push('scripts')
<script>
$('#account-form').on('submit', function(e){
	e.preventDefault();
	$.ajax({
		url: "{{ route('admin.account.update') }}",
		method: "POST",
		data: $(this).serialize(),
		success: function(res){
			showSuccess(res?.msg);
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


