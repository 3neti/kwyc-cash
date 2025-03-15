<?php

namespace App\Actions;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{Http, Log};
use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\{Arr, Str};
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
     * Handles the core disbursement process using a Voucher model.
     *
     * @param Voucher $voucher The voucher model containing cash and redeemer details.
     * @return bool True if the disbursement was successful, otherwise false.
     * @throws ConnectionException
     */
    public function handle(Voucher $voucher): bool
    {
        $cash = $voucher->getEntities(Cash::class)->first();
        if (!$cash instanceof Cash) {
            Log::warning('No associated Cash entity found for the voucher', ['voucher_code' => $voucher->code]);
            return false;
        }
        $mobile = $voucher->redeemers->first()?->redeemer->mobile ?? null;
        $amount = $cash->value->getAmount()->toFloat();
        $tag = $cash->tag;
        if (!$mobile || $amount <= 0) {
            Log::warning('Invalid disbursement data', compact('mobile', 'amount', 'tag'));
            return false;
        }
        $body = $this->buildRequestBody($voucher, $mobile, $amount, $tag);
        $disbursementSuccess = $this->disburse($body);
        if ($disbursementSuccess) {
            $cash->disbursed = true;
            $cash->save();
            Log::info('Disbursement successful', ['voucher_code' => $voucher->code, 'amount' => $amount, 'mobile' => $mobile]);

            return true;
        } else {
            Log::warning('Disbursement failed', ['voucher_code' => $voucher->code]);
        }

        return false;
    }

    /**
     * Dispatches the disbursement as a queued job.
     *
     * @param Voucher $voucher The voucher model to disburse.
     * @return void
     * @throws ConnectionException
     */
    public function asJob(Voucher $voucher): void
    {
        $this->handle($voucher);
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
        if (config('kwyc-cash.disbursement.disabled')) {
            return true;
        }

        Log::info('Disbursement initiated', ['reference' => $body['reference']]);

        $response = Http::withHeaders($this->getRequestHeaders())
            ->post($this->getDisbursementUrl(), $body);

        Log::info('Disbursement HTTP', [
            'headers' => $this->getRequestHeaders(),
            'url' => $this->getDisbursementUrl()
        ]);

        Log::info('Disbursement response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response->ok() && Str::isUuid($response->json('uuid'));
    }

    /**
     * Builds the request body for the disbursement API.
     *
     * @param Voucher $voucher The voucher model.
     * @param string $mobile The mobile number to receive the disbursement.
     * @param float $amount The amount to disburse.
     * @param string|null $tag An optional tag for the disbursement.
     * @return array The formatted request payload.
     */
    protected function buildRequestBody(Voucher $voucher, string $mobile, float $amount, ?string $tag = null): array
    {
        return [
            'reference' => $this->generateReferenceCode($voucher, $mobile, $amount, $tag),
            'bank' => $this->getBankCode(),
            'account_number' => $mobile,
            'via' => $this->getTransferVia(),
            'amount' => $amount,
        ];
    }

    /**
     * Generates a unique reference code for the disbursement.
     *
     * @param Voucher $voucher The voucher model.
     * @param string $mobile The mobile number.
     * @param float $amount The disbursement amount.
     * @param string|null $tag The disbursement tag.
     * @return string The generated reference code.
     */
    protected function generateReferenceCode(Voucher $voucher, string $mobile, float $amount, ?string $tag = null): string
    {
        $randomPart = $voucher->code;
        $referenceCode = "{$randomPart}-{$mobile}";

        Log::info('Generated reference code', ['reference' => $referenceCode]);

        return $referenceCode;
    }

    /**
     * Retrieves the configured bank code for disbursement.
     *
     * @return string The bank code from the configuration.
     */
    protected function getBankCode(): string
    {
        $bankCode = config('kwyc-cash.disbursement.bank.code', 'DEFAULT_BANK_CODE');
        Log::info('Using bank code', ['bank_code' => $bankCode]);

        return $bankCode;
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
