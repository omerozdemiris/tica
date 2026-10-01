@extends('frontend.layouts.app')
@section('title', 'Mesafeli Satış Sözleşmesi')
@section('breadcrumb_title', 'Mesafeli Satış Sözleşmesi')
@section('content')
    @include('frontend.parts.breadcrumb')

    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div >
                <div class="space-y-4">
                    <h2 class="text-2xl font-bold">Mesafeli Satış Sözleşmesi</h2>
                    <div class="text-gray-600">
                        {!! $store->distance_selling ?? '' !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
