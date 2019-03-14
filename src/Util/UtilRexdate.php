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

use DateTime;
use DateInterval;
use Exception;


use function array_key_exists;
use function array_keys;
use function count;
use function ctype_digit;
use function end;
use function explode;
use function in_array;
use function is_array;
use function is_string;
use function reset;
use function str_replace;
use function strlen;
use function strtoupper;
use function substr;
use function substr_count;
use function trim;
use function usort;

/**
 * iCalcreator EXDATE/RDATE support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26.8 - 2018-12-12
 */
class UtilRexdate
{
    /**
     * @var array
     * @access private
     * @static
     */
    private static $GMTUTCZARR = [ 'GMT', 'UTC', 'Z' ];

    /**
     * Check (EXDATE/RDATE) date(-time) and params arrays for an opt. timezone
     *
     * If it is a DATE-TIME or DATE, updates $parno and (opt) $params)
     *
     * @param array $theDate date to check
     * @param int   $parno   no of date parts (i.e. year, month.. .)
     * @param array $params  property parameters
     * @access private
     * @static
     * @since 2.26.7 - 2018-11-25
     */
    private static function chkDateCfg( $theDate, & $parno, & $params ) {
        $paramsValueIsDATE = Util::isParamsValueSet( [ Util::$LCparams => $params ], Util::$DATE );
        switch( true ) {
            case ( isset( $params[Util::$TZID] )) :
                $parno = 6;
                break;
            case ( $paramsValueIsDATE ) :
                $params[Util::$VALUE] = Util::$DATE;
                $parno                = 3;
                break;
            default:
                if( Util::isParamsValueSet( [ Util::$LCparams => $params ], Util::$PERIOD )) {
                    $params[Util::$VALUE] = Util::$PERIOD;
                    $parno                = 7;
                }
                switch( true ) {
                    case ( $theDate instanceof DateTime ) :
                        $tz = $theDate->getTimezone();
                        if( ! empty( $tz )) {
                            $params[Util::$TZID] = $tz->getName();
                        }
                        $parno = 7;
                        break;
                    case ( is_array( $theDate )) :
                        $tzid = null;
                        if( isset( $theDate[Util::$LCTIMESTAMP] )) {
                            $tzid = ( isset( $theDate[Util::$LCtz] )) ? $theDate[Util::$LCtz] : null;
                        }
                        elseif( isset( $theDate[Util::$LCtz] )) {
                            $tzid = $theDate[Util::$LCtz];
                        }
                        elseif( 7 == count( $theDate )) {
                            $tzid = end( $theDate );
                        }
                        if( ! empty( $tzid )) {
                            $parno = 7;
                            if( ! Util::isOffset( $tzid )) {
                                $params[Util::$TZID] = $tzid;
                            } // save only timezone
                        }
                        elseif( ! $parno && ( 3 == count( $theDate )) && $paramsValueIsDATE ) {
                            $parno = 3;
                        }
                        else {
                            $parno = 6;
                        }
                        break;
                    default : // i.e. string
                        $date = trim((string) $theDate );
                        if(( Util::$Z   == substr( $date, -1 )) || // UTC DATE-TIME
                           ( Util::$GMT == substr( $date, -3 )) ||
                           ( Util::$UTC == substr( $date, -3 ))) {
                            $params[Util::$TZID] = Util::$Z;
                            $parno = 7;
                        }
                        elseif(( 2 == substr_count( $date, Util::$SP1 )) &&  // 'Y-m-d H:i:s e'
                               ( 2 == substr_count( $date, Util::$MINUS )) &&
                               ( 2 == substr_count( $date, Util::$COLON ))) {
                            $params[Util::$TZID] = explode( Util::$SP1, $date, 3 )[2];
                            $parno = 7;
                        }
                        elseif((( 8 == strlen( $date ) && ctype_digit( $date )) || ( 11 >= strlen( $date )))
                            && $paramsValueIsDATE ) {
                            $parno = 3;
                        }
                        $date = Util::strDate2ArrayDate( $date, $parno );
                        unset( $date[Util::$UNPARSEDTEXT] );
                        if( ! empty( $date[Util::$LCtz] )) {
                            $parno = 7;
                            if( ! Util::isOffset( $date[Util::$LCtz] )) {
                                $params[Util::$TZID] = $date[Util::$LCtz];
                            } // save only timezone
                        }
                        elseif( empty( $parno )) {
                            $parno = 6;
                        }
                        break;
                } // end switch( true )
                if( isset( $params[Util::$TZID] )) {
                    $parno = 6;
                }
        } // end switch( true )
    }

