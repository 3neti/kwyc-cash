<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Number;
use App\Models\User;

class SystemUserSeeder extends Seeder
{
    /**
     * Seed the system user into the database.
     */
    public function run(): void
    {
        $name =  config('kwyc-cash.system.user.name');
        $email = config('kwyc-cash.system.user.email');
        $mobile = config('kwyc-cash.system.user.mobile');
        $password = config('kwyc-cash.system.user.password');
        $country = config('kwyc-cash.system.user.country') ;
        $prefund = config('kwyc-cash.system.user.prefund');
        $user = User::updateOrCreate(
            ['email' => $email], // Ensure this email matches your intention
            [
                'name' => $name,
                'password' => bcrypt($password), // Default password
                'mobile' => $mobile,
                'country' => $country,
                'system_user' => true,
            ]
        );
        $user->depositFloat($prefund);

        $this->command->info(__(':name created with email address (:email), mobile number (:mobile) and a :prefund pre-fund.',[
            'name' => $name,
            'email' => $email,
            'mobile' => phone($mobile, $country)->formatE164(),
            'prefund' => Number::currency($prefund)
        ]));
    }
}
