<?php

namespace App\Services;

class FinanceCalculationService
{
    private const ASRAT_PERCENTAGE = 0.10;

    public function calculateAsrat(float|string $income): array
    {
        $amount = round((float) $income, 2);
        $asrat = round($amount * self::ASRAT_PERCENTAGE, 2);

        return [
            'asrat' => number_format($asrat, 2, '.', ''),
            'remaining' => number_format($amount - $asrat, 2, '.', ''),
        ];
    }
}
