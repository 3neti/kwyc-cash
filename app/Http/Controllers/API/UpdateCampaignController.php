<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Campaign;

class UpdateCampaignController extends Controller
{
    public function update(Request $request, Campaign $campaign)
    {
        // Validate the request data
        $validated = Validator::make($request->all(), [
            'inputs' => 'nullable|json',
            'feedback' => 'nullable|string',
            'rider' => 'nullable|url',
            'reference_label' => 'nullable|string',
        ])->validate();

        Log::info('Campaign update request validated', [
            'campaign_id' => $campaign->id,
            'validated_data' => $validated,
        ]);

        // Ensure 'inputs' is stored as a JSON object, not a stringified JSON
        if (!empty($validated['inputs']) && is_string($validated['inputs'])) {
            $validated['inputs'] = json_decode($validated['inputs'], true); // Convert string to array
        }

        // Update campaign fields
        $campaign->update($validated);

        Log::info('Campaign successfully updated', [
            'campaign_id' => $campaign->id,
            'updated_campaign' => $campaign->fresh(),
        ]);

        return response()->json([
            'message' => 'Campaign updated successfully',
            'campaign' => $campaign->fresh(), // Return fresh campaign data
        ]);
    }
}
