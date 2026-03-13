<div id="ziraat-iframe-container" class="mt-6 {{ $paymentMethod === 'wire' ? 'hidden' : '' }}">
    @if (isset($paymentData) && isset($paymentData['action']))
        <iframe src="{{ $paymentData['action'] }}" 
                class="w-full border {{ $theme->color ? 'border-' . $theme->color : 'border-gray-200' }} rounded-xl" 
                style="min-height: 600px;" 
                frameborder="0">
        </iframe>
    @endif
</div>
