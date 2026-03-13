@if ($footer_campaign)
    <div data-campaign="footer" data-href="{{ $footer_campaign->link ?? '#' }}" class="campaign-container"
        style="background-color: {{ $footer_campaign->background_color ?? '#dc2626' }};">
        <span class="campaign-text">{{ $footer_campaign->title }}</span>
    </div>
@endif
