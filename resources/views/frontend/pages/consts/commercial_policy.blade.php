@extends('frontend.layouts.app')
@section('title', 'Ticari Elektronik İleti Aydınlatma')
@section('breadcrumb_title', 'Ticari Elektronik İleti Aydınlatma')
@section('content')
    @include('frontend.parts.breadcrumb')
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div >
                <div class="space-y-4">
                    <h2 class="text-2xl font-bold">Ticari Elektronik İleti Aydınlatma</h2>
                    <div class="text-gray-600">
                        {!! $store->commercial_policy ?? '' !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
