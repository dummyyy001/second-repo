<?php

declare (strict_types=1);
namespace WDFQVendorFree\Brick\Math\Exception;

/**
 * Exception thrown when a division by zero occurs.
 */
class DivisionByZeroException extends \WDFQVendorFree\Brick\Math\Exception\MathException
{
    /**
     * @return DivisionByZeroException
     *
     * @psalm-pure
     */
    public static function divisionByZero() : \WDFQVendorFree\Brick\Math\Exception\DivisionByZeroException
    {
        return new self('Division by zero.');
    }
    /**
     * @return DivisionByZeroException
     *
     * @psalm-pure
     */
    public static function modulusMustNotBeZero() : \WDFQVendorFree\Brick\Math\Exception\DivisionByZeroException
    {
        return new self('The modulus must not be zero.');
    }
    /**
     * @return DivisionByZeroException
     *
     * @psalm-pure
     */
    public static function denominatorMustNotBeZero() : \WDFQVendorFree\Brick\Math\Exception\DivisionByZeroException
    {
        return new self('The denominator of a rational number cannot be zero.');
    }
}
