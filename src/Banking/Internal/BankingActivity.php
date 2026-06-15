<?php

declare(strict_types=1);

namespace App\Banking\Internal;

use App\Banking\Banking;
use App\Banking\Exception\InvalidAccount;
use App\Banking\PaymentDetails;
use Psr\Log\LoggerInterface;
use Temporal\Activity;
use Temporal\Exception\Failure\ApplicationFailure;

final class BankingActivity implements Banking
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Service $bank,
    ) {}

    #[\Override]
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

    #[\Override]
    public function deposit(PaymentDetails $data): string
    {
        $referenceId = $data->referenceId . "-deposit";

        // Demo-only failure injection, driven by the DEMO_FAILURE env var on the
        // Worker. Unset/off leaves behavior unchanged.
        $demoFailure = \strtolower((string) \getenv('DEMO_FAILURE'));
        if ($demoFailure === 'transient' && Activity::getInfo()->attempt < 3) {
            // Reuse the always-failing banking path for the first two attempts;
            // the error is retryable, so Temporal retries and the activity
            // succeeds on attempt 3 -> the Workflow recovers and COMPLETEs.
            $this->bank->depositThatFails($data->targetAccount, $data->amount, $referenceId);
        } elseif ($demoFailure === 'permanent') {
            // Reuse the always-failing banking path, but make it non-retryable so
            // the Workflow's refund compensation (saga rollback) runs instead of
            // retrying.
            try {
                $this->bank->depositThatFails($data->targetAccount, $data->amount, $referenceId);
            } catch (\Throwable $e) {
                throw new ApplicationFailure($e->getMessage(), 'DepositFailure', nonRetryable: true, previous: $e);
            }
        }

        try {
            $confirmation = $this->bank->deposit(
                $data->targetAccount,
                $data->amount,
                $referenceId,
            );
            return $confirmation;
        } catch (InvalidAccount $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error("Deposit failed", ['exception' => $e]);
            throw $e;
        }
    }

    #[\Override]
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
