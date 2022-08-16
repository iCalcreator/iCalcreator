<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
use function strtoupper;

/**
 * iCalcreator utility/support class
 *
 * @since  2.41.36 - 2022-04-03
 */
class Util
{
    /**
     * @var string  misc. values
     * @deprecated
     */
    public static string $LCvalue       = 'value';

    /**
     * @var string
     * @deprecated
     */
    public static string $LCparams      = 'params';

    /**
     * @var string
     */
    public static string $CRLF          = "\r\n";

    /**
     * @var string
     */
    public static string $COMMA         = ',';

    /**
     * @var string
     */
    public static string $COLON         = ':';

    /**
     * @var string
     */
    public static string $SEMIC         = ';';

    /**
     * @var string
     */
    public static string $MINUS         = '-';

    /**
     * @var string
     */
    public static string $PLUS          = '+';

    /**
     * @var string
     */
    public static string $SP0           = '';

    /**
     * @var string
     */
    public static string $SP1           = ' ';

    /**
     * @var string
     */
    public static string $ZERO          = '0';

    /**
     * @var string
     */
    public static string $DOT           = '.';

    /**
     * @var string
     */
    public static string $SLASH         = '/';

    /**
     * Return bool true if array key is isset and not empty
     *
     * @param mixed  $array
     * @param null|string $key
     * @return bool
     * @since  2.26.14 - 2019-01-28
     */
    public static function issetAndNotEmpty( mixed $array = null, ? string $key = null) : bool
    {
        if( empty( $array ) || ! is_array( $array )) {
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
    public static function issetKeyAndEquals( mixed $base, string $key, string $value ) : bool
    {
        if( empty( $base ) ||
            ! is_array( $base ) ||
            ! array_key_exists( $key, $base )) {
            return false;
        }
        return ( $value === $base[$key] );
    }

    /**
     * Assert value is integer (and in range)
     *
     * @param mixed  $value
     * @param string $propName
     * @param null|int $rangeMin
     * @param null|int $rangeMax
     * @return void
     * @throws InvalidArgumentException
     * @since  2.27.14 - 2019-02-19
     */
    public static function assertInteger(
        mixed $value,
        string $propName,
        ? int $rangeMin = null,
        ? int $rangeMax = null
    ) : void
    {
        static $ERR1 = '%s expects integer value, got %s';
        static $ERR2 = '%s value %s not in range (%d-%d)';
        if( ! is_scalar( $value ) || ! ctype_digit( (string) $value )) {
            throw new InvalidArgumentException(
                sprintf( $ERR1, $propName, var_export( $value, true ))
            );
        }
        $value    = (int) $value;
        $rangeMin = $rangeMin ?? $value;
        $rangeMax = $rangeMax ?? $value;
        if(( $rangeMin > $value ) || ( $rangeMax < $value )) {
            throw new InvalidArgumentException(
                sprintf( $ERR2, $propName, $value, $rangeMin, $rangeMax )
            );
        }
    }

    /**
     * Assert value is string (i.e. scalar, return string)
     *
     * @param mixed  $value
     * @param string $propName
     * @return string
     * @throws InvalidArgumentException
     * @since  2.29.14 - 2019-09-03
     */
    public static function assertString( mixed $value, string $propName ) : string
    {
        static $ERR1 = '%s expects string value, got (%s) %s';
        if( ! is_scalar( $value )) {
            throw new InvalidArgumentException(
                sprintf( $ERR1, $propName, gettype( $value ), var_export( $value, true ))
            );
        }
        return (string) $value;
    }

    /**
     * Assert value in enumeration
     *
     * @param mixed  $value
     * @param string[] $enumeration - all upper case
     * @param string $propName
     * @return void
     * @throws InvalidArgumentException
     * @since  2.27.2 - 2019-01-04
     */
    public static function assertInEnumeration( mixed  $value, array  $enumeration, string $propName ) : void
    {
        static $ERR = 'Invalid %s value : %s';
        if( ! in_array( strtoupper( $value ), $enumeration, true )) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $propName, var_export( $value, true ))
            );
        }
    }
}
