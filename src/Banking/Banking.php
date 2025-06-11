<?php

declare(strict_types=1);

namespace App\Banking;

use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface('banking.')]
interface Banking
{
    #[ActivityMethod]
    public function withdraw(PaymentDetails $data): string;

    #[ActivityMethod]
    public function deposit(PaymentDetails $data): string;

    #[ActivityMethod]
    public function refund(PaymentDetails $data): string;
}
