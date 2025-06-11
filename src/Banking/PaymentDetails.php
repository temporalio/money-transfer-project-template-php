<?php

declare(strict_types=1);

namespace App\Banking;

final class PaymentDetails
{
    /**
     * @param non-empty-string $sourceAccount The account from which money will be withdrawn.
     * @param non-empty-string $targetAccount The account to which money will be deposited.
     * @param int<1, max> $amount The amount of money to transfer.
     * @param non-empty-string $referenceId A unique identifier for the transaction.
     */
    public function __construct(
        public readonly string $sourceAccount,
        public readonly string $targetAccount,
        public readonly int $amount,
        public readonly string $referenceId,
    ) {}
}
