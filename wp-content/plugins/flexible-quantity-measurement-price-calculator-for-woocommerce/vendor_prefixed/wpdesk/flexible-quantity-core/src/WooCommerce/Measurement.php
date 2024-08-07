<?php

namespace WDFQVendorFree\WPDesk\Library\FlexibleQuantityCore\WooCommerce;

use WDFQVendorFree\Brick\Math\BigDecimal;
use WDFQVendorFree\Brick\Math\RoundingMode;
class Measurement
{
    /** @var mixed $value the measurement value, int or float */
    private $value;
    /** @var string $unit the measurement unit abbreviation, ie 'sq. in.', 'ml', 'tn', etc */
    private $unit;
    /** @var string $common_unit the common unit for this measurement */
    private $common_unit;
    /** @var string $name the measurement name, one of 'length', 'width', 'height', 'area', 'volume', 'weight' */
    private $name;
    /** @var string $label the measurement label, which is displayed for the customer */
    private $label;
    /** @var boolean $editable whether this measurement is editable in the price calculator */
    private $is_editable;
    /** @var array $options associative array of option value to option label */
    private $options;
    /** @var array All units to their corresponding standard unit */
    private static $normalize_table;
    /** @var array All standard units to all other units */
    private static $conversion_table;
    /** @var int Rounding precision for unit conversion calculations */
    private const CONVERSION_ROUNDING_PRECISION = 15;
    /**
     * __construct function.
     *
     * @param string    $unit        the measurement unit abbreviation, ie 'sq. in.', 'ml', 'tn', etc
     * @param int|float $value       $value the measurement value, int or float. Defaults to 1
     * @param string    $name        optional measurement name, one of 'length', 'width', 'height', 'area', 'volume',
     *                               'weight'
     * @param string    $label       optional label to display on the frontend
     * @param string    $is_editable optional "yes"/"no" whether this measurement is editable in the price calculator.
     *                               Defaults to 'no'
     * @param array     $options     array of options for the frontend
     */
    public function __construct($unit, $value = 1, $name = '', $label = '', $is_editable = 'no', $options = [])
    {
        $this->unit = $unit;
        $this->value = $value;
        $this->name = $name;
        $this->label = $label;
        $this->is_editable = $is_editable;
        $this->options = $options;
    }
    /**
     * Sets the measurement value to $value.  $value must be in the current
     * measurement units
     *
     * @param int|float $value measurement value
     */
    public function set_value($value)
    {
        $this->value = $value;
    }
    /**
     * Get the measurement value optionally converted to the specified unit.
     * If no $unit is supplied, the measurement value is returned in the current
     * measurement units
     *
     * @param string $unit optional unit to return the measurement value in
     *
     * @return int|float the measurement value, int or float
     */
    public function get_value($unit = null)
    {
        return !$unit ? $this->value : self::convert($this->value, $this->unit, $unit);
    }
    /**
     * Gets the measurement value converted (as needed) to the configured common
     * unit, used when deriving compound measurments (ie area, volume).
     *
     * @return int|float the measurement value converted to common units
     */
    public function get_value_common()
    {
        return self::convert($this->value, $this->unit, $this->get_unit_common());
    }
    /**
     * Returns the common unit for this measurement
     *
     * @return string the common unit for this measurement, ie 'sq. ft.' or 'sq m', etc
     * @since 3.0
     */
    public function get_unit_common()
    {
        // default to the standard unit if not set
        if (null === $this->common_unit) {
            $this->common_unit = self::get_standard_unit($this->unit);
        }
        return $this->common_unit;
    }
    /**
     * Sets the common unit to use for this measurement.  If the dimension of
     * the supplied $common_unit is incorrect for this measurement type it will
     * be converted as needed, ie if the measurement type is 'area' and 'ft'
     * is passed for $common_unit it will be converted to 'sq. ft.'
     *
     * @param string $common_unit the common unit, ie 'ft', 'sq. ft.', etc
     *
     * @return string the common unit, which may or may not be the same as $common_unit
     * @since 3.0
     */
    public function set_common_unit($common_unit)
    {
        // ensure the supplied unit is in the correct dimensions, ie 'ft', 'sq. ft.' or 'cu. ft.'
        switch ($this->name) {
            case 'length':
            case 'width':
            case 'height':
                $common_unit = self::to_dimension_unit($common_unit);
                break;
            case 'area':
                $common_unit = self::to_area_unit($common_unit);
                break;
            case 'volume':
                $common_unit = self::to_volume_unit($common_unit);
                break;
        }
        $this->common_unit = self::get_standard_unit($common_unit);
        return $this->common_unit;
    }
    /**
     * Sets the measurement's unit and changes the measurement value if needed
     *
     * @param string $unit the unit to convert to, ie 'sq. in.', 'ml', 'tn', etc
     *
     * @return int|float returns the measurement in the new units
     */
    public function set_unit($unit)
    {
        // convert to $unit and set
        $this->value = self::convert($this->value, $this->unit, $unit);
        $this->unit = $unit;
        // return the value in the new units
        return $this->value;
    }
    /**
     * Get the measurement unit
     *
     * @return string the measurement value
     */
    public function get_unit()
    {
        return $this->unit;
    }
    /**
     * Get the measurement unit for display on the frontend
     *
     * @return string the measurement unit label for display on the frontend
     * @since 3.0.1
     */
    public function get_unit_label()
    {
        /**
         * Filter the measurement unit frontend label
         *
         * @param string      $label       The measurement unit frontend label.
         * @param Measurement $measurement instance of this class
         *
         * @since 3.0.1
         */
        return \apply_filters('fq_price_calculator_unit_label', $this->unit, $this);
    }
    /**
     * Get the measurement name, one of 'length', 'width', 'height', 'area', 'volume', 'weight'
     *
     * @return string the measurement name
     * @see get_type()
     */
    public function get_name()
    {
        return $this->name;
    }
    /**
     * Gets the type of measurement, one of 'dimension', 'area', 'volume' or 'weight'
     *
     * @return string type of measurement
     */
    public function get_type()
    {
        switch ($this->get_name()) {
            case 'length':
            case 'width':
            case 'height':
                return 'dimension';
            default:
                return $this->get_name();
        }
    }
    /**
     * Returns true if this measurement is editable by the customer in the frontend
     * calculator
     *
     * @return boolean true if this measurement is editable in the price calculator
     */
    public function is_editable()
    {
        return 'yes' === $this->is_editable;
    }
    /**
     * Returns fixed value or null
     *
     * @return mixed null if is not editable or fixed value
     */
    public function get_default_value()
    {
        return $this->value;
    }
    /**
     * Returns the measurement frontend label
     *
     * @return string measurement label
     */
    public function get_label()
    {
        /**
         * Filter the measurement frontend label
         *
         * @param string      $label       The measurement frontend label
         * @param Measurement $measurement instance of this class
         *
         * @since 3.4.0
         */
        return \apply_filters('fq_price_calculator_label', $this->label, $this);
    }
    /**
     * Returns an array of options for the measurement for the frontend.
     *
     * @return array of option values
     * @since 3.0
     */
    public function get_options()
    {
        /**
         * Filters the array of options for the measurement
         *
         * @param array       $options     array of option values
         * @param Measurement $measurement instance of this class
         *
         * @since 3.10.1
         */
        return \apply_filters('fq_price_calculator_measurement_options', $this->options, $this);
    }
    /**
     * Helper function to convert the measurement value to the given unit, or the
     * default unit
     *
     * @param int|float $value   the value to convert
     * @param string    $unit    the unit, ie 'sq. in.', 'ml', 'tn', etc
     * @param string    $to_unit the unit to convert to, ie 'sq. in.', 'ml', 'tn', etc.
     *
     * @return int|float returns the converted measurement value
     * @since 3.0
     */
    public static function convert($value, $unit, $to_unit)
    {
        if ($unit === $to_unit) {
            return $value;
        }
        if (\is_string($value)) {
            $value = (float) $value;
        }
        // all units to their corresponding standard unit
        $normalize_table = self::get_normalize_table();
        // conversions from our standard units to all other units
        $conversion_table = self::get_conversion_table();
        // convert from $unit to the corresponding standard unit
        if (isset($normalize_table[$unit])) {
            $factor = $normalize_table[$unit]['factor'];
            if (isset($normalize_table[$unit]['inverse']) && $normalize_table[$unit]['inverse']) {
                $value = \WDFQVendorFree\Brick\Math\BigDecimal::of($value)->dividedBy($factor, self::CONVERSION_ROUNDING_PRECISION, \WDFQVendorFree\Brick\Math\RoundingMode::HALF_EVEN)->toFloat();
            } else {
                $value = \WDFQVendorFree\Brick\Math\BigDecimal::of($value)->multipliedBy($factor)->toFloat();
            }
            $unit = $normalize_table[$unit]['unit'];
        }
        // convert from the standard unit to $to_unit
        if (isset($conversion_table[$unit][$to_unit])) {
            $factor = $conversion_table[$unit][$to_unit]['factor'];
            if (isset($conversion_table[$unit][$to_unit]['inverse']) && $conversion_table[$unit][$to_unit]['inverse']) {
                $value = \WDFQVendorFree\Brick\Math\BigDecimal::of($value)->dividedBy($factor, self::CONVERSION_ROUNDING_PRECISION, \WDFQVendorFree\Brick\Math\RoundingMode::HALF_EVEN)->toFloat();
            } else {
                $value = \WDFQVendorFree\Brick\Math\BigDecimal::of($value)->multipliedBy($factor)->toFloat();
            }
        }
        return $value;
    }
    /**
     * Converts a string that could be a number or a fraction to a numerical float.
     *
     * @param string|int|float $value the value to convert, it could be a number (integer, float) or a fraction
     *
     * @return string|float will output a float when using dot as decimal separator, a numerical string in all other
     *                      cases
     * @since 3.4.0
     */
    public static function convert_to_float($value)
    {
        $fraction = null;
        $decimal_sep = \trim(\wc_get_price_decimal_separator());
        // we got a measurement fraction, like "2 1/2" (two and half inches, for example)
        if (\preg_match('#(\\d+)\\s+(\\d+)\\/(\\d+)#', $value, $matches)) {
            $fraction = (float) $matches[3] !== 0 ? $matches[1] + $matches[2] / $matches[3] : $matches[1];
            // we got a simple fraction, like "3/4" (three fourths of a gallon, for example)
        } elseif (\preg_match('#(\\d+)\\/(\\d+)#', $value, $matches)) {
            $fraction = (float) $matches[2] !== 0 ? $matches[1] / $matches[2] : 0;
        }
        if (null !== $fraction) {
            // account for comma or alternative separators, for the correct value to return
            if ('.' !== $decimal_sep) {
                $fraction = \str_replace('.', $decimal_sep, (string) $fraction);
            }
            $value = (string) $fraction;
        }
        // if not using the dot as decimal separator, let this be handled by JS, otherwise caste as float
        return '.' !== $decimal_sep ? $value : (float) $value;
    }
    /**
     * Returns the standard unit for $unit.  Ie, for 'sq. in.', 'sq. ft.',
     * or 'acs' this will return 'sq. ft.', while 'sq mm' will return 'sq m'
     *
     * @param string $unit the unit
     *
     * @return string the standard unit or null if none is found
     * @since 3.0
     */
    public static function get_standard_unit($unit)
    {
        $normalize_table = self::get_normalize_table();
        if (isset($normalize_table[$unit]['unit'])) {
            return $normalize_table[$unit]['unit'];
        }
        return null;
    }
    /**
     * Returns a conversion table which has conversion factors to normalize
     * any given measurement unit to one of only a few others, to simplify
     * the challenge of unit conversion
     *
     * @return array of conversion factors
     * @since 3.0
     */
    public static function get_normalize_table()
    {
        if (null === self::$normalize_table) {
            self::$normalize_table = \apply_filters('fq_price_calculator_normalize_table', ['in' => ['factor' => 12, 'unit' => 'ft', 'inverse' => \true], 'ft' => ['factor' => 1, 'unit' => 'ft'], 'yd' => ['factor' => 3, 'unit' => 'ft'], 'mi' => ['factor' => 5280, 'unit' => 'ft'], 'mm' => ['factor' => 0.001, 'unit' => 'm'], 'cm' => ['factor' => 0.01, 'unit' => 'm'], 'm' => ['factor' => 1, 'unit' => 'm'], 'km' => ['factor' => 1000, 'unit' => 'm'], 'sq. in.' => ['factor' => 144, 'unit' => 'sq. ft.', 'inverse' => \true], 'sq. ft.' => ['factor' => 1, 'unit' => 'sq. ft.'], 'sq. yd.' => ['factor' => 9, 'unit' => 'sq. ft.'], 'acs' => ['factor' => 43560, 'unit' => 'sq. ft.'], 'sq. mi.' => ['factor' => 27878400, 'unit' => 'sq. ft.'], 'sq mm' => ['factor' => 1.0E-6, 'unit' => 'sq m'], 'sq cm' => ['factor' => 0.0001, 'unit' => 'sq m'], 'sq m' => ['factor' => 1, 'unit' => 'sq m'], 'ha' => ['factor' => 10000, 'unit' => 'sq m'], 'sq km' => ['factor' => 1000000, 'unit' => 'sq m'], 'fl. oz.' => ['factor' => 1, 'unit' => 'fl. oz.'], 'cup' => ['factor' => 8, 'unit' => 'fl. oz.'], 'pt' => ['factor' => 16, 'unit' => 'fl. oz.'], 'qt' => ['factor' => 32, 'unit' => 'fl. oz.'], 'gal' => ['factor' => 128, 'unit' => 'fl. oz.'], 'cu. in.' => ['factor' => 1728, 'unit' => 'cu. ft.', 'inverse' => \true], 'cu. ft.' => ['factor' => 1, 'unit' => 'cu. ft.'], 'cu. yd.' => ['factor' => 27, 'unit' => 'cu. ft.'], 'ml' => ['factor' => 1.0E-6, 'unit' => 'cu m'], 'cu cm' => ['factor' => 1.0E-6, 'unit' => 'cu m'], 'l' => ['factor' => 0.001, 'unit' => 'cu m'], 'cu m' => ['factor' => 1, 'unit' => 'cu m'], 'oz' => ['factor' => 16, 'unit' => 'lbs', 'inverse' => \true], 'lbs' => ['factor' => 1, 'unit' => 'lbs'], 'tn' => ['factor' => 2000, 'unit' => 'lbs'], 'g' => ['factor' => 0.001, 'unit' => 'kg'], 'kg' => ['factor' => 1, 'unit' => 'kg'], 't' => ['factor' => 1000, 'unit' => 'kg'], 'item' => ['factor' => 1, 'unit' => \__('item', 'flexible-quantity-measurement-price-calculator-for-woocommerce')]]);
        }
        return self::$normalize_table;
    }
    /**
     * Returns a conversion table which has conversion factors from our limited
     * "standard" units to all other units.
     * The rather verbose tables found in this method and the associated
     * get_normalize_table() method are used to provide as simple and accurate
     * of a measurement unit conversion as possible.  Previously all units were
     * converted to a much smaller number of "standard" units, ie all lengths
     * were converted to inches first and then to the final unit.  This led to
     * rounding issues when converting between units of different systems of
     * measurement.  For instance, 2 m * 2 m => 3.999999999 m
     * With this more complex setup, conversions within a single system of
     * measurement will be accurate and correct, and while the rounding issue
     * will still remain when converting from say feet to meters, this should
     * be a very uncommon (or hopefully non-existant) occurrence.
     *
     * @return array of conversion factors
     * @since 3.0
     */
    public static function get_conversion_table()
    {
        if (null === self::$conversion_table) {
            self::$conversion_table = \apply_filters('fq_price_calculator_conversion_table', ['ft' => ['in' => ['factor' => 12], 'ft' => ['factor' => 1], 'yd' => ['factor' => 3, 'inverse' => \true], 'mi' => ['factor' => 5280, 'inverse' => \true], 'mm' => ['factor' => 304.8], 'cm' => ['factor' => 30.48], 'm' => ['factor' => 0.3048], 'km' => ['factor' => 0.0003048]], 'l' => ['ml' => ['factor' => 1000], 'l' => ['factor' => 1]], 'm' => ['mm' => ['factor' => 1000], 'cm' => ['factor' => 100], 'm' => ['factor' => 1], 'km' => ['factor' => 0.001], 'in' => ['factor' => 39.3701], 'ft' => ['factor' => 3.28084], 'yd' => ['factor' => 1.09361], 'mi' => ['factor' => 0.000621371]], 'sq. ft.' => ['sq. in.' => ['factor' => 144], 'sq. ft.' => ['factor' => 1], 'sq. yd.' => ['factor' => 9, 'inverse' => \true], 'acs' => ['factor' => 43560, 'inverse' => \true], 'sq. mi.' => ['factor' => 27878400, 'inverse' => \true], 'sq mm' => ['factor' => 92903.03999999999], 'sq cm' => ['factor' => 929.0304], 'sq m' => ['factor' => 0.092903], 'sq km' => ['factor' => 9.290299999999999E-8]], 'sq m' => ['sq mm' => ['factor' => 1000000], 'sq cm' => ['factor' => 10000], 'sq m' => ['factor' => 1], 'ha' => ['factor' => 0.0001], 'sq km' => ['factor' => 1.0E-6], 'sq. in.' => ['factor' => 1550], 'sq. ft.' => ['factor' => 10.7639], 'sq. yd.' => ['factor' => 1.19599], 'acs' => ['factor' => 0.000247105], 'sq. mi.' => ['factor' => 3.86102E-7]], 'fl. oz.' => ['fl. oz.' => ['factor' => 1], 'cup' => ['factor' => 8, 'inverse' => \true], 'pt' => ['factor' => 16, 'inverse' => \true], 'qt' => ['factor' => 32, 'inverse' => \true], 'gal' => ['factor' => 128, 'inverse' => \true], 'cu. in.' => ['factor' => 231 / 128], 'cu. ft.' => ['factor' => 0.00104438], 'cu. yd.' => ['factor' => 3.86807163E-5], 'ml' => ['factor' => 29.5735], 'cu cm' => ['factor' => 29.5735], 'l' => ['factor' => 0.0295735], 'cu m' => ['factor' => 2.95735E-5]], 'cu. ft.' => ['fl. oz.' => ['factor' => 957.506], 'cup' => ['factor' => 119.688], 'pt' => ['factor' => 59.8442], 'qt' => ['factor' => 29.9221], 'gal' => ['factor' => 7.48052], 'cu. in.' => ['factor' => 1728], 'cu. ft.' => ['factor' => 1], 'cu. yd.' => ['factor' => 27, 'inverse' => \true], 'ml' => ['factor' => 28316.8466], 'cu cm' => ['factor' => 28316.8466], 'l' => ['factor' => 28.3168466], 'cu m' => ['factor' => 0.0283168466]], 'cu m' => ['ml' => ['factor' => 1000000], 'cu cm' => ['factor' => 1000000], 'l' => ['factor' => 1000], 'cu m' => ['factor' => 1], 'fl. oz.' => ['factor' => 33814], 'cup' => ['factor' => 4226.75], 'pt' => ['factor' => 2113.38], 'qt' => ['factor' => 1056.69], 'gal' => ['factor' => 264.172], 'cu. in.' => ['factor' => 61023.7], 'cu. ft.' => ['factor' => 35.3147], 'cu. yd.' => ['factor' => 1.30795062]], 'lbs' => ['oz' => ['factor' => 16], 'lbs' => ['factor' => 1], 'tn' => ['factor' => 2000, 'inverse' => \true], 'g' => ['factor' => 453.592], 'kg' => ['factor' => 0.453592], 't' => ['factor' => 0.000453592]], 'kg' => ['g' => ['factor' => 1000], 'kg' => ['factor' => 1], 't' => ['factor' => 0.001], 'oz' => ['factor' => 35.274], 'lbs' => ['factor' => 2.20462], 'tn' => ['factor' => 0.00110231]], 'item' => ['item' => ['factor' => 1]]]);
        }
        return self::$conversion_table;
    }
    /**
     * Given a unit, returns the corresponding dimensional unit.  For instance
     * a unit of 'sq. ft.' or 'cu. ft.' returns 'ft'
     *
     * @param string $unit the unit, it 'sq. ft.'
     *
     * @return string corresponding dimensional unit, ie 'ft'
     * @since 3.0
     */
    public static function to_dimension_unit($unit)
    {
        switch ($unit) {
            case 'mm':
            case 'sq mm':
                return 'mm';
            case 'cm':
            case 'sq cm':
            case 'ml':
            case 'cu cm':
                return 'cm';
            case 'm':
            case 'sq m':
            case 'cu m':
            case 'ha':
                return 'm';
            // special case: hectare
            case 'km':
            case 'sq km':
                return 'km';
            case 'in':
            case 'sq. in.':
            case 'cu. in.':
                return 'in';
            case 'ft':
            case 'sq. ft.':
            case 'cu. ft.':
            case 'acs':
                return 'ft';
            // special case: acres
            case 'yd':
            case 'sq. yd.':
            case 'cu. yd.':
                return 'yd';
            case 'mi':
            case 'sq. mi.':
                return 'mi';
            default:
                return $unit;
        }
    }
    /**
     * Given a unit, returns the corresponding area unit.  For instance,
     * a $unit of 'in' returns 'sq. in.'
     *
     * @param string $unit ie 'in'
     *
     * @return string corresponding area unit, ie 'sq. in.'
     */
    public static function to_area_unit($unit)
    {
        switch ($unit) {
            case 'mm':
            case 'sq mm':
                return 'sq mm';
            case 'cm':
            case 'sq cm':
            case 'ml':
            case 'cu cm':
                return 'sq cm';
            case 'm':
            case 'sq m':
            case 'cu m':
                return 'sq m';
            case 'km':
            case 'sq km':
                return 'sq km';
            case 'in':
            case 'sq. in.':
            case 'cu. in.':
                return 'sq. in.';
            case 'ft':
            case 'sq. ft.':
            case 'cu. ft.':
                return 'sq. ft.';
            case 'yd':
            case 'sq. yd.':
            case 'cu. yd.':
                return 'sq. yd.';
            case 'mi':
            case 'sq. mi.':
                return 'sq. mi.';
            // hectare and acres
            case 'ha':
                return 'ha';
            case 'acs':
                return 'acs';
            default:
                return \apply_filters('fq_price_calculator_to_area_unit', null, $unit);
        }
    }
    /**
     * Given a unit, returns the corresponding volume unit.  For instance,
     * a $unit of 'in' or 'sq. in.' returns 'cu. in.'
     *
     * @param string $unit ie 'in'
     *
     * @return string corresponding volume unit, ie 'cu. in.'
     */
    public static function to_volume_unit($unit)
    {
        switch ($unit) {
            case 'cm':
            case 'sq cm':
            case 'ml':
            case 'cu cm':
                return 'ml';
            case 'm':
            case 'sq m':
            case 'cu m':
                return 'cu m';
            case 'in':
            case 'sq. in.':
            case 'cu. in.':
                return 'cu. in.';
            case 'ft':
            case 'sq. ft.':
            case 'cu. ft.':
                return 'cu. ft.';
            case 'yd':
            case 'sq. yd.':
            case 'cu. yd.':
                return 'cu. yd.';
            // no length or area units
            case 'l':
                return 'l';
            case 'gal':
                return 'gal';
            case 'qt':
                return 'qt';
            case 'pt':
                return 'pt';
            case 'cup':
                return 'cup';
            case 'fl. oz.':
                return 'fl. oz.';
            default:
                return \apply_filters('fq_price_calculator_to_volume_unit', null, $unit);
        }
    }
    /**
     * Compare units, disregarding measurement type (length/area/volume/weight)
     * meaning that 'in' will compare as true to 'in', 'sq. in.' and 'cu. in.',
     * etc.
     *
     * @param string $unit1 first unit to compare
     * @param string $unit2 second unit to compare
     *
     * @return bool if the units are the 'same'
     */
    public static function compare_units($unit1, $unit2)
    {
        $compare = \false;
        // straight comparison
        if ($unit1 === $unit2) {
            $compare = \true;
        }
        // match found as area units (ie perhaps 'in' and 'sq. in.')  This also covers volume units
        if (self::to_area_unit($unit1) && self::to_area_unit($unit2) && self::to_area_unit($unit1) === self::to_area_unit($unit2)) {
            $compare = \true;
        }
        return $compare;
    }
}
