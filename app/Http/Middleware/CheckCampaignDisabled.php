<?php

namespace App\Http\Middleware;

use App\Models\Campaign;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCampaignDisabled
{
    public function handle(Request $request, Closure $next)
    {
        $campaign = $request->route('campaign');
        if ($campaign instanceof Campaign) {
            if ($campaign->disabled)
                return inertia()->location($campaign->rider);
        }

        return $next($request);
    }
}
