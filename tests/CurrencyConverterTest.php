<?php

namespace Alinoksha\tests;

use Alinoksha\Mcba\CurrencyConverter;
use Alinoksha\Mcba\Enums\Currency;
use Alinoksha\Mcba\Exceptions\NotExistingCurrencyRateException;
use Alinoksha\Mcba\Money;
use PHPUnit\Framework\TestCase;

class CurrencyConverterTest extends TestCase
{
    public function testConversion(): void
    {
        $converter = new CurrencyConverter([
            Currency::Eur->value => [
                Currency::Rub->value => 50,
            ],
        ]);
        $from = new Money(Currency::Eur, 1);
        $to = $converter->convert($from, Currency::Rub);
        $this->assertEquals(Currency::Rub, $to->getCurrency());
        $this->assertEquals(50, $to->getAmount());

        $converter->setConvertRate(Currency::Eur, Currency::Rub, 100);
        $to = $converter->convert($from, Currency::Rub);
        $this->assertEquals(Currency::Rub, $to->getCurrency());
        $this->assertEquals(100, $to->getAmount());

        $this->expectException(NotExistingCurrencyRateException::class);
        $converter->convert($from, Currency::Usd);
    }
}
