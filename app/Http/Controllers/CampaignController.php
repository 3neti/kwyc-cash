<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Auth, Validator};
use Illuminate\Support\Facades\Log;
use App\Models\{Campaign, User};
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    // Fetch all campaigns for the authenticated user
    public function index()
    {
        $campaigns = Auth::user()->campaigns()->get();

        return response()->json([
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Campaign/Create', [
            'inputs' => config('kwyc-cash.campaign.inputs'),
            'availableInputs' => config('kwyc-cash.campaign.available-inputs'),
            'rider' => config('kwyc-cash.campaign.rider')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function show(Campaign $campaign)
    {
        // Update the user's current campaign

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, Campaign $campaign)
    {
        // Validate the request data
        $validated = Validator::make($request->all(), [
            'inputs' => 'nullable|json',
            'feedback' => 'nullable|string',
            'rider' => 'nullable|url',
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function setCurrent(Request $request, Campaign $campaign)
    {
        Log::info('Attempting to switch campaign', [
            'user_id' => auth()->id(),
            'campaign_id' => $campaign->id
        ]);

        // Get the authenticated user
        $user = auth()->user();

        if ($user instanceof User) {
            // Set the new current campaign
            $user->currentCampaign = $campaign;
            $user->save();

            Log::info('Campaign switched successfully', [
                'user_id' => $user->id,
                'new_campaign_id' => $user->currentCampaign->id,
            ]);

            return back()->with('event', [
                'name' => 'current-campaign-switched',
                'data' => [
                    'campaign' => $user->currentCampaign
                ],
            ]);
        }

        Log::warning('Failed to switch campaign. Authenticated user is invalid.', [
            'user' => $user
        ]);

        return redirect()->back()->with('warning', 'Something is wrong.');
    }
}
