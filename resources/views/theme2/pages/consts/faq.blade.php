@extends('frontend.layouts.app')
@section('title', 'Sıkça Sorulan Sorular')
@section('breadcrumb_title', 'Sıkça Sorulan Sorular')
@section('content')
    @include('frontend.parts.breadcrumb')

    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div >
                <div class="space-y-4">
                    <h2 class="text-2xl font-bold">Sıkça Sorulan Sorular</h2>
                    <div class="text-gray-600">
                        {!! $store->faq ?? '' !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
