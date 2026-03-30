<?php

use LaravelEnso\Helpers\Services\Decimals;
use LaravelEnso\UnitConversion\Exceptions\Conversion;
use LaravelEnso\UnitConversion\Exceptions\Expression;
use LaravelEnso\UnitConversion\Exceptions\Unit;
use LaravelEnso\UnitConversion\Length\Units\Meter;
use LaravelEnso\UnitConversion\Length\Units\Millimeter;
use LaravelEnso\UnitConversion\Mass\Units\Gram;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UnitTest extends TestCase
{
    #[Test]
    public function can_convert_from_unit()
    {
        $result = Millimeter::from(new Meter(2));

        $this->assertTrue(Decimals::eq('2000', $result));
    }

    #[Test]
    public function can_convert_from_expression()
    {
        $result = Millimeter::from('2 m');

        $this->assertTrue(Decimals::eq('2000', $result));
    }

    #[Test]
    public function cant_convert_from_incompatible_unit()
    {
        $unit = new Meter(2);

        $message = Conversion::incompatible($unit::symbol())->getMessage();

        $this->expectException(Conversion::class);
        $this->expectExceptionMessage($message);

        Gram::from($unit);
    }

    #[Test]
    public function cant_convert_from_invalid_expression()
    {
        $expression = '100 kg 5';
        $message = Expression::invalid($expression)->getMessage();

        $this->expectException(Expression::class);
        $this->expectExceptionMessage($message);

        Gram::from($expression);
    }

    #[Test]
    public function cant_convert_from_invalid_symbol()
    {
        $message = Unit::invalid('kx')->getMessage();

        $this->expectException(Unit::class);
        $this->expectExceptionMessage($message);

        Gram::from('100 kx');
    }
}
