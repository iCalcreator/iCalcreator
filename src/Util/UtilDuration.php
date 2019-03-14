<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.26.8
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator\Util;

use DateInterval;
use DateTime;
use Exception;

use function array_key_exists;
use function ctype_digit;
use function date;
use function explode;
use function floor;
use function mktime;
use function strcasecmp;
use function strlen;
use function strtoupper;
use function substr;
use function trim;

/**
 * iCalcreator duration utility/support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26.8 - 2018-12-12
 */
class UtilDuration
{
    /**
     * Class constant
     */
    const INTERVAL_ISO8601 = 'P%yY%mM%dDT%hH%iM%sS';

    /**
     * @var string  iCal TRIGGER param keywords
     * @static
     */
    public static $RELATED      = 'RELATED';
    public static $START        = 'START';
    public static $END          = 'END';
    public static $RELATEDSTART = 'relatedStart';
    public static $BEFORE       = 'before';

    /**
     * @var string  duration keys etc
     * @access private
     * @static
     */
    private static $Y = 'Y';
    private static $T = 'T';
    private static $W = 'W';
    private static $D = 'D';
    private static $H = 'H';
    private static $M = 'M';
    private static $S = 'S';
    private static $PT0H0M0S = 'PT0H0M0S';

    /**
     * @var string  misc
     * @static
     */
    public static $P         = 'P';
    public static $PREFIXARR = [ 'P', '+', '-' ];

    /**
     * Return DateInterval as string
     *
     * @param DateInterval $dateInterval
     * @param bool         $showOptSign
     * @return string
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.16.7 - 2018-11-26
     */
    public static function dateInterval2String( DateInterval $dateInterval, $showOptSign=false ) {
        $result  = UtilDuration::$P;
        if( 0 < $dateInterval->y ) {
            $result .= $dateInterval->y . UtilDuration::$Y;
        }
        if( 0 < $dateInterval->m ) {
            $result .= $dateInterval->m . UtilDuration::$M;
        }
        if( 0 < $dateInterval->d ) {
            if( empty( $dateInterval->y ) && empty( $dateInterval->m ) && ( 0 == ( $dateInterval->d % 7 ))) {
                $result .= (int) floor( $dateInterval->d / 7 ) . UtilDuration::$W;
            }
            else {
                $result .= $dateInterval->d . UtilDuration::$D;
            }
        }
        if( empty( $dateInterval->h ) && empty( $dateInterval->i ) && empty( $dateInterval->s )) {
            if( UtilDuration::$P == $result ) {
                $result = UtilDuration::$PT0H0M0S;
            }
            return ( $showOptSign && ( 0 < $dateInterval->invert )) ? Util::$MINUS . $result : $result;
        }
        $result .= UtilDuration::$T;
        if( 0 < $dateInterval->h ) {
            $result .= $dateInterval->h . UtilDuration::$H;
        }
        if( 0 < $dateInterval->i ) {
            $result .= $dateInterval->i . UtilDuration::$M;
        }
        if( 0 < $dateInterval->s ) {
            $result .= $dateInterval->s . UtilDuration::$S;
        }
        return ( $showOptSign && ( 0 < $dateInterval->invert )) ? Util::$MINUS . $result : $result;
    }

    /**
     * Return conform DateInterval
     *
     * @param DateInterval $dateInterval
     * @return DateInterval
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.16.7 - 2018-11-27
     * @todo error mgnt
     */
    public static function conformDateInterval( DateInterval $dateInterval ) {
        static $NOW = 'now';
        try {
            $dateTime1 = new DateTime( $NOW );
        }
        catch( Exception $e ) {
            return $dateInterval; // todo error mgnt
        }
        $dateTime2 = clone $dateTime1;
        UtilDuration::modifyDateTimeFromDateInterval( $dateTime2, $dateInterval );
        $dateInterval2 = $dateTime1->diff( $dateTime2 );
        return ( false !== $dateInterval2 ) ? $dateInterval2 : $dateInterval;
    }

