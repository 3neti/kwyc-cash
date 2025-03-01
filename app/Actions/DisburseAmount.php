<?php

namespace App\Actions;

use Illuminate\Http\Client\ConnectionException;
use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Events\VoucherRedeemed;
use Illuminate\Support\Str;
use App\Models\Cash;

/**
 * Handles the disbursement of cash amounts to a specified mobile account.
 *
 * This action can be triggered manually, as a queued job, or as an event listener
 * for the VoucherRedeemed event.
 */
class DisburseAmount
{
    use AsAction;

    /**
     * Handles the core disbursement process.
     *
     * @param string $mobile The mobile number to receive the disbursement.
     * @param float $amount The amount to disburse.
     * @param string|null $tag An optional tag for the disbursement.
     * @return bool True if the disbursement was successful, otherwise false.
     * @throws ConnectionException
     */
    public function handle(string $mobile, float $amount, ?string $tag = null): bool
    {
        $body = $this->buildRequestBody($mobile, $amount, $tag);

        return $this->disburse($body);
    }

    /**
     * Dispatches the disbursement as a queued job.
     *
     * @param string $mobile The mobile number to receive the disbursement.
     * @param float $amount The amount to disburse.
     * @param string|null $tag An optional tag for the disbursement.
     * @return void
     * @throws ConnectionException
     */
    public function asJob(string $mobile, float $amount, ?string $tag = null): void
    {
        $this->handle($mobile, $amount, $tag);
    }

    /**
     * Handles the disbursement as a listener to the VoucherRedeemed event.
     *
     * @param VoucherRedeemed $event The event payload containing the voucher.
     * @return void
     */
    public function asListener(VoucherRedeemed $event): void
    {
        $voucher = $event->voucher;

        if ($voucher instanceof Voucher) {
            $mobile = $voucher->redeemer->redeemer->mobile;
            $cash = $voucher->getEntities(Cash::class)->first();
            $amount = $cash?->value->getAmount()->toFloat() ?? 0;

            if ($amount > 0) {
                self::dispatch($mobile, $amount);
            }
        }
    }

    /**
     * Sends the disbursement request to the external service.
     *
     * @param array $body The request payload.
     * @return bool True if the request was successful, otherwise false.
     * @throws ConnectionException
     */
    protected function disburse(array $body): bool
    {
        Log::info('Disbursement initiated', ['reference' => $body['reference']]);

        $response = Http::withHeaders($this->getRequestHeaders())
            ->post($this->getDisbursementUrl(), $body);

        Log::info('Disbursement response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response->ok() && Str::isUuid($response->json('uuid'));
    }

    /**
     * Builds the request body for the disbursement API.
     *
     * @param string $mobile The mobile number to receive the disbursement.
     * @param float $amount The amount to disburse.
     * @param string|null $tag An optional tag for the disbursement.
     * @return array The formatted request payload.
     */
    protected function buildRequestBody(string $mobile, float $amount, ?string $tag = null): array
    {
        return [
            'reference' => $this->generateReferenceCode(compact('mobile', 'amount', 'tag')),
            'bank' => $this->getBankCode(),
            'account_number' => $mobile,
            'via' => $this->getTransferVia(),
            'amount' => $amount,
        ];
    }

    /**
     * Generates a short, unique reference code combining a random string and the mobile number.
     *
     * @param array $params Array containing 'mobile' as a key.
     * @return string The generated reference code.
     */
    protected function generateReferenceCode(array $params): string
    {
        $randomPart = Str::upper(Str::random(8)); // Generates a random 8-character alphanumeric string
        $mobile = $params['mobile'];

        $referenceCode = "{$randomPart}-{$mobile}";

        logger('Generated reference code', ['reference' => $referenceCode]);

        return $referenceCode;
    }

    /**
     * Retrieves the configured bank code for disbursement.
     *
     * @return string The bank code from the configuration.
     */
    protected function getBankCode(): string
    {
        $bank_code = config('kwyc-cash.disbursement.bank.code', 'DEFAULT_BANK_CODE');
        Log::info('Using bank code', ['bank_code' => $bank_code]);

        return $bank_code;
    }

    /**
     * Retrieves the transfer method for the disbursement.
     *
     * @return string The transfer method from the configuration.
     */
    protected function getTransferVia(): string
    {
        $via = config('kwyc-cash.disbursement.bank.via', 'DEFAULT_VIA');
        Log::info('Using transfer method', ['via' => $via]);

        return $via;
    }

    /**
     * Retrieves the API endpoint URL for the disbursement request.
     *
     * @return string The disbursement server URL.
     */
    protected function getDisbursementUrl(): string
    {
        return config('kwyc-cash.disbursement.server.url', 'https://default.url');
    }

    /**
     * Prepares the HTTP headers for the disbursement request.
     *
     * @return array The formatted headers.
     */
    protected function getRequestHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('kwyc-cash.disbursement.server.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
