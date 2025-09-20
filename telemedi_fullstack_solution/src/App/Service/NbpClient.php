<?php
declare(strict_types=1);

namespace App\Service;

class NbpClient
{
    private const BASE = 'https://api.nbp.pl/api';

    private function getJson(string $url): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $body = curl_exec($ch);
        if ($body === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('NBP API error: ' . $err);
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status >= 400) {
            throw new \RuntimeException('NBP API HTTP ' . $status);
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('NBP API invalid JSON');
        }
        return $decoded;
    }

    public function latestTableA(): array
    {
        $url = self::BASE . '/exchangerates/tables/A/?format=json';
        return $this->getJson($url)[0] ?? [];
    }

    public function midRateOn(string $code, string $date): ?float
    {
        $d = urlencode($date);
        $url = self::BASE . "/exchangerates/rates/A/" . urlencode(strtoupper($code)) . "/$d/?format=json";
        $data = $this->getJson($url);
        if (isset($data['rates'][0]['mid'])) {
            return (float)$data['rates'][0]['mid'];
        }
        return null;
    }

    /**
     * Returns list of [date => mid] for the inclusive date range.
     */
    public function midRatesRange(string $code, string $start, string $end): array
    {
        $url = self::BASE . "/exchangerates/rates/A/" . urlencode(strtoupper($code)) . "/$start/$end/?format=json";
        $data = $this->getJson($url);
        $out = [];
        foreach ($data['rates'] ?? [] as $r) {
            $out[$r['effectiveDate']] = (float)$r['mid'];
        }
        return $out;
    }
}
