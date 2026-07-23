<?php

namespace App;

class Utils
{
  public static function to_currency(?float $amount = 0, ?bool $round = false): string
  {
    $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::CURRENCY);
    if ($round) {
      $amount = round($amount ?? 0);
      $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
    }
    return $formatter->formatCurrency($amount ?? 0, 'USD');
  }

  public static function render_invoice(string $template, array $data = []): string
  {
    return preg_replace_callback('#{{\s*((?<obj>\w+)\.)?(?<field>[\w\_]+)\s*(?:\|\s*(?<mod>\w+)\s*)?}}#', function ($matches) use (&$data) {
      [$obj, $field, $mod] = [$matches['obj'] ?? null, $matches['field'], $matches['mod'] ?? null];
      $val = match (true) {
        $obj == 'settings' => $data['settings'][$field] ?? '',
        $field == 'current_date' => date('n/j/Y'),
        $field == 'invoice_items' => join("\r\n", [
          "|Summary|Unit Price|Quantity|Total Amount|",
          "|:---|---:|---:|---:|",
          ...array_map(
            fn($item) => "|{$item->summary}|" . Utils::to_currency($item->unit_price) . "|{$item->quantity}|" . Utils::to_currency($item->total_amount) . "|",
            Crud::list('v_invoice_items', ['invoice_id' => $data['invoice']->id])
          ),
          "| | |**Labor**|" . Utils::to_currency($data['invoice']->labor_amount) . "|",
          "| | |**Total**|**" . Utils::to_currency($data['invoice']->total_amount) . "**|"
        ]),
        default => $data[$obj]->$field ?? ''
      };
      if ($mod) {
        $val = match ($mod) {
          'date' => date('n/j/Y', strtotime($val)),
          'upper' => strtoupper($val),
          'lower' => strtolower($val),
          'currency' => Utils::to_currency($val),
          default => $val
        };
      }
      return $val;
    }, $template);
  }
}