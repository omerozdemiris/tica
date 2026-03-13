@php

    $breadcrumbTitle = trim($__env->yieldContent('breadcrumb_title'));

    $breadcrumbs = $breadcrumbs ?? [];

@endphp



@if ($breadcrumbTitle)

    <div

        class="hidden md:block bg-gray-50 border-b border-gray-200">

        <div

            class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                @if ($breadcrumbTitle)

                    <h1 class="text-xl font-semibold text-gray-900">

                        {{ $breadcrumbTitle }}</h1>

                @endif

            </div>

            <nav class="text-xs text-gray-500">

                <ol class="flex items-center gap-2">

                    <li>

                        <a href="{{ route('home') }}"

                            class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition">Ana

                            Sayfa</a>

                    </li>

                    @if (!empty($breadcrumbs))

                        @foreach ($breadcrumbs as $crumb)

                            <li>/</li>

                            <li>

                                <a href="{{ $crumb['url'] }}"

                                    class="hover:{{ $theme->color ? 'text-' . $theme->color : 'text-gray-600' }} transition">{{ $crumb['label'] }}</a>

                            </li>

                        @endforeach

                    @endif

                    @if ($breadcrumbTitle)

                        <li>/</li>

                        <li class="text-gray-700">

                            {{ $breadcrumbTitle }}</li>

                    @endif

                </ol>

            </nav>

        </div>

    </div>



    <div class="md:hidden bg-white border-b border-gray-100 overflow-x-auto no-scrollbar sticky top-16 z-20">

        <div class="flex items-center gap-1 px-4 py-3 text-xs text-gray-600 whitespace-nowrap">

            <a href="{{ route('home') }}" class="flex-shrink-0">{{ config('app.name') }}</a>



            @if (!empty($breadcrumbs))

                @foreach ($breadcrumbs as $crumb)

                    <i class="ri-arrow-right-s-line text-gray-400 text-base"></i>

                    <a href="{{ $crumb['url'] }}"

                        class="flex-shrink-0 {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">

                        {{ $crumb['label'] }}

                    </a>

                @endforeach

            @endif



            <i class="ri-arrow-right-s-line text-gray-400 text-base"></i>

            <span class="flex-shrink-0 font-semibold {{ $theme->color ? 'text-' . $theme->color : 'text-gray-900' }}">

                {{ $breadcrumbTitle }}

            </span>

        </div>

    </div>

@endif

