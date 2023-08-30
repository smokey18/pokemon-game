<?php

namespace App\Services;

class DamageCalculatorService
{
    public function calculateDamage($movePower, $attackerLevel, $attackerStat, $defenderStat, $typeMultiplier)
    {
        $critical = 1;

        $damage = (
            (
                (
                    (2 * $attackerLevel / 5 + 2) *
                    $movePower *
                    $attackerStat / $defenderStat
                ) / 50 + 2
            ) *
            $critical *
            $typeMultiplier *
            rand(217, 255) / 255
        );

        return floor($damage);
    }
}
