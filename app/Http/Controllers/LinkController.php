<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class LinkController extends Controller
{
    public function handle(string $hash)
    {
        $shortLink = ShortLink::where('hash', $hash)->first();

        if (!$shortLink) {
            return redirect('/');
        }

        $userAgent = request()->header('User-Agent');
        $isMobile = preg_match('/Mobile|Android|BlackBerry|iPhone|iPad|iPod|IEMobile|Opera Mini/i', $userAgent);
        $device = $isMobile ? 'mobile' : 'desktop';

        $source = $shortLink->visitor_type ?? 'other';

        \App\Services\TrafficLogger::logVisit(
            $shortLink->target_type,
            $shortLink->target_id,
            $shortLink->visitor_type ?? 'other'
        );

        return redirect($shortLink->original_url);
    }
}
