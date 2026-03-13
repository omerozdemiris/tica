@if (session('status'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="rounded-2xl border border-green-100 bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('status') }}
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="rounded-2xl border border-red-100 bg-red-50 text-red-700 px-4 py-3 text-sm space-y-1">
            @foreach ($errors->all() as $message)
                <p>{{ $message }}</p>
            @endforeach
        </div>
    </div>
@endif

