<?php

declare(strict_types=1);

namespace App\Worker;

use App\Banking\PaymentDetails;
use App\Workflow\MoneyTransfer;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowOptions;
use Temporal\Common\IdReusePolicy;
use Temporal\Exception\Client\WorkflowFailedException;

require_once __DIR__ . '/../vendor/autoload.php';

# Create client connected to server at the given address
$client = WorkflowClient::create(
    ServiceClient::create('127.0.0.1:7233'),
);

$paymentDetails = new PaymentDetails(
    sourceAccount: '85-150',
    targetAccount: '43-812',
    amount: 250,
    referenceId: '12345',
);
$workflow = $client->newWorkflowStub(
    MoneyTransfer::class,
    WorkflowOptions::new()
        ->withWorkflowIdReusePolicy(IdReusePolicy::AllowDuplicate)
        ->withWorkflowRunTimeout(20)
        ->withWorkflowExecutionTimeout(30),
);

try {
    $result = $workflow->handle($paymentDetails);
    echo "\e[32mResult: $result\e[0m\n";
} catch (WorkflowFailedException $e) {
    echo "\e[31mWorkflow failed: {$e->getMessage()}\e[0m\n";
} catch (\Throwable $e) {
    echo "\e[31mError: {$e->getMessage()}\e[0m\n";
}
