@extends('frontend.layouts.app')
@section('title', 'Adreslerim')
@section('breadcrumb_title', 'Adreslerim')
@section('content')
    @php
        $addresses = $data->addresses ?? collect();
    @endphp
    @include('frontend.parts.breadcrumb')
    <section class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-8">
                <form action="{{ route('user.addresses.store') }}" method="POST" class="space-y-6" data-address-form>
                    @csrf
                    <input type="hidden" name="address_id" value="{{ old('address_id') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Adres
                                Başlığı</label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                            @error('title')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Ad
                                Soyad</label>
                            <input type="text" name="fullname" value="{{ old('fullname', auth()->user()->name) }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                            @error('fullname')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Telefon</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">TC
                                Kimlik Numarası</label>
                            <input type="text" name="tc" value="{{ old('tc') }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">E-posta</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Şehir</label>
                            <input type="text" name="city_name" value="{{ old('city_name') }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0"
                                placeholder="Şehir adı">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">İlçe</label>
                            <input type="text" name="state_name" value="{{ old('state_name') }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0"
                                placeholder="İlçe adı">
                        </div>
                        <div>
                            <label class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Posta Kodu</label>
                            <input type="text" name="zip" value="{{ old('zip') }}"
                                class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium {{ $theme->color ? 'text-' . $theme->color : 'text-gray-700' }}">Adres</label>
                        <textarea name="address" rows="3"
                            class="mt-2 w-full px-4 py-3 border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl focus:border-{{ $theme->color ? 'border-' . $theme->color : 'blue-500' }} focus:ring-0">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-sm {{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }}">
                            <input type="checkbox" name="is_default" value="1" @checked(old('is_default'))>
                            Varsayılan adres olarak işaretle
                        </label>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                            class="px-6 py-3 rounded-full {{ $theme->color ? 'bg-' . $theme->color : 'bg-blue-600' }} text-white hover:{{ $theme->color ? 'text-' . $theme->color : 'text-white' }} text-sm font-semibold hover:{{ $theme->color ? 'bg-' . $theme->color . '/20' : 'bg-blue-700' }} transition-colors">
                            Adresi Kaydet
                        </button>
                        @if (session('status'))
                            <p class="text-sm text-green-600">{{ session('status') }}</p>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm divide-y divide-gray-100">
                @forelse ($addresses as $address)
                    <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $address->title }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $address->fullname }} • {{ $address->phone }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $address->tc }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $address->address }}<br>
                                {{ $address->city }} {{ $address->state }} {{ $address->zip }}
                            </p>
                            <p class="text-xs text-gray-400 mt-2">{{ $address->email }}</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            @if ($address->is_default)
                                <span
                                    class="px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold">Varsayılan</span>
                            @endif
                            <button type="button"
                                class="px-3 py-1 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 transition"
                                onclick="fillAddressForm({{ $address->toJson() }})">
                                Düzenle
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-sm text-gray-500">
                        Henüz adres eklemediniz.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
