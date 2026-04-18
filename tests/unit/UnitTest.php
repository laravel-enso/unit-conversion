<?php

use LaravelEnso\Helpers\Services\Decimals;
use LaravelEnso\UnitConversion\Exceptions\Conversion;
use LaravelEnso\UnitConversion\Exceptions\Expression;
use LaravelEnso\UnitConversion\Exceptions\Unit;
use LaravelEnso\UnitConversion\Electricity\Units\KiloWatt;
use LaravelEnso\UnitConversion\Electricity\Units\Watt;
use LaravelEnso\UnitConversion\Energy\Units\Joule;
use LaravelEnso\UnitConversion\Energy\Units\Kilocalorie;
use LaravelEnso\UnitConversion\Length\Units\Centimeter;
use LaravelEnso\UnitConversion\Length\Units\Kilometer;
use LaravelEnso\UnitConversion\Length\Units\Meter;
use LaravelEnso\UnitConversion\Length\Units\Millimeter;
use LaravelEnso\UnitConversion\Mass\Units\Gram;
use LaravelEnso\UnitConversion\Mass\Units\Kilogram;
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
    public function can_convert_length_mass_energy_and_power_units_directly()
    {
        $this->assertTrue(Decimals::eq('200000', Centimeter::from(new Kilometer(2))));
        $this->assertTrue(Decimals::eq('3000', Gram::from(new Kilogram(3))));
        $this->assertTrue(Decimals::eq('1.00', Kilocalorie::from(new Joule('4184'))));
        $this->assertTrue(Decimals::eq('2.00', KiloWatt::from(new Watt('2000'))));
    }

    #[Test]
    public function respects_requested_precision_for_direct_unit_conversion()
    {
        $result = Kilometer::from(new Millimeter('1'), 8);

        $this->assertSame('0.00000100', $result);
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

    #[Test]
    public function exposes_expected_label_symbol_and_factor_for_core_units()
    {
        $this->assertSame('meter', Meter::label());
        $this->assertSame('m', Meter::symbol());
        $this->assertSame(1.0, Meter::factor());

        $this->assertSame('kilogram', Kilogram::label());
        $this->assertSame('kg', Kilogram::symbol());
        $this->assertSame(1.0, Kilogram::factor());

        $this->assertSame('kilocalorie', Kilocalorie::label());
        $this->assertSame('kcal', Kilocalorie::symbol());
        $this->assertSame(4184.0, Kilocalorie::factor());

        $this->assertSame('kilowatt', KiloWatt::label());
        $this->assertSame('kW', KiloWatt::symbol());
        $this->assertSame(1000.0, KiloWatt::factor());
    }
}
