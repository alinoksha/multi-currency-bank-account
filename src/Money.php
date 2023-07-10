<?php

namespace Alinoksha\Mcba;

use Alinoksha\Mcba\Enums\Currency;

class Money
{
    public function __construct(
        private readonly Currency $currency,
        private readonly int $amount
    ) {
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
