<?php

declare(strict_types=1);

namespace App\Banking\Internal;

use App\Banking\Exception\InsufficientFunds;
use App\Banking\Exception\InvalidAccount;

/**
 * A mock implementation of a banking API.
 *
 * The BankingService class provides methods for simulating deposits and withdrawals
 * from bank accounts, as well as a method for simulating a deposit that always fails.
 */
final class Service
{
    private MockBank $mockBank;

    /**
     * Constructs a new BankingService object with the given hostname.
     *
     * @param non-empty-string $hostname The hostname of the banking API service.
     */
    public function __construct(
        private string $hostname,
    ) {
        $this->mockBank = new MockBank([
            new Account("85-150", 2000),
            new Account("43-812", 0),
        ]);
    }

    /**
     * Simulates a withdrawal from a bank account.
     *
     * @param non-empty-string $accountNumber The account number to withdraw from.
     * @param int<1, max> $amount The amount to withdraw from the account.
     * @param non-empty-string $referenceId An identifier for the transaction, used for idempotency.
     *
     * @return non-empty-string A transaction ID
     *
     * @throws InvalidAccount If the account number is invalid.
     * @throws InsufficientFunds If the account does not have enough funds to complete the withdrawal.
     */
    public function withdraw(string $accountNumber, int $amount, string $referenceId): string
    {
        $account = $this->mockBank->findAccount($accountNumber);
        $amount <= $account->balance or throw new InsufficientFunds(
            "The account {$accountNumber} has insufficient funds to complete this transaction.",
        );

        return $this->generateTransactionId("W");
    }

    /**
     * Simulates a deposit to a bank account.
     *
     * @param non-empty-string $accountNumber The account number to deposit to.
     * @param int<1, max> $amount The amount to deposit to the account.
     * @param non-empty-string $referenceId An identifier for the transaction, used for idempotency.
     *
     * @return non-empty-string A transaction ID.
     *
     * @throws InvalidAccount If the account number is invalid.
     */
    public function deposit(string $accountNumber, int $amount, string $referenceId): string
    {
        $this->mockBank->findAccount($accountNumber);

        return $this->generateTransactionId("D");
    }

    /**
     * Simulates a deposit to a bank account that always fails with an
     * unknown error.
     *
     * @param non-empty-string $accountNumber The account number to deposit to.
     * @param int<1, max> $amount The amount to deposit to the account.
     * @param non-empty-string $referenceId An identifier for the transaction, used for idempotency.
     * @throws \Exception Always throws this exception.
     */
    public function depositThatFails(string $accountNumber, int $amount, string $referenceId): never
    {
        throw new \Exception("This deposit has failed.");
    }

    /**
     * Generates a transaction ID we can send back.
     *
     * @param non-empty-string $prefix A prefix so you can identify the type of transaction.
     * @return non-empty-string The transaction id.
     */
    private function generateTransactionId(string $prefix): string
    {
        $uuid = \sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            \mt_rand(0, 0xffff),
            \mt_rand(0, 0xffff),
            \mt_rand(0, 0xffff),
            \mt_rand(0, 0x0fff) | 0x4000,
            \mt_rand(0, 0x3fff) | 0x8000,
            \mt_rand(0, 0xffff),
            \mt_rand(0, 0xffff),
            \mt_rand(0, 0xffff),
        );

        return "{$prefix}-{$uuid}";
    }
}
