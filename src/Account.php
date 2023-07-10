<?php

namespace Alinoksha\Mcba;

use Alinoksha\Mcba\Enums\Currency;
use Alinoksha\Mcba\Exceptions\DisableCurrencyWithMoneyException;
use Alinoksha\Mcba\Exceptions\DisableMainCurrencyException;
use Alinoksha\Mcba\Exceptions\NotExistingCurrencyRateException;
use Alinoksha\Mcba\Exceptions\NotSupportedCurrencyException;
use Alinoksha\Mcba\Exceptions\WithdrawMoneyException;
use LogicException;

class Account
{
    /**
     * @param array<string, int> $balance
     */
    public function __construct(
        private array $balance,
        private Currency $mainCurrency,
        private readonly CurrencyConverter $converter,
    ) {
        if (!key_exists($this->mainCurrency->value, $this->balance)) {
            throw new LogicException();
        }
    }

    public function addCurrency(Currency $currency): void
    {
        if (key_exists($currency->value, $this->balance)) {
            return;
        }

        $this->balance[$currency->value] = 0;
    }

    /**
     * @throws DisableCurrencyWithMoneyException
     * @throws DisableMainCurrencyException
     */
    public function disableCurrency(Currency $currency): void
    {
        if (!key_exists($currency->value, $this->balance)) {
            return;
        }

        if ($this->mainCurrency === $currency) {
            throw new DisableMainCurrencyException();
        }

        if ($this->balance[$currency->value] !== 0) {
            throw new DisableCurrencyWithMoneyException();
        }

        unset($this->balance[$currency->value]);
    }

    /**
     * @throws NotSupportedCurrencyException
     */
    public function setMainCurrency(Currency $currency): void
    {
        if (!key_exists($currency->value, $this->balance)) {
            throw new NotSupportedCurrencyException();
        }

        $this->mainCurrency = $currency;
    }

    public function getMainCurrency(): Currency
    {
        return $this->mainCurrency;
    }

    /**
     * @throws NotSupportedCurrencyException
     * @throws WithdrawMoneyException
     */
    public function withdrawMoney(Currency $currency, int $amount): Money
    {
        if (!key_exists($currency->value, $this->balance)) {
            throw new NotSupportedCurrencyException();
        }

        if ($this->balance[$currency->value] < $amount) {
            throw new WithdrawMoneyException();
        }

        $this->balance[$currency->value] -= $amount;

        return new Money($currency, $amount);
    }

    /**
     * @throws NotSupportedCurrencyException
     */
    public function depositMoney(Money $money): void
    {
        if (!key_exists($money->getCurrency()->value, $this->balance)) {
            throw new NotSupportedCurrencyException();
        }

        $this->balance[$money->getCurrency()->value] += $money->getAmount();
    }

    /**
     * @return string[]
     */
    public function getSupportedCurrenciesList(): array
    {
        return array_keys($this->balance);
    }

    /**
     * @throws NotExistingCurrencyRateException
     * @throws NotSupportedCurrencyException
     */
    public function getBalance(Currency $currency = null): Money
    {
        if ($currency === null) {
            $currency = $this->mainCurrency;
        }

        if (!key_exists($currency->value, $this->balance)) {
            throw new NotSupportedCurrencyException();
        }

        $balance = 0;
        foreach ($this->balance as $balanceCurrency => $amount) {
            $balance += $this->converter->convert(
                money: new Money(Currency::from($balanceCurrency), $amount),
                currency: $currency
            )->getAmount();
        }

        return new Money($currency, $balance);
    }

    /**
     * @return array<string, int>
     */
    public function getBalanceSummary(): array
    {
        return $this->balance;
    }
}
