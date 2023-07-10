<?php

namespace Alinoksha\Mcba;

use Alinoksha\Mcba\Enums\Currency;
use Alinoksha\Mcba\Exceptions\NotExistingCurrencyRateException;

class CurrencyConverter
{
    public function __construct(
        private array $rateList,
    ) {
    }

    /**
     * @throws NotExistingCurrencyRateException
     */
    public function convert(Money $money, Currency $currency): Money
    {
        if ($money->getCurrency() === $currency) {
            return $money;
        }

        if (
            !key_exists($money->getCurrency()->value, $this->rateList)
            || !key_exists($currency->value, $this->rateList[$money->getCurrency()->value])
        ) {
            throw new NotExistingCurrencyRateException();
        }

        $rate = $this->rateList[$money->getCurrency()->value][$currency->value];

        return new Money(
            currency: $currency,
            amount: (int)($money->getAmount() * $rate)
        );
    }

    public function setConvertRate(Currency $from, Currency $to, float $rate): void
    {
        $this->rateList[$from->value][$to->value] = $rate;
    }
}
