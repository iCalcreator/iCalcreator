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
use DateTimeZone;

use function array_change_key_case;
use function array_keys;
use function checkdate;
use function count;
use function ctype_digit;
use function date;
use function end;
use function get_class;
use function implode;
use function in_array;
use function is_array;
use function is_null;
use function is_string;
use function mktime;
use function sprintf;
use function strcasecmp;
use function strlen;
use function strtoupper;
use function substr;
use function trim;
use function usort;

/**
 * iCalcreator recur support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class UtilRecur
{
    /**
     * Static values for recurrence FREQuence
     *
     * @access private
     * @static
     */
    private static $DAILY   = 'DAILY';
    private static $WEEKLY  = 'WEEKLY';
    private static $MONTHLY = 'MONTHLY';
    private static $YEARLY  = 'YEARLY';
//private static $SECONDLY        = 'SECONDLY';
//private static $MINUTELY        = 'MINUTELY';
//private static $HOURLY          = 'HOURLY';
    private static $DAYNAMES        = [ 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' ];
    private static $YEARCNT_UP      = 'yearcnt_up';
    private static $YEARCNT_DOWN    = 'yearcnt_down';
    private static $MONTHDAYNO_UP   = 'monthdayno_up';
    private static $MONTHDAYNO_DOWN = 'monthdayno_down';
    private static $MONTHCNT_DOWN   = 'monthcnt_down';
    private static $YEARDAYNO_UP    = 'yeardayno_up';
    private static $YEARDAYNO_DOWN  = 'yeardayno_down';
    private static $WEEKNO_UP       = 'weekno_up';
    private static $WEEKNO_DOWN     = 'weekno_down';

    /**
     * Sort recur dates
     *
     * @param string $byDayA
     * @param string $byDayB
     * @return int
     * @access private
     * @static
     */
    private static function recurBydaySort( $byDayA, $byDayB ) {
        static $days = [
            'SU' => 0,
            'MO' => 1,
            'TU' => 2,
            'WE' => 3,
            'TH' => 4,
            'FR' => 5,
            'SA' => 6,
        ];
        return ( $days[substr( $byDayA, -2 )] < $days[substr( $byDayB, -2 )] ) ? -1 : 1;
    }

    /**
     * Return formatted output for calendar component property data value type recur
     *
     * @param string $recurlabel
     * @param array  $recurData
     * @param bool   $allowEmpty
     * @return string
     * @static
     */
    public static function formatRecur( $recurlabel, $recurData, $allowEmpty ) {
        static $FMTFREQEQ        = 'FREQ=%s';
        static $FMTDEFAULTEQ     = ';%s=%s';
        static $FMTOTHEREQ       = ';%s=';
        static $RECURBYDAYSORTER = null;
        if( is_null( $RECURBYDAYSORTER )) {
            $RECURBYDAYSORTER    = [ get_class(), 'recurBydaySort' ];
        }
        if( empty( $recurData )) {
            return null;
        }
        $output = null;
        foreach( $recurData as $rx => $theRule ) {
            if( empty( $theRule[Util::$LCvalue] )) {
                if( $allowEmpty ) {
                    $output .= Util::createElement( $recurlabel );
                }
                continue;
            }
            $attributes = ( isset( $theRule[Util::$LCparams] ))
                ? Util::createParams( $theRule[Util::$LCparams] )
                : null;
            $content1   = $content2 = null;
            foreach( $theRule[Util::$LCvalue] as $ruleLabel => $ruleValue ) {
                $ruleLabel = strtoupper( $ruleLabel );
                switch( $ruleLabel ) {
                    case Util::$FREQ :
                        $content1 .= sprintf( $FMTFREQEQ, $ruleValue );
                        break;
                    case Util::$UNTIL :
                        $parno    = ( isset( $ruleValue[Util::$LCHOUR] )) ? 7 : 3;
                        $content2 .= sprintf( $FMTDEFAULTEQ, Util::$UNTIL,
                                              Util::date2strdate( $ruleValue, $parno )
                        );
                        break;
                    case Util::$COUNT :
                    case Util::$INTERVAL :
                    case Util::$WKST :
                        $content2 .= sprintf( $FMTDEFAULTEQ, $ruleLabel, $ruleValue );
                        break;
                    case Util::$BYDAY :
                        $byday = [ Util::$SP0 ];
                        $bx    = 0;
                        foreach( $ruleValue as $bix => $bydayPart ) {
                            if( ! empty( $byday[$bx] ) &&   // new day
                                ! ctype_digit( substr( $byday[$bx], -1 )) ) {
                                $byday[++$bx] = Util::$SP0;
                            }
                            if( ! is_array( $bydayPart ))   // day without order number
                            {
                                $byday[$bx] .= (string) $bydayPart;
                            }
                            else {                          // day with order number
                                foreach( $bydayPart as $bix2 => $bydayPart2 ) {
                                    $byday[$bx] .= (string) $bydayPart2;
                                }
                            }
                        } // end foreach( $ruleValue as $bix => $bydayPart )
                        if( 1 < count( $byday )) {
                            usort( $byday, $RECURBYDAYSORTER );
                        }
                        $content2 .= sprintf( $FMTDEFAULTEQ, Util::$BYDAY, implode( Util::$COMMA, $byday ));
                        break;
                    default : // BYSECOND/BYMINUTE/BYHOUR/BYMONTHDAY/BYYEARDAY/BYWEEKNO/BYMONTH/BYSETPOS...
                        if( is_array( $ruleValue )) {
                            $content2 .= sprintf( $FMTOTHEREQ, $ruleLabel );
                            $content2 .= implode( Util::$COMMA, $ruleValue );
                        }
                        else {
                            $content2 .= sprintf( $FMTDEFAULTEQ, $ruleLabel, $ruleValue );
                        }
                        break;
                } // end switch( $ruleLabel )
            } // end foreach( $theRule[Util::$LCvalue] )) as $ruleLabel => $ruleValue )
            $output .= Util::createElement( $recurlabel, $attributes, $content1 . $content2 );
        } // end foreach( $recurData as $rx => $theRule )
        return $output;
    }

    /**
     * Convert input format for EXRULE and RRULE to internal format
     *
     * @param array $rexrule
     * @return array
     * @static
     * @since 2.26.7 - 2018-11-23
     */
    public static function setRexrule( $rexrule ) {
        static $BYSECOND = 'BYSECOND';
        static $BYMINUTE = 'BYMINUTE';
        static $BYHOUR   = 'BYHOUR';
        $input = [];
        if( empty( $rexrule )) {
            return $input;
        }
        $rexrule = array_change_key_case( $rexrule, CASE_UPPER );
        foreach( $rexrule as $rexruleLabel => $rexruleValue ) {
            if( Util::$UNTIL != $rexruleLabel ) {
                $input[$rexruleLabel] = $rexruleValue;
            }
            else {
                if( $rexruleValue instanceof DateTime ) {
                    $rexruleValue->setTimezone((new DateTimeZone( Util::$UTC ))); // ensure UTC
                    $rexruleValue = Util::dateTime2Str( $rexruleValue );
                }
                else {
                    Util::strDate2arr( $rexruleValue );
                }
                if( Util::isArrayTimestampDate( $rexruleValue )) { // timestamp, always date-time UTC
                    $input[$rexruleLabel] = Util::timestamp2date( $rexruleValue, 7, Util::$UTC );
                }
                elseif( Util::isArrayDate( $rexruleValue )) { // date or UTC date-time
                    $parno = ( isset( $rexruleValue[Util::$LCHOUR] ) ||
                               isset( $rexruleValue[4] )) ? 7 : 3;
                    $d     = Util::chkDateArr( $rexruleValue, $parno );
                    if(( 3 < $parno ) &&
                        isset( $d[Util::$LCtz] ) &&
                        ( Util::$Z != $d[Util::$LCtz] ) &&
                        Util::isOffset( $d[Util::$LCtz] )) {
                        $input[$rexruleLabel] = Util::ensureArrDatetime( [Util::$LCvalue => $d], $d[Util::$LCtz],7 );
                        unset( $input[$rexruleLabel][Util::$UNPARSEDTEXT] );
                    }
                    else {
                        $input[$rexruleLabel] = $d;
                    }
                }
                elseif( 8 <= strlen( trim( $rexruleValue )) ) { // ex. textual date-time 2006-08-03 10:12:18 => UTC
                    $input[$rexruleLabel] = Util::strDate2ArrayDate( $rexruleValue );
                    unset( $input[$rexruleLabel][Util::$UNPARSEDTEXT] );
                }
                if(( 3 < count( $input[$rexruleLabel] )) &&
                    ! isset( $input[$rexruleLabel][Util::$LCtz] )) {
                    $input[$rexruleLabel][Util::$LCtz] = Util::$Z;
                }
            }
        } // end foreach( $rexrule as $rexruleLabel => $rexruleValue )
        /* set recurrence rule specification in rfc2445 order */
        $input2 = [];
        if( isset( $input[Util::$FREQ] )) {
            $input2[Util::$FREQ] = $input[Util::$FREQ];
        }
        if( isset( $input[Util::$UNTIL] )) {
            $input2[Util::$UNTIL] = $input[Util::$UNTIL];
        }
        elseif( isset( $input[Util::$COUNT] )) {
            $input2[Util::$COUNT] = $input[Util::$COUNT];
        }
        if( isset( $input[Util::$INTERVAL] )) {
            $input2[Util::$INTERVAL] = $input[Util::$INTERVAL];
        }
        if( isset( $input[$BYSECOND] )) {
            $input2[$BYSECOND] = $input[$BYSECOND];
        }
        if( isset( $input[$BYMINUTE] )) {
            $input2[$BYMINUTE] = $input[$BYMINUTE];
        }
        if( isset( $input[$BYHOUR] )) {
            $input2[$BYHOUR] = $input[$BYHOUR];
        }
        if( isset( $input[Util::$BYDAY] )) {
            if( ! is_array( $input[Util::$BYDAY] )) { // ensure upper case.. .
                $input2[Util::$BYDAY] = \strtoupper( $input[Util::$BYDAY] );
            }
            else {
                foreach( $input[Util::$BYDAY] as $BYDAYx => $BYDAYv ) {
                    if( 0 == strcasecmp( Util::$DAY, $BYDAYx )) {
                        $input2[Util::$BYDAY][Util::$DAY] = strtoupper( $BYDAYv );
                    }
                    elseif( ! is_array( $BYDAYv )) {
                        $input2[Util::$BYDAY][$BYDAYx] = $BYDAYv;
                    }
                    else {
                        foreach( $BYDAYv as $BYDAYx2 => $BYDAYv2 ) {
                            if( 0 == strcasecmp( Util::$DAY, $BYDAYx2 )) {
                                $input2[Util::$BYDAY][$BYDAYx][Util::$DAY] = strtoupper( $BYDAYv2 );
                            }
                            else {
                                $input2[Util::$BYDAY][$BYDAYx][$BYDAYx2] = $BYDAYv2;
                            }
                        }
                    }
                }
            }
        } // end if( isset( $input[Util::$BYDAY] ))
        if( isset( $input[Util::$BYMONTHDAY] )) {
            $input2[Util::$BYMONTHDAY] = $input[Util::$BYMONTHDAY];
        }
        if( isset( $input[Util::$BYYEARDAY] )) {
            $input2[Util::$BYYEARDAY] = $input[Util::$BYYEARDAY];
        }
        if( isset( $input[Util::$BYWEEKNO] )) {
            $input2[Util::$BYWEEKNO] = $input[Util::$BYWEEKNO];
        }
        if( isset( $input[Util::$BYMONTH] )) {
            $input2[Util::$BYMONTH] = $input[Util::$BYMONTH];
        }
        if( isset( $input[Util::$BYSETPOS] )) {
            $input2[Util::$BYSETPOS] = $input[Util::$BYSETPOS];
        }
        if( isset( $input[Util::$WKST] )) {
            $input2[Util::$WKST] = $input[Util::$WKST];
        }
        return $input2;
    }

    /**
     * Update array $result with dates based on a recur pattern
     *
     * If missing, UNTIL is set 1 year from startdate (emergency break)
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param array $result   array to update, array([Y-m-d] => bool)
     * @param array $recur    pattern for recurrency (only value part, params ignored)
     * @param mixed $wDate    component start date, string / array / (datetime) obj
     * @param mixed $fcnStart start date, string / array / (datetime) obj
     * @param mixed $fcnEnd   end date, string / array / (datetime) obj
     * @static
     * @todo   BYHOUR, BYMINUTE, BYSECOND, WEEKLY at year end/start OR not at all
     */
    public static function recur2date(
        & $result,
        $recur,
        $wDate,
        $fcnStart,
        $fcnEnd = false
    ) {
        static $YEAR2DAYARR = [ 'YEARLY', 'MONTHLY', 'WEEKLY', 'DAILY' ];
        static $SU  = 'SU';
        static $HIS = '%02d%02d%02d';
        self::reFormatDate( $wDate );
        $wDateYMD   = Util::getYMDString( $wDate );
        $wDateHis   = Util::getHisString( $wDate );
        $untilHis   = $wDateHis;
        self::reFormatDate( $fcnStart );
        $fcnStartYMD = Util::getYMDString( $fcnStart );
        if( ! empty( $fcnEnd )) {
            self::reFormatDate( $fcnEnd );
        }
        else {
            $fcnEnd                = $fcnStart;
            $fcnEnd[Util::$LCYEAR] += 1;
        }
        $fcnEndYMD = Util::getYMDString( $fcnEnd );
        if( ! isset( $recur[Util::$COUNT] ) && ! isset( $recur[Util::$UNTIL] )) {
            $recur[Util::$UNTIL] = $fcnEnd;
        } // create break
        if( isset( $recur[Util::$UNTIL] )) {
            foreach( $recur[Util::$UNTIL] as $k => $v ) {
                if( ctype_digit( $v )) {
                    $recur[Util::$UNTIL][$k] = (int) $v;
                }
            }
            unset( $recur[Util::$UNTIL][Util::$LCtz] );
            if( $fcnEnd > $recur[Util::$UNTIL] ) {
                $fcnEnd    = $recur[Util::$UNTIL]; // emergency break
                $fcnEndYMD = Util::getYMDString( $fcnEnd );
            }
            if( isset( $recur[Util::$UNTIL][Util::$LCHOUR] )) {
                $untilHis = Util::getHisString( $recur[Util::$UNTIL] );
            }
            else {
                $untilHis = sprintf( $HIS, 23, 59, 59 );
            }
        } // end if( isset( $recur[Util::$UNTIL] ))
        if( $wDateYMD > $fcnEndYMD ) {
            return []; // nothing to do.. .
        }
        if( ! isset( $recur[Util::$FREQ] )) { // "MUST be specified.. ."
            $recur[Util::$FREQ] = self::$DAILY;
        } // ??
        $wkst = ( isset( $recur[Util::$WKST] ) && ( $SU == $recur[Util::$WKST] )) ? 24 * 60 * 60 : 0; // ??
        if( ! isset( $recur[Util::$INTERVAL] )) {
            $recur[Util::$INTERVAL] = 1;
        }
        $recurCount = ( ! isset( $recur[Util::$BYSETPOS] )) ? 1 : 0; // DTSTART \counts as the first occurrence
        /* find out how to step up dates and set index for interval \count */
        $step = [];
        if( self::$YEARLY == $recur[Util::$FREQ] ) {
            $step[Util::$LCYEAR] = 1;
        }
        elseif( self::$MONTHLY == $recur[Util::$FREQ] ) {
            $step[Util::$LCMONTH] = 1;
        }
        elseif( self::$WEEKLY == $recur[Util::$FREQ] ) {
            $step[Util::$LCDAY] = 7;
        }
        else {
            $step[Util::$LCDAY] = 1;
        }
        if( isset( $step[Util::$LCYEAR] ) && isset( $recur[Util::$BYMONTH] )) {
            $step = [ Util::$LCMONTH => 1 ];
        }
        if( empty( $step ) && isset( $recur[Util::$BYWEEKNO] )) { // ??
            $step = [ Util::$LCDAY => 7 ];
        }
        if( isset( $recur[Util::$BYYEARDAY] ) ||
            isset( $recur[Util::$BYMONTHDAY] ) ||
            isset( $recur[Util::$BYDAY] )) {
            $step = [ Util::$LCDAY => 1 ];
        }
        $intervalArr = [];
        if( 1 < $recur[Util::$INTERVAL] ) {
            $intervalIx  = self::recurIntervalIx( $recur[Util::$FREQ], $wDate, $wkst );
            $intervalArr = [ $intervalIx => 0 ];
        }
        if( isset( $recur[Util::$BYSETPOS] )) { // save start date + weekno
            $bysetPosymd1 = $bysetPosymd2 = $bysetPosw1 = $bysetPosw2 = [];
            if( is_array( $recur[Util::$BYSETPOS] )) {
                foreach( $recur[Util::$BYSETPOS] as $bix => $bval ) {
                    $recur[Util::$BYSETPOS][$bix] = (int) $bval;
                }
            }
            else {
                $recur[Util::$BYSETPOS] = [ (int) $recur[Util::$BYSETPOS] ];
            }
            if( self::$YEARLY == $recur[Util::$FREQ] ) {
                $wDate[Util::$LCMONTH] = $wDate[Util::$LCDAY] = 1; // start from beginning of year
                $wDateYMD              = Util::getYMDString( $wDate );
                self::stepDate( $fcnEnd, $fcnEndYMD, [ Util::$LCYEAR => 1 ] ); // make sure to \count last year
            }
            elseif( self::$MONTHLY == $recur[Util::$FREQ] ) {
                $wDate[Util::$LCDAY] = 1; // start from beginning of month
                $wDateYMD            = Util::getYMDString( $wDate );
                self::stepDate( $fcnEnd, $fcnEndYMD, [ Util::$LCMONTH => 1 ] ); // make sure to \count last month
            }
            else {
                self::stepDate( $fcnEnd, $fcnEndYMD, $step );
            } // make sure to \count whole last period
            $bysetPosWold = self::getWeeNumber(0,0, $wkst, $wDate[Util::$LCMONTH], $wDate[Util::$LCDAY], $wDate[Util::$LCYEAR] );
            $bysetPosYold = $wDate[Util::$LCYEAR];
            $bysetPosMold = $wDate[Util::$LCMONTH];
            $bysetPosDold = $wDate[Util::$LCDAY];
        } // end if( isset( $recur[Util::$BYSETPOS] ))
        else {
            self::stepDate( $wDate, $wDateYMD, $step );
        }
        $yearOld = null;
        $dayCnts  = [];
        /* MAIN LOOP */
        while( true ) {
            if( $wDateYMD . $wDateHis > $fcnEndYMD . $untilHis ) {
                break;
            }
            if( isset( $recur[Util::$COUNT] ) &&
                ( $recurCount >= $recur[Util::$COUNT] )) {
                break;
            }
            if( $yearOld != $wDate[Util::$LCYEAR] ) { // $yearOld=null 1:st time
                $yearOld  = $wDate[Util::$LCYEAR];
                $dayCnts  = self::initDayCnts( $wDate, $recur, $wkst );
            }

            /* check interval */
            if( 1 < $recur[Util::$INTERVAL] ) {
                /* create interval index */
                $intervalIx = self::recurIntervalIx( $recur[Util::$FREQ], $wDate, $wkst );
                /* check interval */
                $currentKey = array_keys( $intervalArr );
                $currentKey = end( $currentKey ); // get last index
                if( $currentKey != $intervalIx ) {
                    $intervalArr = [ $intervalIx => ( $intervalArr[$currentKey] + 1 ) ];
                }
                if(( $recur[Util::$INTERVAL] != $intervalArr[$intervalIx] ) &&
                    ( 0 != $intervalArr[$intervalIx] )) {
                    /* step up date */
                    self::stepDate( $wDate, $wDateYMD, $step );
                    continue;
                }
                else // continue within the selected interval
                {
                    $intervalArr[$intervalIx] = 0;
                }
            } // endif( 1 < $recur['INTERVAL'] )
            $updateOK = true;
            if( $updateOK && isset( $recur[Util::$BYMONTH] )) {
                $updateOK = self::recurBYcntcheck( $recur[Util::$BYMONTH], $wDate[Util::$LCMONTH],
                    ( $wDate[Util::$LCMONTH] - 13 )
                );
            }
            if( $updateOK && isset( $recur[Util::$BYWEEKNO] )) {
                $updateOK = self::recurBYcntcheck(
                    $recur[Util::$BYWEEKNO],
                    $dayCnts[$wDate[Util::$LCMONTH]][$wDate[Util::$LCDAY]][self::$WEEKNO_UP],
                    $dayCnts[$wDate[Util::$LCMONTH]][$wDate[Util::$LCDAY]][self::$WEEKNO_DOWN]
                );
            }
            if( $updateOK && isset( $recur[Util::$BYYEARDAY] )) {
                $updateOK = self::recurBYcntcheck(
                    $recur[Util::$BYYEARDAY],
                    $dayCnts[$wDate[Util::$LCMONTH]][$wDate[Util::$LCDAY]][self::$YEARCNT_UP],
                    $dayCnts[$wDate[Util::$LCMONTH]][$wDate[Util::$LCDAY]][self::$YEARCNT_DOWN]
                );
            }
            if( $updateOK && isset( $recur[Util::$BYMONTHDAY] )) {
                $updateOK = self::recurBYcntcheck(
                    $recur[Util::$BYMONTHDAY],
                    $wDate[Util::$LCDAY],
                    $dayCnts[$wDate[Util::$LCMONTH]][$wDate[Util::$LCDAY]][self::$MONTHCNT_DOWN]
                );
            }
            if( $updateOK && isset( $recur[Util::$BYDAY] )) {
                $updateOK = false;
                $m        = $wDate[Util::$LCMONTH];
                $d        = $wDate[Util::$LCDAY];
                if( isset( $recur[Util::$BYDAY][Util::$DAY] )) { // single day, opt with year/month day order no
                    $daynoExists = $daynoSw = $dayNameSw = false;
                    if( $recur[Util::$BYDAY][Util::$DAY] == $dayCnts[$m][$d][Util::$DAY] ) {
                        $dayNameSw = true;
                    }
                    if( isset( $recur[Util::$BYDAY][0] )) {
                        $daynoExists = true;
                        if(( isset( $recur[Util::$FREQ] ) && ( $recur[Util::$FREQ] == self::$MONTHLY )) ||
                            isset( $recur[Util::$BYMONTH] )) {
                            $daynoSw = self::recurBYcntcheck(
                                $recur[Util::$BYDAY][0],
                                $dayCnts[$m][$d][self::$MONTHDAYNO_UP],
                                $dayCnts[$m][$d][self::$MONTHDAYNO_DOWN]
                            );
                        }
                        elseif( isset( $recur[Util::$FREQ] ) &&
                            ( $recur[Util::$FREQ] == self::$YEARLY )) {
                            $daynoSw = self::recurBYcntcheck(
                                $recur[Util::$BYDAY][0],
                                $dayCnts[$m][$d][self::$YEARDAYNO_UP],
                                $dayCnts[$m][$d][self::$YEARDAYNO_DOWN]
                            );
                        }
                    }
                    if(( $daynoExists && $daynoSw && $dayNameSw ) ||
                        ( ! $daynoExists && ! $daynoSw && $dayNameSw )) {
                        $updateOK = true;
                    }
                } // end if( isset( $recur[Util::$BYDAY][Util::$DAY] ))
                else {
                    foreach( $recur[Util::$BYDAY] as $bydayvalue ) {
                        $daynoExists = $daynoSw = $dayNameSw = false;
                        if( isset( $bydayvalue[Util::$DAY] ) &&
                            ( $bydayvalue[Util::$DAY] == $dayCnts[$m][$d][Util::$DAY] )) {
                            $dayNameSw = true;
                        }
                        if( isset( $bydayvalue[0] )) {
                            $daynoExists = true;
                            if(( isset( $recur[Util::$FREQ] ) &&
                                       ( $recur[Util::$FREQ] == self::$MONTHLY )) ||
                                  isset( $recur[Util::$BYMONTH] )) {
                                $daynoSw = self::recurBYcntcheck(
                                    $bydayvalue[Util::$ZERO],
                                    $dayCnts[$m][$d][self::$MONTHDAYNO_UP],
                                    $dayCnts[$m][$d][self::$MONTHDAYNO_DOWN]
                                );
                            }
                            elseif( isset( $recur[Util::$FREQ] ) &&
                                         ( $recur[Util::$FREQ] == self::$YEARLY )) {
                                $daynoSw = self::recurBYcntcheck(
                                    $bydayvalue[Util::$ZERO],
                                    $dayCnts[$m][$d][self::$YEARDAYNO_UP],
                                    $dayCnts[$m][$d][self::$YEARDAYNO_DOWN]
                                );
                            }
                        } // end if( isset( $bydayvalue[0] ))
                        if(( $daynoExists && $daynoSw && $dayNameSw ) ||
                            ( ! $daynoExists && ! $daynoSw && $dayNameSw )) {
                            $updateOK = true;
                            break;
                        }
                    } // end foreach( $recur[Util::$BYDAY] as $bydayvalue )
                } // end else
            } // end if( $updateOK && isset( $recur[Util::$BYDAY] ))
            /* check BYSETPOS */
            if( $updateOK ) {
                if(      isset( $recur[Util::$BYSETPOS] ) &&
                    ( in_array( $recur[Util::$FREQ], $YEAR2DAYARR )) ) {
                    if( isset( $recur[self::$WEEKLY] )) {
                        if( $bysetPosWold == $dayCnts[$wDate[Util::$LCMONTH]][$wDate[Util::$LCDAY]][self::$WEEKNO_UP] ) {
                            $bysetPosw1[] = $wDateYMD;
                        }
                        else {
                            $bysetPosw2[] = $wDateYMD;
                        }
                    }
                    else {
                        if(( isset( $recur[Util::$FREQ] ) &&
                                ( self::$YEARLY == $recur[Util::$FREQ] ) &&
                                ( $bysetPosYold == $wDate[Util::$LCYEAR] )) ||
                           ( isset( $recur[Util::$FREQ] ) &&
                               ( self::$MONTHLY == $recur[Util::$FREQ] ) &&
                                (( $bysetPosYold == $wDate[Util::$LCYEAR] ) &&
                                    ( $bysetPosMold == $wDate[Util::$LCMONTH] )) ) ||
                           ( isset( $recur[Util::$FREQ] ) &&
                               ( self::$DAILY == $recur[Util::$FREQ] ) &&
                                (( $bysetPosYold == $wDate[Util::$LCYEAR] ) &&
                                    ( $bysetPosMold == $wDate[Util::$LCMONTH] ) &&
                                    ( $bysetPosDold == $wDate[Util::$LCDAY] )) )) {
                            $bysetPosymd1[] = $wDateYMD;
                        }
                        else {
                            $bysetPosymd2[] = $wDateYMD;
                        }
                    } // end else
                }
                else {
                    if( checkdate((int) $wDate[Util::$LCMONTH], (int) $wDate[Util::$LCDAY], (int) $wDate[Util::$LCYEAR] )) {
                        /* update result array if BYSETPOS is not set */
                        $recurCount++;
                        if( $fcnStartYMD <= $wDateYMD ) { // only output within period
                            $result[$wDateYMD] = true;
                        }
                    }
                    $updateOK = false;
                }
            }
            /* step up date */
            self::stepDate( $wDate, $wDateYMD, $step );
            /* check if BYSETPOS is set for updating result array */
            if( $updateOK && isset( $recur[Util::$BYSETPOS] )) {
                $bysetPos = false;
                if( isset( $recur[Util::$FREQ] ) &&
                    ( self::$YEARLY == $recur[Util::$FREQ] ) &&
                    ( $bysetPosYold != $wDate[Util::$LCYEAR] )) {
                    $bysetPos     = true;
                    $bysetPosYold = $wDate[Util::$LCYEAR];
                }
                elseif( isset( $recur[Util::$FREQ] ) &&
                    ( self::$MONTHLY == $recur[Util::$FREQ] &&
                        (( $bysetPosYold != $wDate[Util::$LCYEAR] ) ||
                            ( $bysetPosMold != $wDate[Util::$LCMONTH] )) )) {
                    $bysetPos     = true;
                    $bysetPosYold = $wDate[Util::$LCYEAR];
                    $bysetPosMold = $wDate[Util::$LCMONTH];
                }
                elseif( isset( $recur[Util::$FREQ] ) &&
                    ( self::$WEEKLY == $recur[Util::$FREQ] )) {
                    $weekNo = self::getWeeNumber(0,0, $wkst, $wDate[Util::$LCMONTH], $wDate[Util::$LCDAY], $wDate[Util::$LCYEAR] );
                    if( $bysetPosWold != $weekNo ) {
                        $bysetPosWold = $weekNo;
                        $bysetPos     = true;
                    }
                }
                elseif( isset( $recur[Util::$FREQ] ) &&
                    ( self::$DAILY == $recur[Util::$FREQ] ) &&
                    (( $bysetPosYold != $wDate[Util::$LCYEAR] ) ||
                     ( $bysetPosMold != $wDate[Util::$LCMONTH] ) ||
                     ( $bysetPosDold != $wDate[Util::$LCDAY] )) ) {
                    $bysetPos     = true;
                    $bysetPosYold = $wDate[Util::$LCYEAR];
                    $bysetPosMold = $wDate[Util::$LCMONTH];
                    $bysetPosDold = $wDate[Util::$LCDAY];
                }
                if( $bysetPos ) {
                    if( isset( $recur[Util::$BYWEEKNO] )) {
                        $bysetPosArr1 = &$bysetPosw1;
                        $bysetPosArr2 = &$bysetPosw2;
                    }
                    else {
                        $bysetPosArr1 = &$bysetPosymd1;
                        $bysetPosArr2 = &$bysetPosymd2;
                    }

                    foreach( $recur[Util::$BYSETPOS] as $ix ) {
                        if( 0 > $ix ) { // both positive and negative BYSETPOS allowed
                            $ix = ( count( $bysetPosArr1 ) + $ix + 1 );
                        }
                        $ix--;
                        if( isset( $bysetPosArr1[$ix] )) {
                            if( $fcnStartYMD <= $bysetPosArr1[$ix] ) { // only output within period
                                $result[$bysetPosArr1[$ix]] = true;
                            }
                            $recurCount++;
                        }
                        if( isset( $recur[Util::$COUNT] ) && ( $recurCount >= $recur[Util::$COUNT] )) {
                            break;
                        }
                    }
                    $bysetPosArr1 = $bysetPosArr2;
                    $bysetPosArr2 = [];
                } // end if( $bysetPos )
            } // end if( $updateOK && isset( $recur['BYSETPOS'] ))
        } // end while( true )
    }

    /**
     * Checking BYDAY (etc) hits, recur2date help function
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.6.12 - 2011-01-03
     * @param array $BYvalue
     * @param int   $upValue
     * @param int   $downValue
     * @return bool
     * @access private
     * @static
     */
    private static function recurBYcntcheck( $BYvalue, $upValue, $downValue ) {
        if( is_array( $BYvalue ) && ( in_array( $upValue, $BYvalue ) || in_array( $downValue, $BYvalue )) ) {
            return true;
        }
        elseif(( $BYvalue == $upValue ) || ( $BYvalue == $downValue )) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * (re-)Calculate internal index, recur2date help function
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param string $freq
     * @param array  $date
     * @param int    $wkst
     * @return bool
     * @access private
     * @static
     */
    private static function recurIntervalIx( $freq, $date, $wkst ) {
        /* create interval index */
        switch( $freq ) {
            case self::$YEARLY :
                $intervalIx = $date[Util::$LCYEAR];
                break;
            case self::$MONTHLY :
                $intervalIx = $date[Util::$LCYEAR] . Util::$MINUS . $date[Util::$LCMONTH];
                break;
            case self::$WEEKLY :
                $intervalIx = self::getWeeNumber(0,0, $wkst, $date[Util::$LCMONTH], $date[Util::$LCDAY], $date[Util::$LCYEAR] );
                break;
            case self::$DAILY :
            default:
                $intervalIx = $date[Util::$LCYEAR] . Util::$MINUS . $date[Util::$LCMONTH] . Util::$MINUS . $date[Util::$LCDAY];
                break;
        }
        return $intervalIx;
    }

    /**
     * Return updated date, array and timpstamp
     *
     * @param array  $date    date to step
     * @param string $dateYMD date YMD
     * @param array  $step    default array( Util::$LCDAY => 1 )
     * @return void
     * @access private
     * @static
     */
    private static function stepDate( & $date, & $dateYMD, $step = null ) {
        if( is_null( $step )) {
            $step = [ Util::$LCDAY => 1 ];
        }
        if( ! isset( $date[Util::$LCHOUR] )) {
            $date[Util::$LCHOUR] = 0;
        }
        if( ! isset( $date[Util::$LCMIN] )) {
            $date[Util::$LCMIN] = 0;
        }
        if( ! isset( $date[Util::$LCSEC] )) {
            $date[Util::$LCSEC] = 0;
        }
        if( isset( $step[Util::$LCDAY] )) {
            $daysInMonth = self::getDaysInMonth( 
                $date[Util::$LCHOUR], 
                $date[Util::$LCMIN], 
                $date[Util::$LCSEC], 
                $date[Util::$LCMONTH], 
                $date[Util::$LCDAY], 
                $date[Util::$LCYEAR]
            );
        }
        foreach( $step as $stepix => $stepvalue ) {
            $date[$stepix] += $stepvalue;
        }
        if( isset( $step[Util::$LCMONTH] )) {
            if( 12 < $date[Util::$LCMONTH] ) {
                $date[Util::$LCYEAR]  += 1;
                $date[Util::$LCMONTH] -= 12;
            }
        }
        elseif( isset( $step[Util::$LCDAY] )) {
            if( $daysInMonth < $date[Util::$LCDAY] ) {
                $date[Util::$LCDAY]   -= $daysInMonth;
                $date[Util::$LCMONTH] += 1;
                if( 12 < $date[Util::$LCMONTH] ) {
                    $date[Util::$LCYEAR]  += 1;
                    $date[Util::$LCMONTH] -= 12;
                }
            }
        }
        $dateYMD = Util::getYMDString( $date );
    }

    /**
     * Return initiated $dayCnts
     *
     * @param array $wDate
     * @param array $recur
     * @param int   $wkst
     * @return array
     * @access private
     * @static
     */
    private static function initDayCnts( array $wDate, array $recur, $wkst ) {
        $dayCnts    = [];
        $yearDayCnt = [];
        $yearDays   = 0;
        foreach( self::$DAYNAMES as $dn ) {
            $yearDayCnt[$dn] = 0;
        }
        for( $m = 1; $m <= 12; $m++ ) { // \count up and update up-counters
            $dayCnts[$m] = [];
            $weekDayCnt  = [];
            foreach( self::$DAYNAMES as $dn ) {
                $weekDayCnt[$dn] = 0;
            }
            $daysInMonth = self::getDaysInMonth( 0, 0, 0, $m, 1, $wDate[Util::$LCYEAR] );
            for( $d = 1; $d <= $daysInMonth; $d++ ) {
                $dayCnts[$m][$d] = [];
                if( isset( $recur[Util::$BYYEARDAY] )) {
                    $yearDays++;
                    $dayCnts[$m][$d][self::$YEARCNT_UP] = $yearDays;
                }
                if( isset( $recur[Util::$BYDAY] )) {
                    $day = self::getDayInWeek( 0, 0, 0, $m, $d, $wDate[Util::$LCYEAR] );
                    $dayCnts[$m][$d][Util::$DAY] = $day;
                    $weekDayCnt[$day]++;
                    $dayCnts[$m][$d][self::$MONTHDAYNO_UP] = $weekDayCnt[$day];
                    $yearDayCnt[$day]++;
                    $dayCnts[$m][$d][self::$YEARDAYNO_UP] = $yearDayCnt[$day];
                }
                if( isset( $recur[Util::$BYWEEKNO] ) || ( $recur[Util::$FREQ] == self::$WEEKLY )) {
                    $dayCnts[$m][$d][self::$WEEKNO_UP] = self::getWeeNumber(0,0, $wkst, $m, $d, $wDate[Util::$LCYEAR] );
                }
            } // end for( $d   = 1; $d <= $daysInMonth; $d++ )
        } // end for( $m = 1; $m <= 12; $m++ )
        $daycnt     = 0;
        $yearDayCnt = [];
        if( isset( $recur[Util::$BYWEEKNO] ) ||
            ( $recur[Util::$FREQ] == self::$WEEKLY )) {
            $weekNo = null;
            for( $d = 31; $d > 25; $d-- ) { // get last weekno for year
                if( ! $weekNo ) {
                    $weekNo = $dayCnts[12][$d][self::$WEEKNO_UP];
                }
                elseif( $weekNo < $dayCnts[12][$d][self::$WEEKNO_UP] ) {
                    $weekNo = $dayCnts[12][$d][self::$WEEKNO_UP];
                    break;
                }
            }
        }
        for( $m = 12; $m > 0; $m-- ) { // count down and update down-counters
            $weekDayCnt = [];
            foreach( self::$DAYNAMES as $dn ) {
                $yearDayCnt[$dn] = $weekDayCnt[$dn] = 0;
            }
            $monthCnt    = 0;
            $daysInMonth = self::getDaysInMonth( 0, 0, 0, $m, 1, $wDate[Util::$LCYEAR] );
            for( $d = $daysInMonth; $d > 0; $d-- ) {
                if( isset( $recur[Util::$BYYEARDAY] )) {
                    $daycnt                              -= 1;
                    $dayCnts[$m][$d][self::$YEARCNT_DOWN] = $daycnt;
                }
                if( isset( $recur[Util::$BYMONTHDAY] )) {
                    $monthCnt                             -= 1;
                    $dayCnts[$m][$d][self::$MONTHCNT_DOWN] = $monthCnt;
                }
                if( isset( $recur[Util::$BYDAY] )) {
                    $day                                     = $dayCnts[$m][$d][Util::$DAY];
                    $weekDayCnt[$day]                       -= 1;
                    $dayCnts[$m][$d][self::$MONTHDAYNO_DOWN] = $weekDayCnt[$day];
                    $yearDayCnt[$day]                       -= 1;
                    $dayCnts[$m][$d][self::$YEARDAYNO_DOWN]  = $yearDayCnt[$day];
                }
                if( isset( $recur[Util::$BYWEEKNO] ) || ( $recur[Util::$FREQ] == self::$WEEKLY )) {
                    $dayCnts[$m][$d][self::$WEEKNO_DOWN] = ( $dayCnts[$m][$d][self::$WEEKNO_UP] - $weekNo - 1 );
                }
            }
        } // end for( $m = 12; $m > 0; $m-- )
        return $dayCnts;
    }

    /**
     * Return a reformatted input date
     *
     * @param mixed $aDate
     * @access private
     * @static
     */
    private static function reFormatDate( & $aDate ) {
        static $YMDHIS2 = 'Y-m-d H:i:s';
        switch( true ) {
            case ( is_string( $aDate )) :
                Util::strDate2arr( $aDate );
                break;
            case ( $aDate instanceof DateTime ) :
                $aDate = $aDate->format( $YMDHIS2 );
                Util::strDate2arr( $aDate );
                break;
            default :
                break;
        }
        foreach( $aDate as $k => $v ) {
            if( ctype_digit( $v )) {
                $aDate[$k] = (int) $v;
            }
        }
    }

    /**
     * Return week number
     *
     * @param int $hour
     * @param int $min
     * @param int $sec
     * @param int $month
     * @param int $day
     * @param int $year
     * @return int
     * @access private
     * @static
     */
    private static function getWeeNumber( $hour, $min, $sec, $month, $day, $year ) {
        static $W = 'W';
        return (int) date( $W, mktime( $hour, $min, $sec, $month, $day, $year ));
    }

    /**
     * Return number of days in month
     *
     * @param int $hour
     * @param int $min
     * @param int $sec
     * @param int $month
     * @param int $day
     * @param int $year
     * @return int
     * @access private
     * @static
     */
    private static function getDaysInMonth( $hour, $min, $sec, $month, $day, $year ) {
        static $T = 't';
        return (int) date( $T, mktime( $hour, $min, $sec, $month, $day, $year ));
    }

    /**
     * Return day in week
     *
     * @param int $hour
     * @param int $min
     * @param int $sec
     * @param int $month
     * @param int $day
     * @param int $year
     * @return string
     * @access private
     * @static
     */
    private static function getDayInWeek( $hour, $min, $sec, $month, $day, $year ) {
        static $W = 'w';
        $dayNo = (int) date( $W, mktime( $hour, $min, $sec, $month, $day, $year ));
        return self::$DAYNAMES[$dayNo];
    }

}
