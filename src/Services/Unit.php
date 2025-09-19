<?php

namespace LaravelEnso\UnitConversion\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelEnso\UnitConversion\Contracts\Unit as Contract;
use LaravelEnso\UnitConversion\Exceptions\Conversion as ConversionException;
use LaravelEnso\UnitConversion\Exceptions\Unit as UnitException;
use ReflectionClass;

abstract class Unit implements Contract
{
    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function from(Contract | string $argument, ?int $precision = 2): string
    {
        return $argument instanceof Contract
            ? self::fromContract($argument, $precision)
            : self::fromExpression($argument, $precision);
    }

    private static function fromContract(Contract $unit,  ?int $precision = 2, bool $verified = false): string
    {
        if (!$verified) {
            self::checkCompatibility($unit);
        }

        return Conversion::handle($unit, static::class, $precision);
    }

    private static function fromExpression(string $expression,  ?int $precision = 2): string
    {
        Expression::validate($expression);

        [$value, $symbol] = explode(' ', $expression);

        $namespace = (new ReflectionClass(static::class))->getNamespaceName();
        $suffix = Str::of($namespace)->explode('\\')->slice(-2)->implode('/');

        $unit = Collection::wrap(File::files(__DIR__ . "/../{$suffix}"))
            ->map(fn ($file) => File::name($file))
            ->map(fn ($unit) => "{$namespace}\\{$unit}")
            ->first(fn ($unit) => $unit::symbol() === $symbol);

        if ($unit === null) {
            throw UnitException::invalid($symbol);
        }

        return self::fromContract(new $unit($value), $precision, true);
    }

    private static function checkCompatibility(Contract $unit): void
    {
        $fromNamespace = (new ReflectionClass($unit))->getNamespaceName();
        $toNamespace = (new ReflectionClass(static::class))->getNamespaceName();

        if ($fromNamespace !== $toNamespace) {
            throw ConversionException::incompatible($unit::symbol(), static::symbol());
        }
    }
}
