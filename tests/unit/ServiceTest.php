<?php

use LaravelEnso\Helpers\Services\Decimals;
use LaravelEnso\UnitConversion\Exceptions\Expression;
use LaravelEnso\UnitConversion\Exceptions\Unit;
use LaravelEnso\UnitConversion\Electricity\Power;
use LaravelEnso\UnitConversion\Electricity\Units\KiloWatt;
use LaravelEnso\UnitConversion\Electricity\Units\Watt;
use LaravelEnso\UnitConversion\Energy\Energy;
use LaravelEnso\UnitConversion\Energy\Units\Joule;
use LaravelEnso\UnitConversion\Energy\Units\Kilocalorie;
use LaravelEnso\UnitConversion\Length\Length;
use LaravelEnso\UnitConversion\Length\Units\Kilometer;
use LaravelEnso\UnitConversion\Length\Units\Meter;
use LaravelEnso\UnitConversion\Length\Units\Millimeter;
use LaravelEnso\UnitConversion\Mass\Mass;
use LaravelEnso\UnitConversion\Mass\Units\Gram;
use LaravelEnso\UnitConversion\Mass\Units\Kilogram;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ServiceTest extends TestCase
{
    #[Test]
    public function can_convert_from_unit()
    {
        $result = Length::from(new Meter(2))->to(Millimeter::class);

        $this->assertTrue(Decimals::eq('2000', $result));
    }

    #[Test]
    public function can_convert_from_expression()
    {
        $result = Length::from('2 m')->to(Millimeter::class);

        $this->assertTrue(Decimals::eq('2000', $result));
    }

    #[Test]
    public function can_convert_mass_units()
    {
        $result = Mass::from('3 kg')->to(Gram::class);

        $this->assertTrue(Decimals::eq('3000', $result));
    }

    #[Test]
    public function can_convert_energy_units()
    {
        $result = Energy::from('1 kcal')->to(Joule::class);

        $this->assertTrue(Decimals::eq('4184', $result));
    }

    #[Test]
    public function can_convert_power_units()
    {
        $result = Power::from(new KiloWatt(2))->to(Watt::class);

        $this->assertTrue(Decimals::eq('2000', $result));
    }

    #[Test]
    public function respects_requested_precision_for_service_conversion()
    {
        $result = Length::from('1 mm')->to(Kilometer::class);

        $this->assertSame('0.00', $result);
    }

    #[Test]
    public function accepts_decimal_expression_values()
    {
        $result = Mass::from('1.5 kg')->to(Gram::class);

        $this->assertTrue(Decimals::eq('1500', $result));
    }

    #[Test]
    public function cant_convert_from_invalid_unit()
    {
        $unit = new Gram(2);

        $message = Unit::invalid($unit::class)->getMessage();

        $this->expectException(Unit::class);
        $this->expectExceptionMessage($message);

        Length::from($unit)->to(Meter::class);
    }

    #[Test]
    public function cant_convert_from_invalid_expression()
    {
        $expression = '100 kg 5';
        $message = Expression::invalid($expression)->getMessage();

        $this->expectException(Expression::class);
        $this->expectExceptionMessage($message);

        Length::from($expression)->to(Meter::class);
    }

    #[Test]
    public function cant_convert_from_invalid_symbol()
    {
        $message = Unit::invalid('kx')->getMessage();

        $this->expectException(Unit::class);
        $this->expectExceptionMessage($message);

        Length::from('100 kx')->to(Meter::class);
    }

    #[Test]
    public function rejects_malformed_expressions_with_missing_space_or_extra_tokens()
    {
        $invalidExpressions = ['2m', '100 kg 5', '10  kg', 'kg 10'];

        foreach ($invalidExpressions as $expression) {
            try {
                Length::from($expression)->to(Meter::class);
                $this->fail("Expected malformed expression '{$expression}' to fail");
            } catch (Expression $exception) {
                $this->assertSame(
                    Expression::invalid($expression)->getMessage(),
                    $exception->getMessage()
                );
            }
        }
    }
}
