<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Session;
use App\Models\Campaign;

class AutoCampaignCheckin
{
    use AsAction;

    public function handle(Campaign $campaign, array $inputs = [])
    {
        // put check in here...
    }

    public function asController(Campaign $campaign)
    {
        Session::forget(['voucher_code', 'redeemer_data', 'signature_data']);

        return inertia()->render('Voucher/Redeem', [
//            'country' => 'US',
            'inputs' => json_encode($campaign->inputs),
            'rider' => $campaign->rider,
            'referenceLabel' => $campaign->reference_label,
        ]);

        // TODO: deprecate route('old-redeem.create')
    }
}
