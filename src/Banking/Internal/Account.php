<?php

declare(strict_types=1);

namespace App\Banking\Internal;

/**
 * A mock implementation of a banking API.
 *
 * The BankingService class provides methods for simulating deposits and withdrawals
 * from bank accounts, as well as a method for simulating a deposit that always fails.
 */
final class Account
{
    /**
     * @param non-empty-string $accountNumber The account number for the account.
     * @param int<0, max> $balance The balance of the account.
     */
    public function __construct(
        public readonly string $accountNumber,
        public readonly int $balance,
    ) {}
}
