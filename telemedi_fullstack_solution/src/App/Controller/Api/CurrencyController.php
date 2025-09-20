<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\CurrencyService;
use App\Service\NbpClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CurrencyController extends AbstractController
{
    public function __construct(private NbpClient $nbp, private CurrencyService $svc) {}

    /** @Route("/api/currencies", methods={"GET"}) */
    public function currencies(): JsonResponse
    {
        return $this->json([ 'currencies' => $this->svc->getSupported() ]);
    }

    /** @Route("/api/rates", methods={"GET"}) */
    public function latest(Request $req): JsonResponse
    {
        $date = $req->query->get('date');
        $codes = $this->svc->getSupported();
        $result = [];
        if ($date) {
            foreach ($codes as $c) {
                try {
                    $mid = $this->nbp->midRateOn($c, $date);
                    if ($mid !== null) {
                        $result[$c] = $this->svc->computeRates($c, $mid);
                        $result[$c]['date'] = $date;
                    }
                } catch (\Throwable $e) {
                    $result[$c] = ['error' => $e->getMessage()];
                }
            }
        } else {
            // Use latest table A
            $table = $this->nbp->latestTableA();
            $map = [];
            foreach (($table['rates'] ?? []) as $row) {
                $map[strtoupper($row['code'])] = (float)$row['mid'];
            }
            foreach ($codes as $c) {
                if (isset($map[$c])) {
                    $result[$c] = $this->svc->computeRates($c, $map[$c]);
                    $result[$c]['date'] = $table['effectiveDate'] ?? null;
                }
            }
        }
        return $this->json(['date' => $result[array_key_first($result)]['date'] ?? null, 'rates' => $result]);
    }

    /** @Route("/api/rates/{code}/history", methods={"GET"}) */
    public function history(string $code, Request $req): JsonResponse
    {
        $date = $req->query->get('date'); // YYYY-MM-DD
        if (!$date) {
            $date = (new \DateTimeImmutable('today'))->format('Y-m-d');
        }
        $end = new \DateTimeImmutable($date);
        $start = $end->modify('-20 days'); // fetch more (NBP has only business days), we'll later limit to 14 items
        $range = $this->nbp->midRatesRange($code, $start->format('Y-m-d'), $end->format('Y-m-d'));
        // take last 14 entries by date
        ksort($range);
        $slice = array_slice($range, -14, 14, true);
        $out = [];
        foreach ($slice as $d => $mid) {
            $calc = $this->svc->computeRates($code, (float)$mid);
            $out[] = ['date' => $d] + $calc;
        }
        return $this->json(['code' => strtoupper($code), 'history' => $out]);
    }
}
