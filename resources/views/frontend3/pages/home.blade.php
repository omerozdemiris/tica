@extends($template . '.layouts.app')
@section('title', 'Ana Sayfa')
@section('content')
    @include($template . '.parts.home.sections')
    @include($template . '.parts.home.video')
    @includeWhen(!empty($activeAnnouncement), $template . '.parts.popup', ['announcement' => $activeAnnouncement])
@endsection
