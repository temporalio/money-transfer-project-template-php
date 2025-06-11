<?php

declare(strict_types=1);

namespace App\Banking\Internal;

use App\Banking\Banking;
use App\Banking\Exception\InvalidAccount;
use App\Banking\PaymentDetails;
use Psr\Log\LoggerInterface;

final class BankingActivity implements Banking
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Service $bank,
    ) {}

    public function withdraw(PaymentDetails $data): string
    {
        $referenceId = $data->referenceId . "-withdrawal";
        try {
            $confirmation = $this->bank->withdraw(
                $data->sourceAccount,
                $data->amount,
                $referenceId,
            );
            return $confirmation;
        } catch (InvalidAccount $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error("Withdrawal failed", ['exception' => $e]);
            throw $e;
        }
    }

    public function deposit(PaymentDetails $data): string
    {
        $referenceId = $data->referenceId . "-deposit";
        try {
            $confirmation = $this->bank->deposit(
                $data->targetAccount,
                $data->amount,
                $referenceId,
            );
            /*
            $confirmation = $this->bank->depositThatFails(
                $data->targetAccount,
                $data->amount,
                $referenceId
            );
            */
            return $confirmation;
        } catch (InvalidAccount $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error("Deposit failed", ['exception' => $e]);
            throw $e;
        }
    }

    public function refund(PaymentDetails $data): string
    {
        $referenceId = $data->referenceId . "-refund";
        try {
            $confirmation = $this->bank->deposit(
                $data->sourceAccount,
                $data->amount,
                $referenceId,
            );
            return $confirmation;
        } catch (InvalidAccount $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error("Refund failed", ['exception' => $e]);
            throw $e;
        }
    }
}
