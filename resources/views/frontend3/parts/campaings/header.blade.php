@if ($header_campaign)
    <div data-campaign="header" data-href="{{ $header_campaign->link ?? '#' }}" class="campaign-container"
        style="background-color: {{ $header_campaign->background_color ?? '#dc2626' }};">
        <span class="text-white font-bold">{{ $header_campaign->title }}</span>
    </div>
@endif
