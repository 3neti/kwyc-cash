<?php

namespace App\Http\Controllers;

use App\Http\Requests\SMSRequest;
use Illuminate\Http\Response;
use App\Events\SMSArrived;
use App\Data\SMSData;

class SMSController extends Controller
{
    /**
     * Handle an incoming SMS request and return a compressed JSON response.
     *
     * @param  SMSRequest  $request The validated request containing SMS data.
     * @return Response Gzipped JSON response containing the SMS data.
     */
    public function __invoke(SMSRequest $request): Response
    {
        // Transform the validated request data into an SMSData object
        $data = SMSData::from($request->validated());

        // Dispatch the SMSArrived event with the parsed data
        SMSArrived::dispatch($data);

        // Convert SMSData to JSON and compress it using Gzip
        $compressedJson = gzencode($data->toJson(), 5);

        // Return the compressed JSON response with appropriate headers
        return response($compressedJson, 200)
            ->header('Content-Encoding', 'gzip')
            ->header('Content-Type', 'application/json');
    }
}
