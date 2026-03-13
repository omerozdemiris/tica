@php
    /** @var \App\Models\CheckoutSession $checkout */
@endphp

@extends($theme->thene . '.layouts.app')

@section('content')
    <div class="container mx-auto py-8">
        <div class="bg-white shadow rounded p-6">
            <h1 class="text-xl font-semibold mb-4">Ödeme</h1>

            @if(!empty($checkoutFormContent))
                {!! $checkoutFormContent !!}
            @else
                <p class="text-red-600">Ödeme formu yüklenemedi. Lütfen daha sonra tekrar deneyin.</p>
            @endif
        </div>
    </div>
@endsection