    /**
     * Return formatted output for calendar component property data value type recur
     *
     * @param array $exdateData
     * @param bool  $allowEmpty
     * @return string
     * @static
     */
    public static function formatExdate( $exdateData, $allowEmpty ) {
        static $SORTER1 = [
            'Kigkonsult\Icalcreator\Util\VcalendarSortHandler',
            'sortExdate1',
        ];
        static $SORTER2 = [
            'Kigkonsult\Icalcreator\Util\VcalendarSortHandler',
            'sortExdate2',
        ];
        $output  = null;
        $exdates = [];
        foreach(( array_keys( $exdateData )) as $ex ) {
            $theExdate = $exdateData[$ex];
            if( empty( $theExdate[Util::$LCvalue] )) {
                if( $allowEmpty ) {
                    $output .= Util::createElement( Util::$EXDATE );
                }
                continue;
            }
            if( 1 < count( $theExdate[Util::$LCvalue] )) {
                usort( $theExdate[Util::$LCvalue], $SORTER1 );
            }
            $exdates[] = $theExdate;
        }
        if( 1 < count( $exdates )) {
            usort( $exdates, $SORTER2 );
        }
        foreach(( array_keys( $exdates )) as $ex ) {
            $theExdate = $exdates[$ex];
            $content   = $attributes = null;
            foreach(( array_keys( $theExdate[Util::$LCvalue] )) as $eix ) {
                $exdatePart = $theExdate[Util::$LCvalue][$eix];
                $parno      = count( $exdatePart );
                $formatted  = Util::date2strdate( $exdatePart, $parno );
                if( isset( $theExdate[Util::$LCparams][Util::$TZID] )) {
                    $formatted = str_replace( Util::$Z, null, $formatted );
                }
                if( 0 < $eix ) {
                    if( isset( $theExdate[Util::$LCvalue][0][Util::$LCtz] )) {
                        if(( Util::isOffset( $theExdate[Util::$LCvalue][0][Util::$LCtz] )) ||
                            ( Util::$Z == $theExdate[Util::$LCvalue][0][Util::$LCtz] )) {
                            if( Util::$Z != substr( $formatted, -1 )) {
                                $formatted .= Util::$Z;
                            }
                        }
                        else {
                            $formatted = str_replace( Util::$Z, null, $formatted );
                        }
                    }
                    else {
                        $formatted = str_replace( Util::$Z, null, $formatted );
                    }
                } // end if( 0 < $eix )
                $content .= ( 0 < $eix ) ? Util::$COMMA . $formatted : $formatted;
            } // end foreach(( array_keys( $theExdate[Util::$LCvalue]...
            $output .= Util::createElement(
                Util::$EXDATE,
                Util::createParams( $theExdate[Util::$LCparams] ),
                $content
            );
        } // end foreach(( array_keys( $exdates...
        return $output;
    }

