<?php

declare(strict_types=1);

namespace App\Exceptions;

use FrittenKeeZ\Vouchers\Exceptions\VoucherException;
class VoucherSecretMismatch extends VoucherException
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'Voucher secret mismatch.';

    /**
     * Exception code - we use 409 Conflict.
     *
     * @var int
     */
    protected $code = 409;
}
