<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Banking\Banking;
use App\Banking\Exception\InsufficientFunds;
use App\Banking\Exception\InvalidAccount;
use App\Banking\PaymentDetails;
use Temporal\Activity\ActivityOptions;
use Temporal\Common\RetryOptions;
use Temporal\DataConverter\Type;
use Temporal\Exception\Failure\ActivityFailure;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow;
use Temporal\Workflow\ReturnType;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
final class MoneyTransfer
{
    private Banking|ActivityProxy $bankingActivity;

    public function __construct()
    {
        $this->bankingActivity = Workflow::newActivityStub(
            Banking::class,
            ActivityOptions::new()
                ->withStartToCloseTimeout('5 seconds')
                ->withRetryOptions(
                    RetryOptions::new()
                        ->withMaximumAttempts(3)
                        ->withMaximumInterval('2 seconds')
                        ->withNonRetryableExceptions([InvalidAccount::class, InsufficientFunds::class]),
                ),
        );
    }

    #[WorkflowMethod('money_transfer')]
    #[ReturnType(Type::TYPE_STRING)]
    public function handle(PaymentDetails $paymentDetails): \Generator
    {
        # Withdraw money
        $withdrawOutput = yield $this->bankingActivity->withdraw($paymentDetails);

        # Deposit money
        try {
            $depositOutput = yield $this->bankingActivity->deposit($paymentDetails);

            return "Transfer complete (transaction IDs: {$withdrawOutput}, {$depositOutput})";
        } catch (\Throwable $depositError) {
            # Handle deposit error
            Workflow::getLogger()->error("Deposit failed: {$depositError->getMessage()}");

            # Attempt to refund
            try {
                $refundOutput = yield $this->bankingActivity->refund($paymentDetails);

                Workflow::getLogger()->info('Refund successful. Confirmation ID: ' . $refundOutput);
            } catch (ActivityFailure $refundError) {
                Workflow::getLogger()->error("Refund failed: {$refundError->getMessage()}");
                throw $refundError;
            }

            # Re-raise deposit error if refund was successful
            throw $depositError;
        }
    }
}
