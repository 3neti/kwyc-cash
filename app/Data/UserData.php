<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Models\User;

class UserData extends Data
{
    public function __construct(
      public string $name,
      public string $email,
      public string $mobile
    ) {}

    public static function fromModel(User $user): UserData
    {
        return new self(
            name: $user->name,
            email: $user->email,
            mobile: $user->mobile
        );
    }
}