    /**
     * Modify DateTime from DateInterval
     *
     * @param DateTime     $dateTime
     * @param DateInterval $dateInterval
     * @access private
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-01
     */
    private static function modifyDateTimeFromDateInterval(
        DateTime     $dateTime,
        DateInterval $dateInterval ) {
        static $PLS = 's';
        $operator   = ( 0 < $dateInterval->invert ) ? Util::$MINUS : Util::$PLUS;
        if( 0 < $dateInterval->y ) {
            $plural = ( 1 < $dateInterval->y ) ? $PLS : Util::$SP0;
            $dateTime->modify( $operator . $dateInterval->y . Util::$SP1 . Util::$LCYEAR . $plural );
        }
        if( 0 < $dateInterval->m ) {
            $plural = ( 1 < $dateInterval->m ) ? $PLS : Util::$SP0;
            $dateTime->modify( $operator . $dateInterval->m . Util::$SP1 . Util::$LCMONTH . $plural );
        }
        if( 0 < $dateInterval->d ) {
            $plural = ( 1 < $dateInterval->d ) ? $PLS : Util::$SP0;
            $dateTime->modify( $operator . $dateInterval->d . Util::$SP1 . Util::$LCDAY . $plural );
        }
        if( 0 < $dateInterval->h ) {
            $plural = ( 1 < $dateInterval->h ) ? $PLS : Util::$SP0;
            $dateTime->modify( $operator . $dateInterval->h . Util::$SP1 . Util::$LCHOUR . $plural );
        }
        if( 0 < $dateInterval->i ) {
            $plural = ( 1 < $dateInterval->i ) ? $PLS : Util::$SP0;
            $dateTime->modify( $operator . $dateInterval->i . Util::$SP1 . Util::$LCMIN . $plural );
        }
        if( 0 < $dateInterval->s ) {
            $plural = ( 1 < $dateInterval->s ) ? $PLS : Util::$SP0;
            $dateTime->modify( $operator . $dateInterval->s . Util::$SP1 . Util::$LCSEC . $plural );
        }
    }

    /**
     * Modify DateTime from DateInterval
     *
     * @param array $dateIntervalArr
     * @return DateInterval
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-01
     */
    public static function DateIntervalArr2DateInterval( array $dateIntervalArr ) {
        try {
            $dateInterval = new DateInterval( 'P0D' );
        }
        catch( Exception $e ) { // ??
            // todo
        }
        foreach( $dateIntervalArr as $key => $value ) {
            $dateInterval->{$key} = $value;
        }
        return $dateInterval;
    }

    /**
     * Return datetime array (in internal format) for startdate + DateInterval
     *
     * @param array $startDate
     * @param DateInterval $dateInterval
     * @return array, date format
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-11-26
     */
    public static function dateInterval2date( array $startDate, DateInterval $dateInterval ) {
        static $FMT = 'Y-m-d H:i:s';
        $dateOnly                 = (
            isset( $startDate[Util::$LCHOUR] ) ||
            isset( $startDate[Util::$LCMIN] )  ||
            isset( $startDate[Util::$LCSEC] )) ? false : true;
        if( ! isset( $startDate[Util::$LCHOUR] )) {
            $startDate[Util::$LCHOUR] = 0;
        }
        if( ! isset( $startDate[Util::$LCMIN] )) {
            $startDate[Util::$LCMIN] = 0;
        }
        if( ! isset( $startDate[Util::$LCSEC] )) {
            $startDate[Util::$LCSEC] = 0;
        }
        $tz       = ( isset( $startDate[Util::$LCtz] )) ? $startDate[Util::$LCtz] : null;
        $dateTime = DateTime::createFromFormat( 
            $FMT,
            Util::getYMDHISEString( $startDate )
        );
        UtilDuration::modifyDateTimeFromDateInterval( $dateTime, $dateInterval );
        $dateTimeArr = Util::dateTime2Arr( $dateTime, [], false );
        $dateTimeArr = $dateTimeArr[Util::$LCvalue];
        if( ! empty( $tz )) {
            $dateTimeArr[Util::$LCtz] = $tz;
        }
        if( $dateOnly &&
            (( 0 == $dateTimeArr[Util::$LCHOUR] ) &&
             ( 0 == $dateTimeArr[Util::$LCMIN] ) &&
             ( 0 == $dateTimeArr[Util::$LCSEC] ))) {
            unset( $dateTimeArr[Util::$LCHOUR], $dateTimeArr[Util::$LCMIN], $dateTimeArr[Util::$LCSEC] );
        }
        return $dateTimeArr;
    }

