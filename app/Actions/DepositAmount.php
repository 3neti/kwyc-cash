<?php

namespace App\Actions;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\User;

class DepositAmount
{
    use AsAction;

    /**
     * @throws ExceptionInterface
     */
    public function handle(User $user, float $amount): \Bavix\Wallet\Models\Transfer
    {
        $system = User::system();

        return $system->transferFloat($user, $amount);
    }
}
