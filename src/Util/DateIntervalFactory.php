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

use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;

use function floor;
use function strlen;
use function substr;
use function trim;

/**
 * iCalcreator DateInterval utility/support class
 *
 * @see https://en.wikipedia.org/wiki/Iso8601
 * @since 2.40.11 2022-01-15
 */
class DateIntervalFactory
{
    /**
     * @var string  duration keys etc
     */
    private static string $Y = 'Y';

    /**
     * @var string  dito
     */
    private static string $T = 'T';

    /**
     * @var string  dito
     */
    private static string $W = 'W';

    /**
     * @var string  dito
     */
    private static string $D = 'D';

    /**
     * @var string  dito
     */
    private static string $H = 'H';

    /**
     * @var string  dito
     */
    private static string $M = 'M';

    /**
     * @var string  dito
     */
    private static string $S = 'S';

    /**
     * @var string  dito
     */
    private static string $PT0H0M0S = 'PT0H0M0S';

    /**
     * @var string  dito
     */
    private static string $s = 's';

    /**
     * @var string  dito
     */
    private static string $i = 'i';

    /**
     * @var string  dito
     */
    private static string $h = 'h';

    /**
     * @var string  dito
     */
    private static string $d = 'd';

    /**
     * @var string  dito
     */
    private static string $m = 'm';

    /**
     * @var string  dito
     */
    private static string $y = 'y';

    /**
     * @var string  dito
     */
    private static string $invert = 'invert';

    /**
     * @var string
     */
    public static string $P         = 'P';

    /**
     * Return new DateTimeZone object instance
     *
     * @param string $dateIntervalString
     * @return DateInterval
     * @throws InvalidArgumentException
     * @since  2.27.8 - 2019-01-12
     */
    public static function factory( string $dateIntervalString ) : DateInterval
    {
        return self::assertDateIntervalString( $dateIntervalString );
    }

    /**
     * Assert DateIntervalString
     *
     * @param string $dateIntervalString
     * @return DateInterval
     * @throws InvalidArgumentException
     * @since  2.27.8 - 2019-01-12
     */
    public static function assertDateIntervalString(
        string $dateIntervalString
    ) : DateInterval
    {
        static $ERR = 'Invalid DateInterval \'%s\'';
        try {
            $dateInterval = new DateInterval( $dateIntervalString );
        }
        catch( Exception $e ) {
            throw new InvalidArgumentException(
                sprintf( $ERR, $dateIntervalString ),
                $e->getCode(),
                $e
            );
        }
        return $dateInterval;
    }

    /**
     * Return bool true if string and duration
     *
     * @param mixed  $value
     * @return bool
     * @since  2.29.22 - 2020-08-22
     */
    public static function isStringAndDuration( mixed $value ) : bool
    {
        static $PREFIXARR = [ 'P', '+', '-' ];
        if( ! is_string( $value )) {
            return false;
        }
        $value = trim( $value );
        $value = StringFactory::trimTrailNL( $value );
        return (( 3 <= strlen( $value )) &&
            ( in_array( $value[0], $PREFIXARR, true ) ));
    }

    /**
     * Return value with removed opt. prefix +/-
     *
     * @param string  $value
     * @return string
     * @since  2.16.7 - 2018-11-26
     */
    public static function removePlusMinusPrefix( string $value ) : string
    {
        if( self::hasPlusMinusPrefix( $value )) {
            $value = substr( $value, 1 );
        }
        return $value;
    }

    /**
     * Return bool true if string has a leading +/-
     *
     * @param string  $value
     * @return bool
     * @since  2.16.14 - 2019-02-18
     */
    public static function hasPlusMinusPrefix( string $value ) : bool
    {
        static $PLUSMINUSARR  = [ '+', '-' ];
        return ( in_array( $value[0], $PLUSMINUSARR ));
    }