    /**
     * Return DateInterval as (extrnal) array
     *
     * @param DateInterval $dateInterval
     * @return array
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.16.7 - 2018-12-04
     */
    public static function dateInterval2arr( DateInterval $dateInterval ) {
        $result = [];
        if( 0 < $dateInterval->y ) {
            $result[Util::$LCYEAR]  = $dateInterval->y;
        }
        if( 0 < $dateInterval->m ) {
            $result[Util::$LCMONTH] = $dateInterval->m;
        }
        if( 0 < $dateInterval->d ) {
            $result[Util::$LCDAY]   = $dateInterval->d;
        }
        if( 0 < $dateInterval->h ) {
            $result[Util::$LCHOUR]  = $dateInterval->h;
        }
        if( 0 < $dateInterval->i ) {
            $result[Util::$LCMIN]   = $dateInterval->i;
        }
        if( 0 < $dateInterval->s ) {
            $result[Util::$LCSEC]   = $dateInterval->s;
        }
        // separate duration (arr) from datetime (arr)
        if( ! UtilDuration::issetAndNotEmpty( $result, Util::$LCWEEK )) {
            $result[Util::$LCWEEK] = 0;
        }
        return $result;
    }

    /**
     * Return array (in internal format) for a (array) duration (only W+D+HMS)
     *
     * @param array $duration
     * @return array
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.16.7 - 2018-11-26
     * @todo error mgnt
     */
    public static function duration2arr( $duration ) {
        $dateTime1 = new DateTime();
        $dateTime2 = clone $dateTime1;
        foreach( $duration as $durKey => $durValue ) {
            if( empty( $durValue )) {
                continue;
            }
            switch( $durKey ) {
                case Util::$ZERO:
                case Util::$LCWEEK:
                    $durValue *= 7;
                    $dateTime2->modify( Util::$PLUS . $durValue . Util::$SP1 . Util::$LCDAY );
                    break;
                case '1':
                case Util::$LCDAY:
                    $dateTime2->modify( Util::$PLUS . $durValue . Util::$SP1 . Util::$LCDAY );
                    break;
                case '2':
                case Util::$LCHOUR:
                    $dateTime2->modify( Util::$PLUS . $durValue . Util::$SP1 . Util::$LCHOUR );
                    break;
                case '3':
                case Util::$LCMIN:
                    $dateTime2->modify( Util::$PLUS . $durValue . Util::$SP1 . Util::$LCMIN );
                    break;
                case '4':
                case Util::$LCSEC:
                    $dateTime2->modify( Util::$PLUS . $durValue . Util::$SP1 . Util::$LCSEC );
                    break;
            }
        }
        $diffStr = UtilDuration::dateInterval2String( $dateTime1->diff( $dateTime2 ));
        $result  = UtilDuration::durationStr2arr( $diffStr, false );
        if( ! UtilDuration::issetAndNotEmpty( $result, Util::$LCYEAR )  &&
            ! UtilDuration::issetAndNotEmpty( $result, Util::$LCMONTH ) &&
              UtilDuration::issetAndNotEmpty( $result, Util::$LCDAY )   &&
                               ( 7 <= $result[Util::$LCDAY] )) {
            $weeks = (int) floor( $result[Util::$LCDAY] / 7 );
            if( 1 <= $weeks ) {
                $result[Util::$LCWEEK] = $weeks;
                $days = $result[Util::$LCDAY] % 7;
                if( 0 < $days ) {
                    $result[Util::$LCDAY] = $days;
                }
                else {
                    unset( $result[Util::$LCDAY] );
                }
            }
        }
        if( empty( $result[Util::$LCYEAR] ) && empty( $result[Util::$LCMONTH] )) {
            unset( $result[Util::$LCYEAR], $result[Util::$LCMONTH] );
        }
        if( empty( $result[Util::$LCDAY] )) {
            unset( $result[Util::$LCDAY] );
        }
        if( empty( $result[Util::$LCHOUR] ) && empty( $result[Util::$LCMIN] ) && empty( $result[Util::$LCSEC] )) {
            unset( $result[Util::$LCHOUR], $result[Util::$LCMIN], $result[Util::$LCSEC] );
        }
        // separate duration (arr) from datetime (arr)
        if( ! UtilDuration::issetAndNotEmpty( $result, Util::$LCWEEK )) {
            $result[Util::$LCWEEK] = 0;
        }
        return $result;
    }

