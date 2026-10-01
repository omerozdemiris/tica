<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        $theme = Theme::first();
        return view($theme->thene . '.pages.consts.about');
    }

    public function privacy()
    {
        $theme = Theme::first();
        return view($theme->thene . '.pages.consts.privacy');
    }

    public function cookies()
    {
        $theme = Theme::first();
        return view($theme->thene . '.pages.consts.cookies');
    }

    public function distanceSelling()
    {
        $theme = Theme::first();
        return view($theme->thene . '.pages.consts.distance_selling');
    }
    public function faq()
    {
        $theme = Theme::first();
        return view($theme->thene . '.pages.consts.faq');
    }
    public function returnPolicy()
    {
        $theme = Theme::first();
        return view($theme->thene . '.pages.consts.return_policy');
    }
    public function commercialPolicy()
    {
        $theme = Theme::first();
        return view($theme->thene . '.pages.consts.commercial_policy');
    }
    public function usageRequirements()
    {
        $theme = Theme::first();
        return view($theme->thene . '.pages.consts.usage_requirements');
    }
}
