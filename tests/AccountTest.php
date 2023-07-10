<?php

namespace Alinoksha\tests;

use Alinoksha\Mcba\Account;
use Alinoksha\Mcba\Bank;
use Alinoksha\Mcba\CurrencyConverter;
use Alinoksha\Mcba\Enums\Currency;
use Alinoksha\Mcba\Exceptions\DisableCurrencyWithMoneyException;
use Alinoksha\Mcba\Exceptions\DisableMainCurrencyException;
use Alinoksha\Mcba\Exceptions\NotSupportedCurrencyException;
use Alinoksha\Mcba\Exceptions\WithdrawMoneyException;
use Alinoksha\Mcba\Money;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    public function testAddCurrency(): void
    {
        $account = $this->createAccount();

        $account->addCurrency(Currency::Rub);
        $this->assertEquals([Currency::Rub->value], $account->getSupportedCurrenciesList());

        $account->addCurrency(Currency::Eur);
        $this->assertEquals([Currency::Rub->value, Currency::Eur->value], $account->getSupportedCurrenciesList());
    }

    public function testSetMainCurrency(): void
    {
        $account = $this->createAccount();

        $this->assertEquals(Currency::Rub, $account->getMainCurrency());
        $account->addCurrency(Currency::Eur);
        $account->setMainCurrency(Currency::Eur);
        $this->assertEquals(Currency::Eur, $account->getMainCurrency());
    }

    public function testSetInvalidCurrencyAsMain(): void
    {
        $account = $this->createAccount();

        $this->expectException(NotSupportedCurrencyException::class);
        $account->setMainCurrency(Currency::Eur);
    }

    public function testDisableCurrency(): void
    {
        $account = $this->createAccount();

        $account->addCurrency(Currency::Eur);
        $account->setMainCurrency(Currency::Eur);
        $account->disableCurrency(Currency::Rub);
        $this->assertEquals([Currency::Eur->value], $account->getSupportedCurrenciesList());
    }

    public function testDisableMainCurrency(): void
    {
        $account = $this->createAccount();

        $account->addCurrency(Currency::Eur);
        $account->setMainCurrency(Currency::Eur);
        $this->expectException(DisableMainCurrencyException::class);
        $account->disableCurrency(Currency::Eur);
    }

    public function testDisableCurrencyWithMoney(): void
    {
        $account = $this->createAccount();

        $account->addCurrency(Currency::Eur);
        $account->depositMoney(new Money(Currency::Eur, 100));
        $this->expectException(DisableCurrencyWithMoneyException::class);
        $account->disableCurrency(Currency::Eur);
    }

    public function testWithdrawMoney(): void
    {
        $account = $this->createAccount();

        $account->addCurrency(Currency::Eur);
        $account->depositMoney(new Money(Currency::Eur, 150));
        $account->withdrawMoney(Currency::Eur, 100);
        $this->assertEquals([Currency::Rub->value => 0, Currency::Eur->value => 50], $account->getBalanceSummary());
    }

    public function testWithdrawMoneyInNotSupportedCurrency(): void
    {
        $account = $this->createAccount();

        $this->expectException(NotSupportedCurrencyException::class);
        $account->withdrawMoney(Currency::Eur, 100);
    }

    public function testWithdrawInvalidMoneyAmount(): void
    {
        $account = $this->createAccount();

        $account->addCurrency(Currency::Eur);
        $this->expectException(WithdrawMoneyException::class);
        $account->withdrawMoney(Currency::Eur, 100);
    }

    public function testDepositMoney(): void
    {
        $account = $this->createAccount();

        $account->addCurrency(Currency::Eur);
        $account->depositMoney(new Money(Currency::Eur, 100));
        $this->assertEquals([Currency::Rub->value => 0, Currency::Eur->value => 100], $account->getBalanceSummary());
    }

    public function testDepositMoneyInNotSupportedCurrency(): void
    {
        $account = $this->createAccount();

        $this->expectException(NotSupportedCurrencyException::class);
        $account->depositMoney(new Money(Currency::Eur, 100));
    }

    public function testGetBalance(): void
    {
        $account = $this->createAccount();

        $account->addCurrency(Currency::Eur);
        $account->depositMoney(new Money(Currency::Rub, 100));
        $account->depositMoney(new Money(Currency::Eur, 10));
        $this->assertEquals(new Money(Currency::Rub, 600), $account->getBalance());
        $this->assertEquals(new Money(Currency::Eur, 12), $account->getBalance(Currency::Eur));
    }

    public function testGetBalanceInNotSupportedCurrency(): void
    {
        $account = $this->createAccount();

        $this->expectException(NotSupportedCurrencyException::class);
        $account->getBalance(Currency::Eur);
    }

    public function testGetBalanceSummary(): void
    {
        $account = $this->createAccount();
        $this->assertEquals([Currency::Rub->value => 0], $account->getBalanceSummary());

        $account->addCurrency(Currency::Eur);
        $account->depositMoney(new Money(Currency::Eur, 10));
        $this->assertEquals([Currency::Rub->value => 0, Currency::Eur->value => 10], $account->getBalanceSummary());
    }

    private function createAccount(): Account
    {
        return (new Bank(
            new CurrencyConverter([
                Currency::Eur->value => [
                    Currency::Rub->value => 50,
                ],
                Currency::Rub->value => [
                    Currency::Eur->value => 1/50,
                ],
            ])
        ))->createAccount();
    }
}
