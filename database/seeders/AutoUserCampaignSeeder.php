<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Log;
use App\Actions\AutoUserCampaign;
use Illuminate\Database\Seeder;
use App\Models\User;

class AutoUserCampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch all users
        User::all()->each(function ($user) {
            Log::info("Dispatching AutoUserCampaign for user", ['user_id' => $user->id]);

            // Dispatch the job for each user
            AutoUserCampaign::dispatch($user);
        });

        Log::info("AutoUserCampaignSeeder completed.");
    }
}
