<?php

namespace Database\Seeders;

use App\Actions\GenerateCashVouchers;
use App\Models\{Cash, User};
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoucherSeeder extends Seeder
{
    /**
     * Seed the application's database with cash vouchers.
     */
    public function run(): void
    {
        // Truncate existing data
        DB::table('cashes')->truncate();
        DB::table('vouchers')->truncate();

        // Fetch or create the system user
        $user = User::firstOrCreate(
            ['email' => 'system@example.com'],
            [
                'name' => 'System User',
                'password' => bcrypt('password'), // Set a default password
            ]
        );

        // Ensure the system user has enough balance
        $user->depositFloat(10000);

        // Parameters for generating cash vouchers
        $params = [
            'qty' => 10,
            'value' => 20,
            'tag' => 'SEEDED',
        ];

        // Generate cash vouchers
        $vouchers = GenerateCashVouchers::run($user, $params);

        $this->command->info('Generated ' . $vouchers->count() . ' vouchers.');

        // Display each voucher code in the terminal
        $this->command->table(
            ['Voucher Code', 'Amount', 'Tag'],
            $vouchers->map(function (Voucher $voucher) {
                $cash = $voucher->getEntities(Cash::class)->first();
                return [
                    'code' => $voucher->code,
                    'amount' => $cash?->value->getAmount()->toFloat() ?? 'N/A',
                    'tag' => $cash?->tag ?? 'N/A',
                ];
            })->toArray()
        );
    }
}
