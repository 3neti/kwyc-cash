<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\{Campaign, User};

class AutoUserCampaign
{
    use AsAction;

    public function handle(User $user): void
    {
        $campaign = new Campaign;
        $campaign->user()->associate($user);
        $campaign->name = config('kwyc-cash.campaign.name');
        $campaign->inputs = json_decode(config('kwyc-cash.campaign.inputs'));
        $campaign->rider = config('kwyc-cash.campaign.rider');
        $campaign->dedication = config('kwyc-cash.campaign.dedication');
        $campaign->save();

        if ($user->currentCampaign == null) {
            $user->currentCampaign = $campaign;
            $user->save();
        }
    }
}
