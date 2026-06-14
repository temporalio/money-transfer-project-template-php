<?php

declare(strict_types=1);

namespace App\Worker;

use App\Banking\PaymentDetails;
use App\Workflow\MoneyTransfer;
use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowOptions;
use Temporal\Common\IdReusePolicy;
use Temporal\Exception\Client\WorkflowFailedException;

require_once __DIR__ . '/../vendor/autoload.php';

# Connect to Temporal Cloud using the gRPC endpoint, namespace, and API key
# supplied via environment variables, over a secure (TLS) connection.
$client = WorkflowClient::create(
    ServiceClient::createSSL((string) getenv('TEMPORAL_ADDRESS'))
        ->withAuthKey((string) getenv('TEMPORAL_API_KEY')),
    (new ClientOptions())->withNamespace((string) getenv('TEMPORAL_NAMESPACE')),
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
