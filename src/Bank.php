<?php

namespace Alinoksha\Mcba;

use Alinoksha\Mcba\Enums\Currency;

class Bank
{
    public function __construct(
        private readonly CurrencyConverter $currencyConverter
    ) {
    }

    public function createAccount(): Account
    {
        return new Account(
            balance: [
                Currency::Rub->value => 0,
            ],
            mainCurrency: Currency::Rub,
            converter: $this->currencyConverter,
        );
    }
}
