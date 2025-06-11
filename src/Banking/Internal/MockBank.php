<?php

declare(strict_types=1);

namespace App\Banking\Internal;

use App\Banking\Exception\InvalidAccount;

/**
 * A Bank with a list of accounts.
 *
 * The Bank class provides methods for finding an account with a given account number.
 */
final class MockBank
{
    /** @var Account[] A list of Account objects representing the bank's accounts. */
    private array $accounts;

    /**
     * @param Account[] $accounts A list of Account objects representing the bank's accounts.
     */
    public function __construct(array $accounts)
    {
        $this->accounts = $accounts;
    }

    /**
     * Finds and returns the Account object with the given account number.
     *
     * @param non-empty-string $accountNumber The account number to search for.
     * @return Account The Account object with the given account number.
     * @throws InvalidAccount If no account with the given account number is
     *                            found in the bank's accounts list.
     */
    public function findAccount(string $accountNumber): Account
    {
        foreach ($this->accounts as $account) {
            if ($account->accountNumber === $accountNumber) {
                return $account;
            }
        }

        throw new InvalidAccount("The account number {$accountNumber} is invalid.");
    }

    /**
     * @return Account[] Returns all accounts in the bank.
     */
    public function getAccounts(): array
    {
        return $this->accounts;
    }
}
