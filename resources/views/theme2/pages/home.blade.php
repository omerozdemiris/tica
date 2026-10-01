@extends('frontend.layouts.app')
@section('title', 'Ana Sayfa')
@section('content')
    @include('frontend.parts.home.sections')
    {{-- @include('frontend.parts.home.video') --}}
    @includeWhen(!empty($activeAnnouncement), 'frontend.parts.popup', ['announcement' => $activeAnnouncement])
@endsection
