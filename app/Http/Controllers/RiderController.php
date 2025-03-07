<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RiderController extends Controller
{
    public function __invoke(Request $request)
    {
        $rider = $request->get('rider');

        return $rider
            ? inertia()->location($rider)
            : redirect(route('home'));
    }
}
