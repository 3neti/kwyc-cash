<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CashUser extends Pivot
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cash()
    {
        return $this->belongsTo(Cash::class);
    }
}