    /**
     * Return DateInterval as string
     *
     * @param DateInterval $dateInterval
     * @param bool         $showOptSign
     * @return string
     * @since  2.16.14 - 2019-02-15
     */
    public static function dateInterval2String(
        DateInterval $dateInterval,
        ? bool $showOptSign = false
    ) : string
    {
        $dateIntervalArr = (array) $dateInterval;
        $result          = self::$P;
        if( empty( $dateIntervalArr[self::$y] ) &&
            empty( $dateIntervalArr[self::$m] ) &&
            empty( $dateIntervalArr[self::$h] ) &&
            empty( $dateIntervalArr[self::$i] ) &&
            empty( $dateIntervalArr[self::$s] ) &&
          ! empty( $dateIntervalArr[self::$d] ) &&
            ( 0 === ( $dateIntervalArr[self::$d] % 7 ))) {
            $result .= (int) floor( $dateIntervalArr[self::$d] / 7 ) .
                self::$W;
            return ( $showOptSign && ( 0 < $dateIntervalArr[self::$invert] ))
                ? Util::$MINUS . $result : $result;
        }
        if( 0 < $dateIntervalArr[self::$y] ) {
            $result .= $dateIntervalArr[self::$y] . self::$Y;
        }
        if( 0 < $dateIntervalArr[self::$m] ) {
            $result .= $dateIntervalArr[self::$m] . self::$M;
        }
        if( 0 < $dateIntervalArr[self::$d] ) {
            $result .= $dateIntervalArr[self::$d] . self::$D;
        }
        $hourIsSet = ! empty( $dateIntervalArr[self::$h] );
        $minIsSet  = ! empty( $dateIntervalArr[self::$i] );
        $secIsSet  = ! empty( $dateIntervalArr[self::$s] );
        if( ! $hourIsSet && ! $minIsSet && ! $secIsSet ) {
            if( self::$P === $result ) {
                $result = self::$PT0H0M0S;
            }
            return ( $showOptSign && ( 0 < $dateIntervalArr[self::$invert] ))
                ? Util::$MINUS . $result : $result;
        }
        $result .= self::$T;
        if( $hourIsSet ) {
            $result .= $dateIntervalArr[self::$h] . self::$H;
        }
        if( $minIsSet ) {
            $result .= $dateIntervalArr[self::$i] . self::$M;
        }
        if( $secIsSet ) {
            $result .= $dateIntervalArr[self::$s] . self::$S;
        }
        return ( $showOptSign && ( 0 < $dateIntervalArr[self::$invert] ))
            ? Util::$MINUS . $result : $result;
    }

    /**
     * Return conformed DateInterval
     *
     * @param DateInterval $dateInterval
     * @return DateInterval
     * @throws Exception  on DateInterval create error
     * @since  2.27.14 - 2019-03-09
     */
    public static function conformDateInterval( DateInterval $dateInterval ) : DateInterval
    {
        $dateIntervalArr = (array) $dateInterval;
        if( 60 <= $dateIntervalArr[self::$s] ) {
            $dateIntervalArr[self::$i] +=
                (int) floor( $dateIntervalArr[self::$s] / 60 );
            $dateIntervalArr[self::$s] %= 60;
        }
        if( 60 <= $dateIntervalArr[self::$i] ) {
            $dateIntervalArr[self::$h] +=
                (int) floor( $dateIntervalArr[self::$i] / 60 );
            $dateIntervalArr[self::$i] %= 60;
        }
        if( 24 <= $dateIntervalArr[self::$h] ) {
            $dateIntervalArr[self::$d] +=
                (int) floor( $dateIntervalArr[self::$h] / 24 );
            $dateIntervalArr[self::$h] %= 24;
        }
        return self::DateIntervalArr2DateInterval( $dateIntervalArr );
    }

    /**
     * Modify DateTime from DateInterval
     *
     * @param DateTime     $dateTime
     * @param DateInterval $dateInterval
     * @return void
     * @since  2.29.2 - 2019-06-20
     * @tofo error mgnt
     */
    public static function modifyDateTimeFromDateInterval(
        DateTime $dateTime,
        DateInterval $dateInterval
    ) : void
    {
        static $YEAR  = 'year';
        static $MONTH = 'month';
        static $DAY   = 'day';
        static $HOUR  = 'hour';
        static $MIN   = 'minute';
        static $SEC   = 'second';
        static $KEYS  = [];
        if( empty( $KEYS )) {
            $KEYS = [
                self::$y => $YEAR,
                self::$m => $MONTH,
                self::$d => $DAY,
                self::$h => $HOUR,
                self::$i => $MIN,
                self::$s => $SEC
            ];
        }
        $dateIntervalArr = (array) $dateInterval;
        $operator        = ( 0 < $dateIntervalArr[self::$invert] )
            ? Util::$MINUS
            : Util::$PLUS;
        foreach( $KEYS as $diKey => $dtKey ) {
            if( 0 < $dateIntervalArr[$diKey] ) {
                $dateTime->modify(
                    self::getModifyString ( $operator, $dateIntervalArr[$diKey], $dtKey )
                );
            }
        }
    }

    /**
     * @param string $operator
     * @param int|string $number
     * @param string $unit
     * @return string
     */
    private static function getModifyString ( string $operator, int|string $number, string $unit ) : string
    {
        static $MONTH = 'month';
        $suffix = ( $MONTH !== $unit ) ? self::getOptPluralSuffix( $number ) : null;
        return $operator . $number . Util::$SP1 . $unit . $suffix;
    }

    /**
     * @param int|string $number
     * @return string
     */
    private static function getOptPluralSuffix ( int|string $number ) : string
    {
        static $PLS = 's';
        return ( 1 < $number ) ? $PLS : Util::$SP0;
    }

    /**
     * Get DateInterval from (DateInterval) array
     *
     * @param array $dateIntervalArr
     * @return DateInterval
     * @throws Exception  on DateInterval create error
     * @since  2.27.2 - 2018-12-21
     */
    public static function DateIntervalArr2DateInterval( array $dateIntervalArr ) : DateInterval
    {
        static $P0D = 'P0D';
        $dateInterval = new DateInterval( $P0D );
        foreach( $dateIntervalArr as $key => $value ) {
            $dateInterval->{$key} = $value;
        }
        return $dateInterval;
    }
}
