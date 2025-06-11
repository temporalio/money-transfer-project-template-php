<?php

declare(strict_types=1);

namespace App\Worker;

use App\Banking\Internal\BankingActivity;
use App\Banking\Internal\Service;
use Temporal\Worker\FeatureFlags;
use Temporal\Worker\Logger\StderrLogger;
use Temporal\WorkerFactory;

require_once __DIR__ . '/../vendor/autoload.php';

# Configure Worker behavior
FeatureFlags::$workflowDeferredHandlerStart = true;

# Create a Worker Factory
$logger = new StderrLogger();
$factory = WorkerFactory::create();
$worker = $factory->newWorker('default', logger: $logger);

# Register Workflows
$worker->registerWorkflowTypes(\App\Workflow\MoneyTransfer::class);

# Register Activities
$worker->registerActivity(BankingActivity::class, static fn(): BankingActivity => new BankingActivity(
    $logger,
    new Service('bank-api.example.com'),
));

$factory->run();
