<?php

namespace LaravelEnso\UnitConversion\Services;

use LaravelEnso\Helpers\Services\Decimals;
use LaravelEnso\UnitConversion\Contracts\Unit;

class Conversion
{
    public static function handle(Unit $from, string $to, ?int $precision = 2): string
    {
        $baseValue = Decimals::mul($from->value(), $from::factor(), 20);

        return Decimals::div($baseValue, $to::factor(), $precision);
    }
}
