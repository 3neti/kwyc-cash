<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SignatureController extends Controller
{
    public function create(Request $request)
    {
        return inertia()->render('Redeem/Signature', [
            'payload' => session()->getOldInput()
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->except('signature_data'); // Remove the signature data from payload

        // Add the signature data to the inputs['signature']
        $payload['inputs']['signature'] = $request->input('signature_data');

        return redirect()->intended()->withInput($payload);
    }
}
