<?php
declare(strict_types=1);

namespace App\Service;

class CurrencyService
{
    /** @var string[] */
    private array $supported = ['EUR','USD','CZK','IDR','BRL'];

    public function getSupported(): array
    {
        return $this->supported;
    }

    public function computeRates(string $code, float $mid): array
    {
        $code = strtoupper($code);
        if (in_array($code, ['EUR','USD'], true)) {
            return [
                'buy' => round($mid - 0.15, 4),
                'sell' => round($mid + 0.11, 4),
                'mid' => round($mid, 4),
            ];
        }
        return [
            'buy' => null,
            'sell' => round($mid + 0.20, 4),
            'mid' => round($mid, 4),
        ];
    }
}
