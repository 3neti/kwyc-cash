<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate existing campaigns
        DB::table('campaigns')->truncate();

        // Fetch or create the system user
        $user = User::firstOrCreate(
            ['email' => 'lester@hurtado.ph'],
            [
                'name' => 'System User',
                'mobile' => '09173011987',
                'country' => 'PH',
                'password' => bcrypt('password'),
            ]
        );

        // Create two campaigns for the system user
        $campaigns = Campaign::factory()->count(5)->create([
            'user_id' => $user->id,
            'inputs' => json_decode(config('kwyc-cash.campaign.inputs')),
            'rider' => config('kwyc-cash.campaign.rider'),
        ]);

        // Set the first campaign as the current campaign
        $currentCampaign = $campaigns->first();

//        // Set the last campaign as the current campaign
//        $currentCampaign = $campaigns->last();

        $user->current_campaign = $currentCampaign;
        $user->save();

        $this->command->info("Created {$campaigns->count()} campaigns for the system user.");
        $this->command->info("Current campaign set to: {$currentCampaign->name}");

        // Display each campaign in the terminal
        $this->command->table(
            ['Campaign ID', 'Name', 'User ID', 'Rider'],
            $campaigns->map(fn(Campaign $campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'user_id' => $campaign->user_id,
                'inputs' => json_encode($campaign->inputs),
                'rider' => $campaign->rider,
            ])->toArray()
        );

        // Display the current campaign separately
        $this->command->table(
            ['Current Campaign ID', 'Name', 'User ID'],
            [[
                'id' => $currentCampaign->id,
                'name' => $currentCampaign->name,
                'user_id' => $user->id,
            ]]
        );
    }
}