    /**
     * Return datetime array (in internal format) for startdate + duration
     *
     * @param array $startDate
     * @param array $duration
     * @return array, date format
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-11-26
     */
    public static function duration2date( $startDate, $duration ) {
        $dateOnly                 = (
            isset( $startDate[Util::$LCHOUR] ) ||
            isset( $startDate[Util::$LCMIN] )  ||
            isset( $startDate[Util::$LCSEC] )) ? false : true;
        $startDate[Util::$LCHOUR] = ( isset( $startDate[Util::$LCHOUR] ))
            ? $startDate[Util::$LCHOUR] : 0;
        $startDate[Util::$LCMIN]  = ( isset( $startDate[Util::$LCMIN] ))
            ? $startDate[Util::$LCMIN] : 0;
        $startDate[Util::$LCSEC]  = ( isset( $startDate[Util::$LCSEC] ))
            ? $startDate[Util::$LCSEC] : 0;
        if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCYEAR )) {
            $startDate[Util::$LCYEAR]  += $duration[Util::$LCYEAR];
        }
        if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCMONTH )) {
            $startDate[Util::$LCMONTH] += $duration[Util::$LCMONTH];
        }
        if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCWEEK )) {
            $startDate[Util::$LCDAY]   += ( $duration[Util::$LCWEEK] * 7 );
        }
        if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCDAY )) {
            $startDate[Util::$LCDAY]   += $duration[Util::$LCDAY];
        }
        if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCHOUR )) {
            $startDate[Util::$LCHOUR]  += $duration[Util::$LCHOUR];
        }
        if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCMIN )) {
            $startDate[Util::$LCMIN]   += $duration[Util::$LCMIN];
        }
        if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCSEC )) {
            $startDate[Util::$LCSEC]   += $duration[Util::$LCSEC];
        }
        $date   = date(
            Util::$YMDHIS3,
            mktime(
                (int) $startDate[Util::$LCHOUR],
                (int) $startDate[Util::$LCMIN],
                (int) $startDate[Util::$LCSEC],
                (int) $startDate[Util::$LCMONTH],
                (int) $startDate[Util::$LCDAY],
                (int) $startDate[Util::$LCYEAR]
            )
        );
        $d      = explode( Util::$MINUS, $date );
        $dtEnd2 = [
            Util::$LCYEAR  => $d[0],
            Util::$LCMONTH => $d[1],
            Util::$LCDAY   => $d[2],
            Util::$LCHOUR  => $d[3],
            Util::$LCMIN   => $d[4],
            Util::$LCSEC   => $d[5],
        ];
        if( isset( $startDate[Util::$LCtz] )) {
            $dtEnd2[Util::$LCtz] = $startDate[Util::$LCtz];
        }
        if( $dateOnly &&
            (( 0 == $dtEnd2[Util::$LCHOUR] ) &&
             ( 0 == $dtEnd2[Util::$LCMIN] ) &&
             ( 0 == $dtEnd2[Util::$LCSEC] ))) {
            unset( $dtEnd2[Util::$LCHOUR], $dtEnd2[Util::$LCMIN], $dtEnd2[Util::$LCSEC] );
        }
        return $dtEnd2;
    }

    /**
     * Return an iCal formatted string from (internal array) duration
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-11-26
     * @param array $duration , array( year, month, day, week, day, hour, min, sec )
     * @return string
     * @static
     */
    public static function duration2str( array $duration ) {
        if( ! isset( $duration[Util::$LCYEAR] )  &&
            ! isset( $duration[Util::$LCMONTH] ) &&
            ! isset( $duration[Util::$LCDAY] )   &&
            ! isset( $duration[Util::$LCWEEK] )  &&
            ! isset( $duration[Util::$LCHOUR] )  &&
            ! isset( $duration[Util::$LCMIN] )   &&
            ! isset( $duration[Util::$LCSEC] )) {
            return null;
        }
        if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCWEEK )) {
            $result = UtilDuration::$P . $duration[Util::$LCWEEK] . UtilDuration::$W;
        }
        else {
            $result = UtilDuration::$P;
            if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCYEAR ) ) {
                $result .= $duration[Util::$LCYEAR] . UtilDuration::$Y;
            }
            if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCMONTH ) ) {
                $result .= $duration[Util::$LCMONTH] . UtilDuration::$M;
            }
        }
        if( UtilDuration::issetAndNotEmpty( $duration, Util::$LCDAY )) {
            $result .= $duration[Util::$LCDAY] . UtilDuration::$D;
        }
        $hourIsSet = ( UtilDuration::issetAndNotEmpty( $duration, Util::$LCHOUR ));
        $minIsSet  = ( UtilDuration::issetAndNotEmpty( $duration, Util::$LCMIN ));
        $secIsSet  = ( UtilDuration::issetAndNotEmpty( $duration, Util::$LCSEC ));
        if( $hourIsSet || $minIsSet || $secIsSet ) {
            $result .= UtilDuration::$T;
        }
        if( $hourIsSet ) {
            $result .= $duration[Util::$LCHOUR] . UtilDuration::$H;
        }
        if( $minIsSet ) {
            $result .= $duration[Util::$LCMIN] . UtilDuration::$M;
        }
        if( $secIsSet ) {
            $result .= $duration[Util::$LCSEC] . UtilDuration::$S;
        }
        if( UtilDuration::$P == $result ) {
            $result = UtilDuration::$PT0H0M0S;
        }
        return $result;
    }

    /**
     * Return array (in internal format) from string duration
     *
     * @param string $duration
     * @param bool   $reCheck
     * @return array|bool  false on error
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-11-26
     */
    public static function durationStr2arr( $duration, $reCheck=true ) {
        $duration = (string) trim( $duration );
        while( 0 != strcasecmp( UtilDuration::$P, $duration[0] )) {
            if( 0 < strlen( $duration )) {
                $duration = substr( $duration, 1 );
            }
            else {
                return false;
            } // no leading P !?!?
        }
        $duration = substr( $duration, 1 ); // skip P
        $result   = [];
        $val      = null;
        $timePart = false;
        $durLen   = strlen( $duration );
        for( $ix = 0; $ix < $durLen; $ix++ ) {
            switch( strtoupper( $duration[$ix] )) {
                case UtilDuration::$Y :
                    if( ! empty( $val )) {
                        $result[Util::$LCYEAR] = $val;
                    }
                    $val = null;
                    break;
                case UtilDuration::$W :
                    if( ! empty( $val )) {
                        $result[Util::$LCWEEK] = $val;
                    }
                    $val = null;
                    break;
                case UtilDuration::$D :
                    if( ! empty( $val )) {
                        $result[Util::$LCDAY] = $val;
                    }
                    $val = null;
                    break;
                case UtilDuration::$T :
                    $timePart = true;
                    $val = null;
                    break;
                case UtilDuration::$H :
                    if( ! empty( $val )) {
                        $result[Util::$LCHOUR] = $val;
                    }
                    $val = null;
                    break;
                case UtilDuration::$M :
                    if( ! empty( $val )) {
                        if( ! $timePart ) {
                            $result[Util::$LCMONTH] = $val;
                        }
                        else {
                            $result[Util::$LCMIN] = $val;
                        }
                    }
                    $val = null;
                    break;
                case UtilDuration::$S :
                    if( ! empty( $val )) {
                        $result[Util::$LCSEC] = $val;
                    }
                    $val = null;
                    break;
                default:
                    if( ! ctype_digit( $duration[$ix] )) {
                        return false;
                    } // unknown duration control character  !?!?
                    else {
                        $val .= $duration[$ix];
                    }
            }
        }
        // separate duration (arr) from datetime (arr)
        if( ! isset( $result[Util::$LCWEEK] )) {
            $result[Util::$LCWEEK] = 0;
        }
        return ( $reCheck) ? UtilDuration::duration2arr( $result ) : $result ;
    }

    /**
     * Return bool true if value is isset and not empty
     *
     * @param array  $array
     * @param string $key
     * @return bool
     * @static
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-11-26
     */
    private static function issetAndNotEmpty( array $array, $key ) {
        if( ! array_key_exists( $key, $array )) {
            return false;
        }
        return ( isset( $array[$key] ) && ! empty( $array[$key] ));
    }

}
