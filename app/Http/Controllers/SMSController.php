<?php

namespace App\Http\Controllers;

use App\Services\SMSRouterService;
use App\Http\Requests\SMSRequest;
use Illuminate\Http\Response;
use App\Events\SMSArrived;
use App\Data\SMSData;

class SMSController extends Controller
{
    public function __invoke(SMSRequest $request)
    {
        // Transform the validated request data into an SMSData object
        $data = SMSData::from($request->validated());

        // Dispatch the SMSArrived event with the parsed data
        SMSArrived::dispatch($data);


        /** @var SMSRouterService $router */
        $router = resolve(SMSRouterService::class);
        $message = trim($data->message);

        return $router->handle($message, $data->from, $data->to);

//        // Convert SMSData to JSON and compress it using Gzip
//        $compressedJson = gzencode($data->toJson(), 5);
//
//        // Return the compressed JSON response with appropriate headers
//        return response($compressedJson, 200)
//            ->header('Content-Encoding', 'gzip')
//            ->header('Content-Type', 'application/json');
    }
}
