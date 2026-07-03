<?php

namespace Faktura\Services;

class UtilsService
{
  public static function toCurrency(float $amount): string
  {
    return (new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::CURRENCY))->formatCurrency($amount, 'USD');
  }
}