    /**
     * Return prepared calendar component property exdate input
     *
     * @param array $exdates
     * @param array $params
     * @return mixed array|bool
     * @static
     * @since 2.26.7 - 2018-11-25
     */
    public static function prepInputExdate( $exdates, $params = null ) {
        $output = [
            Util::$LCparams => Util::setParams( $params, Util::$DEFAULTVALUEDATETIME ),
            Util::$LCvalue  => [],
        ];
        foreach(( array_keys( $exdates )) as $eix ) {
            if( $exdates[$eix] instanceof DateTime ) {
                $exdates[$eix] = Util::dateTime2Str( $exdates[$eix] );
            }
        }
        /* ev. check 1:st date and save ev. timezone **/
        self::chkDateCfg( reset( $exdates ), $parno, $output[Util::$LCparams] );
        Util::existRem( $output[Util::$LCparams], Util::$VALUE, Util::$DATE_TIME ); // remove default parameter
        $toZ   = ( isset( $output[Util::$LCparams][Util::$TZID] ) &&
            in_array( strtoupper( $output[Util::$LCparams][Util::$TZID] ), self::$GMTUTCZARR )) ? true : false;
        foreach(( array_keys( $exdates )) as $eix ) {
            $theExdate = $exdates[$eix];
            $wDate     = [];
            Util::strDate2arr( $theExdate );
            if( Util::isArrayTimestampDate( $theExdate )) {
                if( isset( $theExdate[Util::$LCtz] ) &&
                    ! Util::isOffset( $theExdate[Util::$LCtz] )) {
                    if( isset( $output[Util::$LCparams][Util::$TZID] )) {
                        $theExdate[Util::$LCtz] = $output[Util::$LCparams][Util::$TZID];
                    }
                    else {
                        $output[Util::$LCparams][Util::$TZID] = $theExdate[Util::$LCtz];
                    }
                }
                $wDate = Util::timestamp2date( $theExdate, $parno );
            }
            elseif( is_array( $theExdate )) {
                $d = Util::chkDateArr( $theExdate, $parno );
                if( isset( $d[Util::$LCtz] ) && ( Util::$Z != $d[Util::$LCtz] ) && Util::isOffset( $d[Util::$LCtz] )) {
                    $wDate = Util::ensureArrDatetime( [Util::$LCvalue => $d], $d[Util::$LCtz], 7 );
                    unset( $wDate[Util::$UNPARSEDTEXT] );
                }
                else {
                    $wDate = $d;
                }
            }
            elseif( 8 <= strlen( trim( $theExdate ))) { // ex. 2006-08-03 10:12:18
                $wDate = Util::strDate2ArrayDate( $theExdate, $parno );
                unset( $wDate[Util::$UNPARSEDTEXT] );
            }
            if( 3 == $parno ) {
                unset( $wDate[Util::$LCHOUR], $wDate[Util::$LCMIN], $wDate[Util::$LCSEC], $wDate[Util::$LCtz] );
            }
            elseif( isset( $wDate[Util::$LCtz] )) {
                $wDate[Util::$LCtz] = (string) $wDate[Util::$LCtz];
            }
            if( isset( $output[Util::$LCparams][Util::$TZID] ) ||
                ( isset( $wDate[Util::$LCtz] ) && ! Util::isOffset( $wDate[Util::$LCtz] )) ||
                ( isset( $output[Util::$LCvalue][0] ) && ( ! isset( $output[Util::$LCvalue][0][Util::$LCtz] ))) ||
                ( isset( $output[Util::$LCvalue][0][Util::$LCtz] ) &&
                    ! Util::isOffset( $output[Util::$LCvalue][0][Util::$LCtz] ))) {
                unset( $wDate[Util::$LCtz] );
            }
            if( $toZ ) { // time zone Z
                $wDate[Util::$LCtz] = Util::$Z;
            }
            $output[Util::$LCvalue][] = $wDate;
        } // end foreach(( array_keys( $exdates...
        if( 0 >= count( $output[Util::$LCvalue] )) {
            return false;
        }
        if( 3 == $parno ) {
            $output[Util::$LCparams][Util::$VALUE] = Util::$DATE;
            unset( $output[Util::$LCparams][Util::$TZID] );
        }
        if( $toZ ) { // time zone Z
            unset( $output[Util::$LCparams][Util::$TZID] );
        }
        return $output;
    }

