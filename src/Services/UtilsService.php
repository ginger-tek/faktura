<?php

namespace Faktura\Services;

class UtilsService
{
  public static function toUSD(float $amount): string
  {
    return (new \NumberFormatter('en-US', \NumberFormatter::CURRENCY))->formatCurrency($amount, 'USD');
  }
}