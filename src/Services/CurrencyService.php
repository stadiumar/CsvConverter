<?php

namespace App\Services;

class CurrencyService
{
    const GBP = 'GBP';
    const USD = 'USD';
    
    /**
     * Retrieves the converted amount from one currency to another.
     *
     * @param float $amount The amount to be converted.
     * @param string $fromCurrency The currency to convert from.
     * @param string $toCurrency The currency to convert to.
     * @return float|null The converted amount, or null if the conversion failed.
     */
    public  function getConvertedAmmount(float $amount, string $fromCurrency, string $toCurrency): ?float
    {
        $url  = "https://www.google.com/search?q=" . $fromCurrency . "+to+" . $toCurrency;
        $get = file_get_contents($url);

        if (!$get) return null;

        $getFormatted = preg_split('/\D\s(.*?)\s=\s/', $get);

        if (!$getFormatted) return null;

        $exhangeRate = (float)substr($getFormatted[1], 0, 5);
        $convertedAmount = $amount * $exhangeRate;

        return $convertedAmount;
    }
}