    /**
     * Return formatted output for calendar component property rdate
     *
     * @param array  $rdateData
     * @param bool   $allowEmpty
     * @param string $compType
     * @return string
     * @static
     * @since 2.26.7 - 2018-11-29
     */
    public static function formatRdate( $rdateData, $allowEmpty, $compType ) {
        static $SORTER1 = [
            'Kigkonsult\Icalcreator\Util\VcalendarSortHandler',
            'sortRdate1',
        ];
        static $SORTER2 = [
            'Kigkonsult\Icalcreator\Util\VcalendarSortHandler',
            'sortRdate2',
        ];
        $utcTime = ( Util::isCompInList( $compType, Util::$TZCOMPS )) ? true : false;
        $output  = null;
        $rDates  = [];
        foreach(( array_keys( $rdateData )) as $rpix ) {
            $theRdate = $rdateData[$rpix];
            if( empty( $theRdate[Util::$LCvalue] )) {
                if( $allowEmpty ) {
                    $output .= Util::createElement( Util::$RDATE );
                }
                continue;
            }
            if( $utcTime ) {
                unset( $theRdate[Util::$LCparams][Util::$TZID] );
            }
            if( 1 < count( $theRdate[Util::$LCvalue] )) {
                usort( $theRdate[Util::$LCvalue], $SORTER1 );
            }
            $rDates[] = $theRdate;
        }
        if( 1 < count( $rDates )) {
            usort( $rDates, $SORTER2 );
        }
        $paramsTZIDisSet = ! empty( $theRdate[Util::$LCparams][Util::$TZID] );
        foreach(( array_keys( $rDates )) as $rpix ) {
            $theRdate    = $rDates[$rpix];
            $attributes  = Util::createParams( $theRdate[Util::$LCparams] );
            $cnt         = count( $theRdate[Util::$LCvalue] );
            $content     = null;
            $rno         = 1;
            foreach(( array_keys( $theRdate[Util::$LCvalue] )) as $rix ) {
                $rdatePart   = $theRdate[Util::$LCvalue][$rix];
                $contentPart = null;
                if( is_array( $rdatePart ) && Util::isParamsValueSet( $theRdate, Util::$PERIOD )) { // PERIOD
                    if( $utcTime  ||
                        ( $paramsTZIDisSet && isset( $rdatePart[0][Util::$LCtz] ) &&
                            ! Util::isOffset( $rdatePart[0][Util::$LCtz] ))) {
                        unset( $rdatePart[0][Util::$LCtz] );
                    }
                    $formatted = Util::date2strdate( $rdatePart[0] ); // PERIOD part 1
                    if( $utcTime || $paramsTZIDisSet ) {
                        $formatted = str_replace( Util::$Z, null, $formatted );
                    }
                    $contentPart .= $formatted;
                    $contentPart .= '/';
                    if( isset( $rdatePart[1]['invert'] )) { // fix pre 7.0.5 bug
                        $dateInterval = UtilDuration::DateIntervalArr2DateInterval( $rdatePart[1] );
                        $contentPart .= UtilDuration::dateInterval2String( $dateInterval );
                    }
                    elseif( ! array_key_exists( Util::$LCHOUR, $rdatePart[1] )) {
                        $contentPart .= Util::getYMDString( $rdatePart[1] );
                    }
                    else { // date-time
                        if( $utcTime  ||
                            ( $paramsTZIDisSet && isset( $rdatePart[1][Util::$LCtz] ) &&
                                ! Util::isOffset( $rdatePart[1][Util::$LCtz] ))) {
                            unset( $rdatePart[1][Util::$LCtz] );
                        }
                        $formatted = Util::date2strdate( $rdatePart[1] ); // PERIOD part 2
                        if( $utcTime || $paramsTZIDisSet ) {
                            $formatted = str_replace( Util::$Z, null, $formatted );
                        }
                        $contentPart .= $formatted;
                    }

                } // PERIOD end
                else { // SINGLE date start
                    if( $utcTime  ||
                        ( $paramsTZIDisSet && isset( $rdatePart[Util::$LCtz] ) &&
                            ! Util::isOffset( $rdatePart[Util::$LCtz] ))) {
                        unset( $rdatePart[Util::$LCtz] );
                    }
                    $parno     = ( Util::isParamsValueSet( $theRdate, Util::$DATE )) ? 3 : null;
                    $formatted = Util::date2strdate( $rdatePart, $parno );
                    if( $utcTime || $paramsTZIDisSet ) {
                        $formatted = str_replace( Util::$Z, null, $formatted );
                    }
                    $contentPart .= $formatted;
                }
                $content .= $contentPart;
                if( $rno < $cnt ) {
                    $content .= Util::$COMMA;
                }
                $rno++;
            } // end foreach(( array_keys( $theRdate[Util::$LCvalue]...
            $output .= Util::createElement( Util::$RDATE, $attributes, $content );
        } // foreach(( array_keys( $rDates...
        return $output;
    }

