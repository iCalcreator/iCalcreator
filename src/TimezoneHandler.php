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

namespace Kigkonsult\Icalcreator;

use Kigkonsult\Icalcreator\Util\Util;
use DateTimeZone;
use DateTime;
use Exception;

use function array_keys;
use function date;
use function end;
use function explode;
use function floor;
use function is_array;
use function is_int;
use function is_null;
use function ksort;
use function reset;
use function sprintf;
use function str_replace;
use function strcasecmp;
use function strpos;
use function strtolower;
use function substr;
use function trim;

/**
 * iCalcreator timezone management class
 *
 * Manages loosely coupled iCalcreator Vcalendar (timezone) functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class TimezoneHandler
{
    private static $FMTTIMESTAMP = '@%s';
    private static $OFFSET       = 'offset';
    private static $TIME         = 'time';

    /**
     * Create a calendar timezone and standard/daylight components
     *
     * Result when 'Europe/Stockholm' and no from/to arguments is used as timezone:
     * BEGIN:VTIMEZONE
     * TZID:Europe/Stockholm
     * BEGIN:STANDARD
     * DTSTART:20101031T020000
     * TZOFFSETFROM:+0200
     * TZOFFSETTO:+0100
     * TZNAME:CET
     * END:STANDARD
     * BEGIN:DAYLIGHT
     * DTSTART:20100328T030000
     * TZOFFSETFROM:+0100
     * TZOFFSETTO:+0200
     * TZNAME:CEST
     * END:DAYLIGHT
     * END:VTIMEZONE
     *
     * Generates components for all transitions in a date range,
     *   based on contribution by Yitzchok Lavi <icalcreator@onebigsystem.com>
     * Additional changes jpirkey
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param Vcalendar $calendar iCalcreator calendar instance
     * @param string    $timezone valid timezone avveptable by PHP5 DateTimeZone
     * @param array     $xProp    *[x-propName => x-propValue]
     * @param int       $from     unix timestamp
     * @param int       $to       unix timestamp
     * @return bool
     * @static
     */
    public static function createTimezone(
        Vcalendar $calendar,
        $timezone,
        $xProp = [],
        $from  = null,
        $to    = null
    ) {
        static $Y           = 'Y  ';
        static $YMD         = 'Ymd';
        static $T000000     = 'T000000';
        static $MINUS7MONTH = '-7 month';
        static $YMD2        = 'Y-m-d';
        static $T235959     = 'T235959';
        static $PLUS18MONTH = '+18 month';
        static $TS          = 'ts';
        static $YMDHIS3     = 'Y-m-d-H-i-s';
        static $SECONDS     = 'seconds';
        static $ABBR        = 'abbr';
        static $ISDST       = 'isdst';
        static $NOW         = 'now';
        static $YMDTHISO    = 'Y-m-d\TH:i:s O';
        if( empty( $timezone )) {
            return false;
        }
        if( ! empty( $from ) && ! is_int( $from )) {
            return false;
        }
        if( ! empty( $to ) && ! is_int( $to )) {
            return false;
        }
        try {
            $newTz = new DateTimeZone( $timezone );
            $utcTz = new DateTimeZone( Util::$UTC );
        }
        catch( Exception $e ) {
            return false;
        }
        if( empty( $from ) || empty( $to )) {
            $dates = array_keys( $calendar->getProperty( Util::$DTSTART ));
            if( empty( $dates )) {
                $dates = [ date( $YMD ) ];
            }
        }
        if( ! empty( $from )) {
            try {
                $timestamp = sprintf( self::$FMTTIMESTAMP, $from );
                $dateFrom  = new DateTime( $timestamp );    // set lowest date (UTC)
            }
            catch( Exception $e ) {
                return false;
            }
        }
        else {
            try {
                $from     = reset( $dates );         // set lowest date to the lowest dtstart date
                $dateFrom = new DateTime( $from . $T000000, $newTz );
                $dateFrom->modify( $MINUS7MONTH );          // set $dateFrom to seven month before the lowest date
                $dateFrom->setTimezone( $utcTz );           // convert local date to UTC
            }
            catch( Exception $e ) {
                return false;
            }
        }
        $dateFromYmd = $dateFrom->format( $YMD2 );
        if( ! empty( $to )) {
            try {
                $timestamp = sprintf( self::$FMTTIMESTAMP, $to );
                $dateTo    = new DateTime( $timestamp );    // set end date (UTC)
            }
            catch( Exception $e ) {
                return false;
            }
        }
        else {
            try {
                $to     = end( $dates );             // set highest date to the highest dtstart date
                $dateTo = new DateTime( $to . $T235959, $newTz );
            }
            catch( Exception $e ) {
                return false;
            }
            $dateTo->modify( $PLUS18MONTH );                  // set $dateTo to 18 month after the highest date
            $dateTo->setTimezone( $utcTz );                   // convert local date to UTC
        }
        $dateToYmd      = $dateTo->format( $YMD2 );
        $transTemp      = [];
        $prevOffsetfrom = 0;
        $stdIx          = $dlghtIx = null;
        $prevTrans      = false;
        $transitions    = $newTz->getTransitions();
        foreach( $transitions as $tix => $trans ) {           // all transitions in date-time order!!
            if( 0 > (int) date( $Y, $trans[$TS] )) {         // skip negative year... but save offset
                $prevOffsetfrom = $trans[self::$OFFSET];      // previous trans offset will be 'next' trans offsetFrom
                continue;
            }
            try {
                $timestamp = sprintf( self::$FMTTIMESTAMP, $trans[$TS] );
                $date      = new DateTime( $timestamp );      // set transition date (UTC)
            }
            catch( Exception $e ) {
                return false;
            }
            $transDateYmd = $date->format( $YMD2 );
            if( $transDateYmd < $dateFromYmd ) {
                $prevOffsetfrom                 = $trans[self::$OFFSET]; // previous trans offset will be 'next' trans offsetFrom
                $prevTrans                      = $trans;                // we save it in case we don't find any that match
                $prevTrans[Util::$TZOFFSETFROM] = ( 0 < $tix ) ? $transitions[$tix - 1][self::$OFFSET] : 0;
                continue;
            }
            if( $transDateYmd > $dateToYmd ) {
                break;
            }                                                 // loop always (?) breaks here
            if( ! empty( $prevOffsetfrom ) || ( 0 == $prevOffsetfrom )) {
                $trans[Util::$TZOFFSETFROM] = $prevOffsetfrom; // i.e. set previous offsetto as offsetFrom
                $date->modify( $trans[Util::$TZOFFSETFROM] . $SECONDS ); // convert utc date to local date
                $d                  = \explode( Util::$MINUS, $date->format( $YMDHIS3 ));
                $trans[self::$TIME] = [
                    Util::$LCYEAR  => (int) $d[0],            // set date to array
                    Util::$LCMONTH => (int) $d[1],            //  to ease up dtstart and (opt) rdate setting
                    Util::$LCDAY   => (int) $d[2],
                    Util::$LCHOUR  => (int) $d[3],
                    Util::$LCMIN   => (int) $d[4],
                    Util::$LCSEC   => (int) $d[5],
                ];
            }
            $prevOffsetfrom = $trans[self::$OFFSET];
            if( true !== $trans[$ISDST] ) {                   // standard timezone
                if( ! empty( $stdIx ) && isset( $transTemp[$stdIx][Util::$TZOFFSETFROM] ) &&
                    ( $transTemp[$stdIx][$ABBR] == $trans[$ABBR] ) &&
                    ( $transTemp[$stdIx][Util::$TZOFFSETFROM] == $trans[Util::$TZOFFSETFROM] ) &&
                    ( $transTemp[$stdIx][self::$OFFSET] == $trans[self::$OFFSET] )) {
                    $transTemp[$stdIx][Util::$RDATE][] = $trans[self::$TIME];
                    continue; // check for any repeating rdate's (in order)
                }
                $stdIx = $tix;
            } // end standard timezone
            else {                                            // daylight timezone
                if( ! empty( $dlghtIx ) && isset( $transTemp[$dlghtIx][Util::$TZOFFSETFROM] ) &&
                    ( $transTemp[$dlghtIx][$ABBR] == $trans[$ABBR] ) &&
                    ( $transTemp[$dlghtIx][Util::$TZOFFSETFROM] == $trans[Util::$TZOFFSETFROM] ) &&
                    ( $transTemp[$dlghtIx][self::$OFFSET] == $trans[self::$OFFSET] )) {
                    $transTemp[$dlghtIx][Util::$RDATE][] = $trans[self::$TIME];
                    continue; // check for any repeating rdate's (in order)
                }
                $dlghtIx = $tix;
            } // end daylight timezone
            $transTemp[$tix] = $trans;
        } // end foreach( $transitions as $tix => $trans )
        $timezoneComp = $calendar->newVtimezone();
        $timezoneComp->setproperty( Util::$TZID, $timezone );
        if( ! empty( $xProp )) {
            foreach( $xProp as $xPropName => $xPropValue ) {
                if( Util::isXprefixed( $xPropName )) {
                    $timezoneComp->setproperty( $xPropName, $xPropValue );
                }
            }
        }
        if( empty( $transTemp )) {        // if no match is found
            if( $prevTrans ) {            // we use the last transition (before startdate) for the tz info
                try {
                    $timestamp = sprintf( self::$FMTTIMESTAMP, $prevTrans[$TS] );
                    $date      = new DateTime( $timestamp ); // set transition date (UTC)
                }
                catch( Exception $e ) {
                    return false;
                }
                $date->modify( $prevTrans[Util::$TZOFFSETFROM] . $SECONDS );// convert utc date to local date
                $d = explode( Util::$MINUS, $date->format( $YMDHIS3 )
                );                        // set arr-date to ease up dtstart setting
                $prevTrans[self::$TIME] = [
                    Util::$LCYEAR  => (int) $d[0],
                    Util::$LCMONTH => (int) $d[1],
                    Util::$LCDAY   => (int) $d[2],
                    Util::$LCHOUR  => (int) $d[3],
                    Util::$LCMIN   => (int) $d[4],
                    Util::$LCSEC   => (int) $d[5],
                ];
                $transTemp[0] = $prevTrans;
            } // end if( $prevTrans )
            else {                        // or we use the timezone identifier to BUILD the standard tz info (?)
                try {
                    $date = new DateTime( $NOW, $newTz );
                }
                catch( Exception $e ) {
                    return false;
                }
                $transTemp[0] = [
                    self::$TIME         => $date->format( $YMDTHISO ),
                    self::$OFFSET       => $date->format( Util::$Z ),
                    Util::$TZOFFSETFROM => $date->format( Util::$Z ),
                    $ISDST              => false,
                ];
            }
        } // end if( empty( $transTemp ))
        foreach( $transTemp as $tix => $trans ) { // create standard/daylight subcomponents
            $subComp = ( true !== $trans[$ISDST] )
                ? $timezoneComp->newStandard()
                : $timezoneComp->newDaylight();
            $subComp->setProperty( Util::$DTSTART, $trans[self::$TIME] );
            if( ! empty( $trans[$ABBR] )) {
                $subComp->setProperty( Util::$TZNAME, $trans[$ABBR] );
            }
            if( isset( $trans[Util::$TZOFFSETFROM] )) {
                $subComp->setProperty( Util::$TZOFFSETFROM, self::offsetSec2His( $trans[Util::$TZOFFSETFROM] ));
            }
            $subComp->setProperty( Util::$TZOFFSETTO, self::offsetSec2His( $trans[self::$OFFSET] ));
            if( isset( $trans[Util::$RDATE] )) {
                $subComp->setProperty( Util::$RDATE, $trans[Util::$RDATE] );
            }
        }
        return true;
    }

    /**
     * Return iCal offset [-/+]hhmm[ss] (string) from UTC offset seconds
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param string $seconds
     * @return string
     * @static
     */
    public static function offsetSec2His( $seconds ) {
        static $FMT = '%02d';
        switch( substr( $seconds, 0, 1 )) {
            case Util::$MINUS :
                $output  = Util::$MINUS;
                $seconds = substr( $seconds, 1 );
                break;
            case Util::$PLUS :
                $output  = Util::$PLUS;
                $seconds = substr( $seconds, 1 );
                break;
            default :
                $output = Util::$PLUS;
                break;
        }
        $output  .= sprintf( $FMT, ((int) floor( $seconds / 3600 ))); // hour
        $seconds = $seconds % 3600;
        $output  .= sprintf( $FMT, ((int) floor( $seconds / 60 )));   // min
        $seconds = $seconds % 60;
        if( 0 < $seconds ) {
            $output .= sprintf( $FMT, $seconds ); // sec
        }
        return $output;
    }

    /**
     * Very basic conversion of a MS timezone to a PHP5 valid (Date-)timezone
     * matching (MS) UCT offset and time zone descriptors
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.8 - 2017-04-17
     * @param string $timezone to convert
     * @return bool
     * @static
     */
    public static function ms2phpTZ( & $timezone ) {
        static $REPL1 = [ 'GMT', 'gmt', 'utc' ];
        static $REPL2 = [ '(', ')', '&', ',', '  ' ];
        static $PUTC = '(UTC';
        static $ENDP = ')';
        static $TIMEZONE_ID = 'timezone_id';
        if( empty( $timezone )) {
            return false;
        }
        $search = str_replace( Util::$QQ, null, $timezone );
        $search = str_replace( $REPL1, Util::$UTC, $search );
        if( $PUTC != substr( $search, 0, 4 )) {
            return false;
        }
        if( false === ( $pos = strpos( $search, $ENDP ))) {
            return false;
        }
        $searchOffset = substr( $search, 4, ( $pos - 4 ));
        $searchOffset = Util::tz2offset( str_replace( Util::$COLON, null, $searchOffset ));
        while( Util::$SP1 == $search[( $pos + 1 )] ) {
            $pos += 1;
        }
        $searchText  = trim( str_replace( $REPL2, Util::$SP1, substr( $search, ( $pos + 1 ))));
        $searchWords = explode( Util::$SP1, $searchText );
        try {
            $timezoneAbbreviations = DateTimeZone::listAbbreviations();
        }
        catch( Exception $e ) {
            return false;
        }
        $hits = [];
        foreach( $timezoneAbbreviations as $name => $transitions ) {
            foreach( $transitions as $cnt => $transition ) {
                if( empty( $transition[self::$OFFSET] ) ||
                    empty( $transition[$TIMEZONE_ID] ) ||
                    ( $transition[self::$OFFSET] != $searchOffset )) {
                    continue;
                }
                $cWords = explode( Util::$L, $transition[$TIMEZONE_ID] );
                $cPrio  = $hitCnt = $rank = 0;
                foreach( $cWords as $cWord ) {
                    if( empty( $cWord )) {
                        continue;
                    }
                    $cPrio += 1;
                    $sPrio  = 0;
                    foreach( $searchWords as $sWord ) {
                        if( empty( $sWord ) || ( self::$TIME == strtolower( $sWord ))) {
                            continue;
                        }
                        $sPrio += 1;
                        if( 0 == strcasecmp( $cWord, $sWord )) {
                            $hitCnt += 1;
                            $rank   += ( $cPrio + $sPrio );
                        }
                        else {
                            $rank += 10;
                        }
                    }
                }
                if( 0 < $hitCnt ) {
                    $hits[$rank][] = $transition[$TIMEZONE_ID];
                }
            } // end foreach( $transitions as $cnt => $transition )
        } // end foreach( $timezoneAbbreviations as $name => $transitions )
        if( empty( $hits )) {
            return false;
        }
        ksort( $hits );
        foreach( $hits as $rank => $tzs ) {
            if( ! empty( $tzs )) {
                $timezone = reset( $tzs );
                return true;
            }
        }
        return false;
    }

    /**
     * Transforms a dateTime from a timezone to another
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-03-04
     * @param mixed  $date   date to alter
     * @param string $tzFrom PHP valid 'from' timezone
     * @param string $tzTo   PHP valid 'to' timezone, default Util::$UTC
     * @param string $format date output format, default 'Ymd\THis'
     * @return bool true on success, false on error
     * @static
     */
    public static function transformDateTime( & $date, $tzFrom, $tzTo = null, $format = null ) {
        static $YMDTHIS = 'Ymd\THis';
        if( is_null( $tzTo )) {
            $tzTo = Util::$UTC;
        }
        elseif( Util::$Z == $tzTo ) {
            $tzTo = Util::$UTC;
        }
        if( is_null( $format )) {
            $format = $YMDTHIS;
        }
        if( is_array( $date ) && isset( $date[Util::$LCTIMESTAMP] )) {
            try {
                $timestamp = sprintf( self::$FMTTIMESTAMP, $date[Util::$LCTIMESTAMP] );
                $d         = new DateTime( $timestamp ); // set UTC date
                $newTz     = new DateTimeZone( $tzFrom );
                $d->setTimezone( $newTz );               // convert to 'from' date
            }
            catch( Exception $e ) {
                return false;
            }
        }
        else {
            if( Util::isArrayDate( $date )) {
                if( isset( $date[Util::$LCtz] )) {
                    unset( $date[Util::$LCtz] );
                }
                $date = Util::date2strdate( Util::chkDateArr( $date ));
            }
            if( Util::$Z == substr( $date, -1 )) {
                $date = substr( $date, 0, ( \strlen( $date ) - 2 ));
            }
            try {
                $d    = new DateTime( $date, new DateTimeZone( $tzFrom ));
            }
            catch( Exception $e ) {
                return false;
            }
        }
        try {
            $newTz = new DateTimeZone( $tzTo );
            $d->setTimezone( $newTz );
        }
        catch( Exception $e ) {
            return false;
        }
        $date = $d->format( $format );
        return true;
    }
}
