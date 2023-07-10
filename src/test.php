<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Alinoksha\Mcba\Bank;
use Alinoksha\Mcba\CurrencyConverter;
use Alinoksha\Mcba\Enums\Currency;
use Alinoksha\Mcba\Money;

$converter = new CurrencyConverter([
    Currency::Eur->value => [
        Currency::Rub->value => 80,
        Currency::Usd->value => 1,
    ],
    Currency::Usd->value => [
        Currency::Rub->value => 70,
        Currency::Eur->value => 1,
    ],
    Currency::Rub->value => [
        Currency::Usd->value => 1/70,
        Currency::Eur->value => 1/80,
    ],
]);

echo '#1.---------------------------------------------------------' . PHP_EOL;
// Счет = Банк->ОткрытьНовыйСчет()
$account = (new Bank($converter))->createAccount();
// Счет->ДобавитьВалюту(RUB)
$account->addCurrency(Currency::Rub);
// Счет->ДобавитьВалюту(EUR)
$account->addCurrency(Currency::Eur);
// Счет->ДобавитьВалюту(USD)
$account->addCurrency(Currency::Usd);
// Счет->УстановитьОсновнуюВалюту(RUB)
$account->setMainCurrency(Currency::Rub);
// Счет->СписокПоддеживаемыхВалют() // [RUB, EUR, USD]
var_dump($account->getSupportedCurrenciesList());
// Счет->ПополнитьБаланс(RUB(1000))
$account->depositMoney(new Money(Currency::Rub, 1000));
// Счет->ПополнитьБаланс(EUR(50))
$account->depositMoney(new Money(Currency::Eur, 50));
// Счет->ПополнитьБаланс(USD(40))
$account->depositMoney(new Money(Currency::Usd, 40));

echo '#2.---------------------------------------------------------' . PHP_EOL;
// Счет->ПолучитьБаланс() => xxxxx RUB
var_dump($account->getBalance());
// Счет->ПолучитьБаланс(USD) => xxxxx USD
var_dump($account->getBalance(Currency::Usd));
// Счет->ПолучитьБаланс(EUR) => xxxxx EUR
var_dump($account->getBalance(Currency::Eur));

echo '#3.---------------------------------------------------------' . PHP_EOL;
// Счет->ПополнитьБаланс(RUB(1000))
$account->depositMoney(new Money(Currency::Rub, 1000));
// Счет->ПополнитьБаланс(EUR(50))
$account->depositMoney(new Money(Currency::Eur, 50));
// Счет->СписатьСБаланса(USD(10))
$account->depositMoney(new Money(Currency::Usd, 10));

var_dump($account->getBalanceSummary());

echo '#4.---------------------------------------------------------' . PHP_EOL;
// EUR->УстановитьКурсОбменаВалюты(RUR, 150)
$converter->setConvertRate(Currency::Eur, Currency::Rub, 150);
// USD->УстановитьКурсОбменаВалюты(RUR, 100)
$converter->setConvertRate(Currency::Usd, Currency::Rub, 100);

echo '#5.---------------------------------------------------------' . PHP_EOL;
// Счет->ПолучитьБаланс() => xxxxx RUB
var_dump($account->getBalance());

echo '#6.---------------------------------------------------------' . PHP_EOL;
// Счет->УстановитьОсновнуюВалюту(EUR)
$account->setMainCurrency(Currency::Eur);
// Счет->ПолучитьБаланс() => xxx EUR
var_dump($account->getBalance());

echo '#7.---------------------------------------------------------' . PHP_EOL;
// ДенежныеСредства = Счет->СписатьСБаланса(RUB(1000))
$rub = $account->withdrawMoney(Currency::Rub, 100);
$eur = $converter->convert($rub, Currency::Eur);
var_dump($eur);
// Счет->ПополнитьБаланс(EUR(ДенежныеСредства))
$account->depositMoney($eur);
// Счет->ПолучитьБаланс() => xxx EUR
var_dump($account->getBalance());

echo '#8.---------------------------------------------------------' . PHP_EOL;
// EUR->УстановитьКурсОбменаВалюты(RUR, 120)
$converter->setConvertRate(Currency::Eur, Currency::Rub, 120);

echo '#9.---------------------------------------------------------' . PHP_EOL;
// Счет->ПолучитьБаланс() => xxx EUR
var_dump($account->getBalance());

echo '#10.--------------------------------------------------------' . PHP_EOL;
// Счет->УстановитьОсновнуюВалюту(RUB)
$account->setMainCurrency(Currency::Rub);
// Счет->ОтключитьВалюту(EUR)
var_dump($account->getBalanceSummary());
$eur = $account->withdrawMoney(Currency::Eur, 101);
$account->depositMoney($converter->convert($eur, Currency::Rub));
$account->disableCurrency(Currency::Eur);
// Счет->ОтключитьВалюту(USD)
$usd = $account->withdrawMoney(Currency::Usd, 50);
$account->depositMoney($converter->convert($usd, Currency::Rub));
$account->disableCurrency(Currency::Usd);
// Счет->СписокПоддеживаемыхВалют() // [RUB]
var_dump($account->getSupportedCurrenciesList());
// Счет->ПолучитьБаланс() => xxxxx RUB
var_dump($account->getBalance());
