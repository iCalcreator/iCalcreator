<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software iCalcreator.
 *            The above copyright, link, package and version notices,
 *            this licence notice and the invariant [rfc5545] PRODID result use
 *            as implemented and invoked in iCalcreator shall be included in
 *            all copies or substantial portions of the iCalcreator.
 *
 *            iCalcreator is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            iCalcreator is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 */
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator\Util;

use InvalidArgumentException;
use function array_key_exists;
use function in_array;
use function is_array;
use function strtolower;
use function strtoupper;
use function ucfirst;

/**
 * iCalcreator utility/support class
 *
 * @since  2.27.2 - 2018-12-21
 */
class Util
{
    /**
     * @var string  misc. values
     */
    public static $LCvalue       = 'value';
    public static $LCparams      = 'params';
    public static $ISLOCALTIME   = 'ISLOCALTIME';
    public static $CRLF          = "\r\n";
    public static $COMMA         = ',';
    public static $COLON         = ':';
    public static $SEMIC         = ';';
    public static $MINUS         = '-';
    public static $PLUS          = '+';
    public static $SP0           = '';
    public static $SP1           = ' ';
    public static $ZERO          = '0';
    public static $DOT           = '.';
    public static $SLASH         = '/';

    /**
     * Return bool true if compType is in array
     *
     * @param string $compType   component name
     * @param array  $compList   list of components
     * @return bool
     * @since  2.26 - 2018-11-03
     */
    public static function isCompInList( string $compType, array $compList ) : bool
    {
        if( empty( $compType )) {
            return false;
        }
        return in_array( ucfirst( strtolower( $compType )), $compList);
    }

    /**
     * Return bool true if property is in array
     *
     * @param string $propName   property name
     * @param array  $propList   list of properties
     * @return bool
     * @since  2.26 - 2018-11-04
     */
    public static function isPropInList( string $propName, array $propList ) : bool
    {
        return in_array( strtoupper( $propName ), $propList);
    }

    /**
     * Return bool true if array key is isset and not empty
     *
     * @param mixed  $array
     * @param string $key
     * @return bool
     * @since  2.26.14 - 2019-01-28
     */
    public static function issetAndNotEmpty( $array = null, $key = null) : bool
    {
        if( empty( $array ) ||
            ! is_array( $array ) ||
            ! array_key_exists( $key, $array )) {
            return false;
        }
        return ( isset( $array[$key] ) && ! empty( $array[$key] ));
    }

    /**
     * Return bool true if array key is set and equals value
     *
     * @param mixed  $base
     * @param string $key
     * @param string $value
     * @return bool
     * @since  2.26.14 - 2019-03-01
     */
    public static function issetKeyAndEquals( $base, string $key, string $value ) : bool
    {
        if( empty( $base ) ||
            ! is_array( $base ) ||
            ! array_key_exists( $key, $base )) {
            return false;
        }
        return ( $value == $base[$key] );
    }

    /**
     * Assert value is integer
     *
     * @param mixed  $value
     * @param string $propName
     * @param int $rangeMin
     * @param int $rangeMax
     * @throws InvalidArgumentException
     * @since  2.27.14 - 2019-02-19
     */
    public static function assertInteger(
        $value,
        string $propName,
        $rangeMin = null,
        $rangeMax = null
    ) {
        static $ERR1 = '%s expects integer value, got %s';
        static $ERR2 = '%s value %s not in range (%d-%d)';
        if( ! is_scalar( $value ) || ! ctype_digit( (string) $value )) {
            throw new InvalidArgumentException(
                sprintf( $ERR1, $propName, var_export( $value, true ))
            );
        }
        if( ( ! is_null( $rangeMin ) && ( $rangeMin > $value )) ||
            ( ! is_null( $rangeMax )) && ( $rangeMax < $value )) {
            throw new InvalidArgumentException(
                sprintf( $ERR2, $propName, $value, $rangeMin, $rangeMax )
            );
        }

    }

    /**
     * Assert value is string
     *
     * @param mixed  $value
     * @param string $propName
     * @return string
     * @throws InvalidArgumentException
     * @since  2.29.14 - 2019-09-03
     */
    public static function assertString( $value, string $propName ) : string
    {
        static $ERR1 = '%s expects string value, got (%s) %s';
        if( ! is_scalar( $value )) {
            throw new InvalidArgumentException(
                sprintf(
                    $ERR1,
                    $propName,
                    gettype( $value ),
                    var_export( $value, true )
                )
            );
        }
        return  (string) $value;
    }

    /**
     * Assert value in enumeration
     *
     * @param mixed  $value
     * @param array  $enumeration - all upper case
     * @param string $propName
     * @throws InvalidArgumentException
     * @since  2.27.2 - 2019-01-04
     */
    public static function assertInEnumeration(
        $value,
        array $enumeration,
        string $propName
    ) {
        static $ERR = 'Invalid %s value : %s';
        if( ! in_array( strtoupper( $value ), $enumeration )) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $propName, var_export( $value, true ))
            );
        }
    }
}