    /**
     * Return prepared calendar component property rdate input
     *
     * @param array  $rDates
     * @param array  $params
     * @param string $compType
     * @return array
     * @static
     * @since 2.26.7 - 2018-11-29
     * @todo fix exception
     */
    public static function prepInputRdate( array $rDates, $params=null, $compType=null ) {
        $output = [ Util::$LCparams => Util::setParams( $params, Util::$DEFAULTVALUEDATETIME ) ];
        if( Util::isCompInList( $compType, Util::$TZCOMPS )) {
            unset( $output[Util::$LCparams][Util::$TZID] );
            $output[Util::$LCparams][Util::$VALUE] = Util::$DATE_TIME;
        }
        /*  check if PERIOD, if not set */
        if( self::isUnAttributedPeriod( $rDates, $output )) {
            $output[Util::$LCparams][Util::$VALUE] = Util::$PERIOD;
        }
        /* check 1:st date, upd. $parno (opt) and save opt. timezone */
        $date = reset( $rDates );
        if( isset( $output[Util::$LCparams][Util::$VALUE] ) &&
            ( Util::$PERIOD == $output[Util::$LCparams][Util::$VALUE] )) { // PERIOD
            $date = reset( $date );
        }
        self::chkDateCfg( $date, $parno, $output[Util::$LCparams] );
        if( isset( $output[Util::$LCvalue ][Util::$LCtz] ) &&
            ( in_array( strtoupper( $output[Util::$LCvalue ][Util::$LCtz] ), self::$GMTUTCZARR ) ||
            Util::isOffset( $output[Util::$LCvalue ][Util::$LCtz] ))) {
            $toZ = true;
        }
        else {
            $toZ = ( isset( $params[Util::$TZID] ) &&
                in_array( strtoupper( $params[Util::$TZID] ), self::$GMTUTCZARR )) ? true : false;
        }
        Util::existRem( $output[Util::$LCparams], Util::$VALUE, Util::$DATE_TIME ); // remove default
        foreach( $rDates as $rpix => $theRdate ) {
            $wDate = null;
            if( $theRdate instanceof DateTime ) {
                $theRdate = Util::dateTime2Str( $theRdate );
            }
            Util::strDate2arr( $theRdate );
            if( is_array( $theRdate )) {
                if( isset( $output[Util::$LCparams][Util::$VALUE] ) &&
                    ( Util::$PERIOD == $output[Util::$LCparams][Util::$VALUE] )) { // PERIOD
                    foreach( $theRdate as $rix => $rPeriod ) {
                        if( $rPeriod instanceof DateTime ) {
                            $rPeriod = Util::dateTime2Str( $rPeriod );
                        }
                        elseif( $rPeriod instanceof DateInterval ) {
                            $wDate[] = (array) $rPeriod; // fix pre 7.0.5 bug
                            continue;
                        }
                        $wDate2 = null;
                        if( is_array( $rPeriod )) {
                            if( Util::isArrayTimestampDate( $rPeriod )) {    // timestamp
                                if( isset( $rPeriod[Util::$LCtz] ) &&
                                    ! Util::isOffset( $rPeriod[Util::$LCtz] )) {
                                    if( isset( $output[Util::$LCparams][Util::$TZID] )) {
                                        $rPeriod[Util::$LCtz] = $output[Util::$LCparams][Util::$TZID];
                                    }
                                    else {
                                        $output[Util::$LCparams][Util::$TZID] = $rPeriod[Util::$LCtz];
                                    }
                                }
                                $wDate2 = Util::timestamp2date( $rPeriod, $parno );
                            } // end if( Util::isArrayTimestampDate( $rPeriod ))
                            elseif( Util::isArrayDate( $rPeriod )) {
                                $d = ( 3 < count( $rPeriod ))
                                    ? Util::chkDateArr( $rPeriod, $parno )
                                    : Util::chkDateArr( $rPeriod, 6 );
                                if( isset( $d[Util::$LCtz] ) &&
                                    ( Util::$Z != $d[Util::$LCtz] ) &&
                                    Util::isOffset( $d[Util::$LCtz] )) {
                                    $wDate2 = Util::ensureArrDatetime( [Util::$LCvalue => $d], $d[Util::$LCtz], 7 );
                                    unset( $wDate2[Util::$UNPARSEDTEXT] );
                                }
                                else {
                                    $wDate2 = $d;
                                }
                            } // end elseif( Util::isArrayDate( $rPeriod ))
                            elseif(( 1 == count( $rPeriod )) &&
                                   ( 8 <= strlen( reset( $rPeriod )))) { // text-date
                                $wDate2 = Util::strDate2ArrayDate( reset( $rPeriod ), $parno );
                                unset( $wDate2[Util::$UNPARSEDTEXT] );
                            }
                            else {  // array format duration
                                try {  // fix pre 7.0.5 bug
                                    $wDate[] = (array) UtilDuration::conformDateInterval(
                                        new DateInterval(
                                            UtilDuration::duration2str(
                                                UtilDuration::duration2arr( $rPeriod )
                                            )
                                        )
                                    );
                                }
                                catch( Exception $e ) {
                                    // return false; // todo
                                }
                                continue;
                            }
                        } // end if( is_array( $rPeriod ))
                        elseif(( 3 <= strlen( trim( $rPeriod ))) &&    // string format duration
                            ( in_array( $rPeriod[0], UtilDuration::$PREFIXARR ))) {
                            if( UtilDuration::$P != $rPeriod[0] ) {
                                $rPeriod = substr( $rPeriod, 1 );
                            }
                            try {
                                $wDate[] = (array) UtilDuration::conformDateInterval( new DateInterval( $rPeriod ));
                            }
                            catch( Exception $e ) {
                                // return false; // todo
                            }
                            continue;
                        }
                        elseif( 8 <= strlen( trim( $rPeriod ))) {      // text date ex. 2006-08-03 10:12:18
                            $wDate2 = Util::strDate2ArrayDate( $rPeriod, $parno );
                            unset( $wDate2[Util::$UNPARSEDTEXT] );
                        }
                        if(( 0 == $rpix ) && ( 0 == $rix )) {
                            if( isset( $wDate2[Util::$LCtz] ) &&
                                in_array( strtoupper( $wDate2[Util::$LCtz] ), self::$GMTUTCZARR )) {
                                $wDate2[Util::$LCtz] = Util::$Z;
                                $toZ                 = true;
                            }
                        }
                        else {
                            if( isset( $wDate[0][Util::$LCtz] ) &&
                                ( Util::$Z == $wDate[0][Util::$LCtz] ) &&
                                isset( $wDate2[Util::$LCYEAR] )) {
                                $wDate2[Util::$LCtz] = Util::$Z;
                            }
                            else {
                                unset( $wDate2[Util::$LCtz] );
                            }
                        }
                        if( $toZ && isset( $wDate2[Util::$LCYEAR] )) {
                            $wDate2[Util::$LCtz] = Util::$Z;
                        }
                        $wDate[] = $wDate2;
                    } // end foreach( $theRdate as $rix => $rPeriod )
                } // PERIOD end
                elseif( Util::isArrayTimestampDate( $theRdate )) {    // timestamp
                    if( isset( $theRdate[Util::$LCtz] ) && ! Util::isOffset( $theRdate[Util::$LCtz] )) {
                        if( isset( $output[Util::$LCparams][Util::$TZID] )) {
                            $theRdate[Util::$LCtz] = $output[Util::$LCparams][Util::$TZID];
                        }
                        else {
                            $output[Util::$LCparams][Util::$TZID] = $theRdate[Util::$LCtz];
                        }
                    }
                    $wDate = Util::timestamp2date( $theRdate, $parno );
                }
                else {                                                 // date[-time]
                    $wDate = Util::chkDateArr( $theRdate, $parno );
                    if( isset( $wDate[Util::$LCtz] ) && ( Util::$Z != $wDate[Util::$LCtz] ) && Util::isOffset( $wDate[Util::$LCtz] )) {
                        $wDate = Util::ensureArrDatetime( [Util::$LCvalue => $wDate], $wDate[Util::$LCtz], 7 );
                        unset( $wDate[Util::$UNPARSEDTEXT] );
                    }
                }
            } // end if( is_array( $theRdate ))
            elseif( 8 <= strlen( trim( $theRdate ))) {                 // text date ex. 2006-08-03 10:12:18
                $wDate = Util::strDate2ArrayDate( $theRdate, $parno );
                unset( $wDate[Util::$UNPARSEDTEXT] );
                if( $toZ ) {
                    $wDate[Util::$LCtz] = Util::$Z;
                }
            }
            if( ! isset( $output[Util::$LCparams][Util::$VALUE] ) ||
                ( Util::$PERIOD != $output[Util::$LCparams][Util::$VALUE] )) { // no PERIOD
                if(( 0 == $rpix ) && ! $toZ ) {
                    $toZ = ( isset( $wDate[Util::$LCtz] ) &&
                        in_array( strtoupper( $wDate[Util::$LCtz] ), self::$GMTUTCZARR )) ? true : false;
                }
                if( $toZ ) {
                    $wDate[Util::$LCtz] = Util::$Z;
                }
                if( 3 == $parno ) {
                    unset( $wDate[Util::$LCHOUR], $wDate[Util::$LCMIN], $wDate[Util::$LCSEC], $wDate[Util::$LCtz] );
                }
                elseif( isset( $wDate[Util::$LCtz] )) {
                    $wDate[Util::$LCtz] = (string) $wDate[Util::$LCtz];
                }
                if(   isset( $output[Util::$LCparams][Util::$TZID] ) ||
                    ( isset( $output[Util::$LCvalue][0] ) &&
                  ( ! isset( $output[Util::$LCvalue][0][Util::$LCtz] )))) {
                    if( ! $toZ ) {
                        unset( $wDate[Util::$LCtz] );
                    }
                }
            } // end if - no PERIOD
            $output[Util::$LCvalue][] = $wDate;
        } // end foreach( $rDates as $rpix => $theRdate )
        if( 3 == $parno ) {
            $output[Util::$LCparams][Util::$VALUE] = Util::$DATE;
            unset( $output[Util::$LCparams][Util::$TZID] );
        }
        if( $toZ ) {
            unset( $output[Util::$LCparams][Util::$TZID] );
        }
        return $output;
    }
    
