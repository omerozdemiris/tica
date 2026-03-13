@extends('admin.layouts.app')

@section('title', $title ?? 'Müşteri Siparişleri')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.customers.show', $customer->id) }}"
            class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
            <i class="ri-arrow-left-line"></i>
            <span>Müşteri Detayına Dön</span>
        </a>
    </div>

    @include('admin.pages.orders.partials.table')
@endsection

