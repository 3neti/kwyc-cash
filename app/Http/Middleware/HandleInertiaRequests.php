<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => function () use ($user) {
                    if (!$user) {
                        return null;
                    }

                    // Eager load necessary relationships
                    $user->load(['currentCampaign']);
//                    $user->currentCampaign->append('QRCodeURI');

                    return array_merge($user->toArray(), [
                        'balanceFloat' => (float) $user->balanceFloat,
                        'balanceUpdatedAt' => $user->updated_at,
                        'current_campaign' => $user->currentCampaign
                            ? array_merge($user->currentCampaign->toArray(), [
                                'url' => $user->currentCampaign->url,
                                'QRCodeURI' => $user->currentCampaign->QRCodeURI,
                            ])
                            : null,
                    ]);
                },
            ],
//            'auth' => [
//                'user' => fn () => $user ? array_merge($user->toArray(), [
//                    'balanceFloat' => (float) $user->balanceFloat,
//                    'balanceUpdatedAt' => $user->updated_at,
//                ]) : null,
//            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'warning' => fn () => $request->session()->get('warning'),
                'data' => fn () => $request->session()->get('data'),
                'event' => fn () => $request->session()->get('event'),
            ],
            'data' => [
                'appLink' => env('APP_URL', 'http://kwyc-cash.test/'),
            ],
            'app.name' => config('app.name'),
            'app.url' => config('app.url'),
            'footer.message' => env('FOOTER_MESSAGE', 'DevOps Asïana v3.17 (DVO v8.2.25)')
        ];
    }
}
