<?php

namespace Thoughtco\StatamicABTester\Support;

class StatisticalSignificance
{
    /**
     * Compare two variants using a two-proportion z-test.
     *
     * Returns null when there is insufficient data to perform the test
     * (e.g. zero hits, or identical zero conversion rates).
     *
     * @return array{z_score: float, p_value: float, confidence: float, is_significant: bool}|null
     */
    public static function calculate(int $hitsA, int $conversionsA, int $hitsB, int $conversionsB): ?array
    {
        if ($hitsA === 0 || $hitsB === 0) {
            return null;
        }

        $pPool = ($conversionsA + $conversionsB) / ($hitsA + $hitsB);

        // Cannot compute a meaningful SE when pooled rate is 0 or 1
        if ($pPool <= 0 || $pPool >= 1) {
            return null;
        }

        $se = sqrt($pPool * (1 - $pPool) * (1 / $hitsA + 1 / $hitsB));

        if ($se == 0) {
            return null;
        }

        $p1 = $conversionsA / $hitsA;
        $p2 = $conversionsB / $hitsB;
        $zScore = ($p2 - $p1) / $se;

        $pValue = 2 * (1 - self::normalCdf(abs($zScore)));
        $confidence = (1 - $pValue) * 100;

        return [
            'z_score' => round($zScore, 4),
            'p_value' => round($pValue, 4),
            'confidence' => round($confidence, 2),
            'is_significant' => $pValue < 0.05,
        ];
    }

    private static function normalCdf(float $z): float
    {
        return 0.5 * (1 + self::erf($z / sqrt(2)));
    }

    /**
     * Error function approximation (Abramowitz & Stegun 7.1.26).
     * Maximum error: 1.5×10⁻⁷
     */
    private static function erf(float $x): float
    {
        $sign = $x >= 0 ? 1 : -1;
        $x = abs($x);

        $t = 1.0 / (1.0 + 0.3275911 * $x);
        $poly = $t * (0.254829592 + $t * (-0.284496736 + $t * (1.421413741 + $t * (-1.453152027 + $t * 1.061405429))));
        $y = 1.0 - $poly * exp(-$x * $x);

        return $sign * $y;
    }
}
