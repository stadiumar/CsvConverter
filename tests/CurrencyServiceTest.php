<?php

namespace App\Tests;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Services\CurrencyService;

class CurrencyServiceTest extends KernelTestCase
{
    /**
     * @var  CurrencyService
     */
    private $currencyService;

    protected function setUp(): void
    {
        $this->currencyService = new CurrencyService();
    }

    /** @test */
    public function testConvertionOnePound()
    {
        $onePound = $this->currencyService->getConvertedAmmount(1, 'GBP', 'USD');

        $this->assertIsFloat($onePound);
        $this->assertNotEquals(null, $onePound);
    }

    /** @test */
    public function testConvertionOfZero()
    {
        $zeroPound = $this->currencyService->getConvertedAmmount(0, 'GBP', 'USD');

        $this->assertIsFloat($zeroPound);
        $this->assertEquals(null, $zeroPound);
    }

    /** @test */
    public function testConvertionWithArgumentsStartingWithCurrences()
    {
        $onePound = $this->currencyService->getConvertedAmmount(1, 'GBPjAD', 'kkjkUSDTR');

        $this->assertIsFloat($onePound);
        $this->assertNotEquals(null, $onePound);
    }

    /** @test */
    public function testConvertionWithRandomArguments2()
    {
        $oneRandom = $this->currencyService->getConvertedAmmount(1, 'dkjhfla', 'alerjg');
        $this->assertEquals(null, $oneRandom);
    }
}