    /**
     * Return true if PERIOD is not set BUT is PERIOD
     *
     * @param array  $rDates
     * @param array  $output
     * @return bool
     * @access private
     * @static
     * @since 2.26.7 - 2018-11-21
     */
    private static function isUnAttributedPeriod( array $rDates, array $output ) {
        if( Util::isParamsValueSet( $output, Util::$PERIOD )) {
            return false;
        }
        if( isset( $output[Util::$LCparams][Util::$VALUE] ) &&
            ( Util::isParamsValueSet( $output, Util::$DATE ) ||
              Util::isParamsValueSet( $output, Util::$DATE_TIME ))) {
            return false;
        }
        if( isset( $rDates[0] ) && is_string( $rDates[0] )) {
            return false;
        }
        if( isset( $rDates[0] ) && is_array( $rDates[0] ) && ( 2 != count( $rDates[0] ))) {
            return false;
        }
        if( ! isset( $rDates[0][0] )) {
            return false;
        }
        if( isset( $rDates[0][1] ) && isset( $rDates[0][Util::$LCTIMESTAMP] )) {
            return false;
        }
        $firstOK = false;
        switch( true ) {
            case ( $rDates[0][0] instanceof DateTime ) :
                $firstOK = true;
                break;
            case ( is_array( $rDates[0][0] ) &&
                ( Util::isArrayDate( $rDates[0][0] ) || isset( $rDates[0][0][Util::$LCTIMESTAMP] ))) :
                $firstOK = true;
                break;
            case ( is_string( $rDates[0][0] ) && ( 8 <= strlen( trim( $rDates[0][0] )))) :
                $firstOK = true;
                break;
        }
        if( ! $firstOK ) {
            return false;
        }
        switch( true ) {
            case ( $rDates[0][1] instanceof DateTime ) :
                return true;
                break;
            case ( is_array( $rDates[0][1] ))  :
                return true;
                break;
            case ( is_string( $rDates[0][1] ) && ( 3 <= strlen( trim( $rDates[0][1] )))) :
                return true;
                break;
        }
        return false;
    }

}
