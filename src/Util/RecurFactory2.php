<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
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
use Exception;
use Kigkonsult\Icalcreator\Vcalendar;
use LogicException;

use function array_flip;
use function array_keys;
use function array_merge;
use function array_values;
use function array_unique;
use function checkdate;
use function count;
use function ctype_digit;
use function end;
use function explode;
use function in_array;
use function is_array;
use function reset;
use function sort;
use function sprintf;
use function substr;
use function var_export;

/**
 * iCalcreator 'newer' recur support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.27.28 - 2020-09-10
 */
class RecurFactory2
{
    /*
     * @var string  DateTime format keys
     */
    private static $YMD  = 'Ymd';
    private static $LCD  = 'd'; // day NN
    private static $LCJ  = 'j'; // day [N]N
    private static $LCM  = 'm'; // month
    private static $LCT  = 't'; // number of days in month
    private static $UCY  = 'Y'; // year NNNN
    private static $LCW  = 'w'; // day of week number
    private static $UCW  = 'W'; // week number

    /*
     * @var string  DateTime nodify string
     */
    private static $FMTX = '%d days';

    /**
     * @var string
     */
    private static $COMMA = ',';

    /*
     * @var array   troublesome simple recurs keys
     */
    private static $RECURBYX  = [
        Vcalendar::BYSECOND,
        Vcalendar::BYMINUTE,
        Vcalendar::BYHOUR,
        Vcalendar::BYMONTHDAY,
        Vcalendar::BYYEARDAY,
        Vcalendar::BYWEEKNO,
        Vcalendar::BYSETPOS,
        Vcalendar::WKST
    ];

    /**
     * Asserts recur
     *
     * @param array $recur
     * @throws LogicException
     * @since  2.27.26 - 2020-09-10
     */
    public static function assertRecur( array $recur )
    {
        $recurDisp = var_export( $recur, true );
        static $ERR1TXT = '#1 The FREQ rule part MUST be specified in the recurrence rule.';
        if( ! isset( $recur[Vcalendar::FREQ] )) {
            throw new LogicException( $ERR1TXT . $recurDisp );
        }
        static $ERR2TXT = '#2 NO BYDAY days : ';
        static $ERR3TXT = '#3 Unkown BYDAY day : ';
        $cntDays        = 0;
        if( isset( $recur[Vcalendar::BYDAY] )) {
            foreach( $recur[Vcalendar::BYDAY] as $BYDAYx => $BYDAYv ) {
                if(((int) $BYDAYx === $BYDAYx ) && is_array( $BYDAYv )) {
                    foreach( $BYDAYv as $BYDAYx2 => $BYDAYv2 ) {
                        if( Vcalendar::DAY === $BYDAYx2 ) {
                            if( ! in_array( $BYDAYv2, RecurFactory::$DAYNAMES )) {
                                throw new LogicException( $ERR3TXT . $recurDisp );
                            }
                            $cntDays += 1;
                        }
                    } // end foreach
                    $dayN = $pos = false;
                    continue;
                } // end if
                elseif(( Vcalendar::DAY === $BYDAYx )) {
                    if( ! in_array( $BYDAYv, RecurFactory::$DAYNAMES )) {
                        throw new LogicException( $ERR3TXT . var_export( $recur, true ));
                    }
                    $cntDays += 1;
                }
            } // end foreach
            if( empty( $cntDays )) {
                throw new LogicException( $ERR2TXT . var_export( $recur, true ));
            }
        } // end if BYDAY
        static $ERR4TXT =
            '#3 The BYDAY rule part MUST NOT ' .
            'be specified with a numeric value ' .
            'when the FREQ rule part is not set to MONTHLY or YEARLY. ';
        static $FREQ1 = [ Vcalendar::YEARLY, Vcalendar::MONTHLY ];
        if( isset( $recur[Vcalendar::BYDAY] ) &&
            ! in_array( $recur[Vcalendar::FREQ], $FREQ1 ) &&
            self::hasRecurByDaysWithRelativeWeekdays( $recur[Vcalendar::BYDAY] )) {
            throw new LogicException( $ERR4TXT . $recurDisp );
        } // end if
        static $ERR5TXT =
            '#4 The BYDAY rule part MUST NOT ' .
            'be specified with a numeric value ' .
            'with the FREQ rule part set to YEARLY ' .
            'when the BYWEEKNO rule part is specified. ';
        if( isset( $recur[Vcalendar::BYDAY] ) &&
            ( $recur[Vcalendar::FREQ] == Vcalendar::YEARLY ) &&
            isset( $recur[Vcalendar::BYWEEKNO] ) &&
            self::hasRecurByDaysWithRelativeWeekdays( $recur[Vcalendar::BYDAY] )) {
            throw new LogicException( $ERR5TXT . $recurDisp );
        } // end if
        static $ERR6TXT =
            '#5 The BYMONTHDAY rule part MUST NOT be specified ' .
            'when the FREQ rule part is set to WEEKLY. ';
        if(( $recur[Vcalendar::FREQ] == Vcalendar::WEEKLY ) &&
            isset( $recur[Vcalendar::BYMONTHDAY] )) {
            throw new LogicException( $ERR6TXT . $recurDisp );
        } // end if
        static $ERR7TXT =
            '#6 The BYYEARDAY rule part MUST NOT be specified ' .
            'when the FREQ rule part is set to DAILY, WEEKLY, or MONTHLY. ';
        static $FREQ4 = [ Vcalendar::DAILY, Vcalendar::WEEKLY, Vcalendar::MONTHLY ];
        if( isset( $recur[Vcalendar::BYYEARDAY] ) &&
            in_array( $recur[Vcalendar::FREQ], $FREQ4 )) {
            throw new LogicException( $ERR7TXT . $recurDisp );
        } // end if
        static $ERR8TXT =
            '#7 The BYWEEKNO rule part MUST NOT be used ' .
            'when the FREQ rule part is set to anything other than YEARLY.';
        if( isset( $recur[Vcalendar::BYWEEKNO] ) &&
            ( $recur[Vcalendar::FREQ] != Vcalendar::YEARLY )) {
            throw new LogicException( $ERR8TXT . $recurDisp );
        } // end if
    }

    /*
     *  Return Bool true if it is an simpler DAILY recur
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYDAYs opt, only fixed weekdays ex. 'TH', not '-1TH'
     * Recur BYMONTH opt
     * Recur BYMONTHDAY opt
     *
     * "The BYDAY rule part MUST NOT be specified with a numeric value when the FREQ rule part
     * is  not  set to MONTHLY or YEARLY."
     *
     * @param array $recur
     * @return bool
     * @since  2.27.22 - 2020-08-18
     */
    public static function isRecurDaily1( array $recur )
    {
        static $ACCEPT = [ Vcalendar::BYMONTHDAY, Vcalendar::WKST ];
        if( Vcalendar::DAILY != $recur[Vcalendar::FREQ ] ) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) {
            if( in_array( $byX, $ACCEPT )) {
                continue;
            }
            if( isset( $recur[$byX])) {
                return false;
            }
        } // end foreach
        if( ! isset( $recur[Vcalendar::BYDAY] )) {
            return true;
        }
        if( empty( self::getRecurByDaysWithNoRelativeWeekdays(
            $recur[Vcalendar::BYDAY] )
        )) {
            return false;
        }
        return true;
    }

    /*
     *  Return Bool true if it is an simple DAILY recur
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYDAYs opt, only fixed weekdays ex. 'TH', not '-1TH'
     * Recur BYMONTH opt
     * Recur BYMONTHDAY opt
     * BYSETPOS, only if BYMONTH or BYMONTHDAY exists
     *
     * "The BYDAY rule part MUST NOT be specified with a numeric value when the FREQ rule part
     * is  not  set to MONTHLY or YEARLY."
     *
     * @param array $recur
     * @return bool
     * @since  2.27.24 - 2020-08-27
     */
    public static function isRecurDaily2( array $recur )
    {
        if( Vcalendar::DAILY != $recur[Vcalendar::FREQ ] ) {
            return false;
        }
        if( isset( $recur[Vcalendar::BYSETPOS] ) &&
            ( isset( $recur[Vcalendar::BYMONTH] ) ||
                isset( $recur[Vcalendar::BYMONTHDAY] ))) {
            unset( $recur[Vcalendar::BYSETPOS] );
            return self::isRecurDaily1( $recur );
        }
        return false;
    }

    /*
     *  Return Bool true if it is an simple WEEKLY recur without BYDAYs
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYMONTH opt.
     *
     * @param array $recur
     * @return bool
     * @since  2.27.22 - 2020-08-18
     */
    public static function isRecurWeekly1( array $recur )
    {
        if( Vcalendar::WEEKLY != $recur[Vcalendar::FREQ ] ) {
            return false;
        }
        if( isset( $recur[Vcalendar::BYDAY] )) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) {
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /*
     *  Return Bool true if it is an simple WEEKLY recur with BYDAYs
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYDAYs required, only fixed weekdays ex. 'TH', not '-1TH'
     * Recur BYMONTH opt.
     *
     * "The BYDAY rule part MUST NOT be specified with a numeric value when the FREQ rule part
     * is  not  set to MONTHLY or YEARLY."
     *
     * @param array $recur
     * @return bool
     * @since  2.27.22 - 2020-08-18
     */
    public static function isRecurWeekly2( array $recur )
    {
        if( Vcalendar::WEEKLY != $recur[Vcalendar::FREQ ] ) {
            return false;
        }
        if( ! isset( $recur[Vcalendar::BYDAY] )) {
            return false;
        }
        if( empty( self::getRecurByDaysWithNoRelativeWeekdays(
            $recur[Vcalendar::BYDAY]
        ))) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) {
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /*
     *  Return Bool true if it is an simple MONTHLY recur with opt BYDAY/BYMONTHDAY
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYDAYs opt, only fixed Weekdays ex. 'TH', not '-1TH'
     * Recur BYMONTH opt
     * Recur BYMONTHDAY opt
     * Recur BYSETPOS if BYMONTHDAY exists
     *
     * @param array $recur
     * @return bool
     * @since  2.27.22 - 2020-08-18
     */
    public static function isRecurMonthly1( array $recur )
    {
        static $ACCEPT = [ Vcalendar::BYMONTHDAY, Vcalendar::BYSETPOS, Vcalendar::WKST ];
        if( Vcalendar::MONTHLY != $recur[Vcalendar::FREQ ] ) {
            return false;
        }
        if( isset( $recur[Vcalendar::BYDAY] ) &&
            self::hasRecurByDaysWithRelativeWeekdays( $recur[Vcalendar::BYDAY] )) {
            return false;
        }
        if( isset( $recur[Vcalendar::BYSETPOS] ) &&
            ! isset( $recur[Vcalendar::BYMONTHDAY] )) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) {
            if( in_array( $byX, $ACCEPT )) {
                continue;
            }
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /*
     *  Return Bool true if it is an simple MONTHLY recur with only BYDAYs
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYDAYs required
     * Recur BYMONTH opt
     * Recur BYSETPOS opt
     *
     * @param array $recur
     * @param array $recur
     * @return bool
     * @since  2.27.22 - 2020-08-18
     */
    public static function isRecurMonthly2( array $recur )
    {
        static $ACCEPT = [ Vcalendar::BYSETPOS, Vcalendar::WKST ];
        if( Vcalendar::MONTHLY != $recur[Vcalendar::FREQ ] ) {
            return false;
        }
        if( ! isset( $recur[Vcalendar::BYDAY] )) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) {
            if( in_array( $byX, $ACCEPT )) {
                continue;
            }
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /*
     *  Return Bool true if it is an simple YEARLY recur with BYMONTH/BYMONTHDAY but without BYDAYs
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYMONTH opt
     * Recur BYMONTHDAY opt
     *
     * @param array $recur
     * @return bool
     * @since  2.27.22 - 2020-08-18
     */
    public static function isRecurYearly1( array $recur )
    {
        static $ACCEPT = [ Vcalendar::BYMONTHDAY, Vcalendar::WKST ];
        if( Vcalendar::YEARLY != $recur[Vcalendar::FREQ ] ) {
            return false;
        }
        if( isset( $recur[Vcalendar::BYDAY] )) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) {
            if( in_array( $byX, $ACCEPT )) {
                continue;
            }
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /*
     *  Return Bool true if it is an YEARLY recur with BYMONTH/BYDAY
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYMONTH required
     * Recur BYDAY required
     * Recur BYSETPOS opt
     *
     * @param array $recur
     * @return bool
     * @since  2.29.24 - 2020-08-29
     */
    public static function isRecurYearly2( array $recur )
    {
        static $ACCEPT = [ Vcalendar::BYSETPOS, Vcalendar::WKST ];
        if( Vcalendar::YEARLY != $recur[Vcalendar::FREQ ] ) {
            return false;
        }
        if( ! isset( $recur[Vcalendar::BYDAY] ) ||
            ! isset( $recur[Vcalendar::BYMONTH] )) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) {
            if( in_array( $byX, $ACCEPT )) {
                continue;
            }
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Return initiated base values for recur_x_Simple
     *
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     * @param array $recur
     * @param mixed $wDateIn
     * @param mixed $fcnStartIn
     * @param mixed $fcnEndIn
     * @return array
     * @throws Exception
     * @since  2.29.2 - 2019-03-03
     */
    private static function getRecurSimpleBase(
        array $recur,
        $wDateIn,
        $fcnStartIn,
        $fcnEndIn
    ) {
        static $MOD  = ' years';
        $wDate       = self::dateToDateTime( $wDateIn );
        $wDateYmd    = $wDate->format( self::$YMD );
        $fcnStart    = self::dateToDateTime( $fcnStartIn );
        $fcnStartYmd = $fcnStart->format( self::$YMD );
        if( empty( $fcnEndIn )) {
            $base = ( $wDateYmd > $fcnStartYmd ) ? clone $wDate : clone $fcnStart;
            $base->modify( RecurFactory::EXTENDYEAR . $MOD ); // max??
        }
        else {
            $base = self::dateToDateTime( $fcnEndIn );
        }
        $endYmd = $base->format( self::$YMD );
        if( isset( $recur[Vcalendar::UNTIL] )) {
            $untilYmd = $recur[Vcalendar::UNTIL]->format( self::$YMD );
            if( $untilYmd < $endYmd ) {
                $endYmd = $untilYmd;
            }
        }
        return [
            $wDate,
            $wDateYmd,
            $fcnStartYmd,
            $endYmd
        ];
    }

    /**
     * Return array dates based on a simple DAILY recur pattern
     *
     * Recur INTERVAL required (or set to 1)
     * Recur BYDAYs opt, only fixed weekdays ex. 'TH', not '-1TH'
     * Recur BYMONTH opt
     * Recur BYMONTHDAY opt
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     * "The BYDAY rule part MUST NOT be specified with a numeric value when the FREQ rule part
     * is  not  set to MONTHLY or YEARLY."
     *
     * @param array $recur      pattern for recurrency (only value part, params ignored)
     * @param mixed $wDateIn    component start date, string / array / (datetime) obj
     * @param mixed $fcnStartIn start date, string / array / (datetime) obj
     * @param mixed $fcnEndIn   end date, string / array / (datetime) obj
     * @return array            array([Ymd] => bool)
     * @throws Exception
     * @since  2.27.16 - 2019-03-03
     */
    public static function recurDaily1(
        array $recur,
        $wDateIn,
        $fcnStartIn,
        $fcnEndIn = false
    ) {
        list( $wDate, $wDateYmd, $fcnStartYmd, $endYmd ) =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $plusXdays = sprintf( self::$FMTX, $recur[Vcalendar::INTERVAL] );
        $count     = self::getCount( $recur );
        $result = $byDayList = $byMonthList = $byMonthDayList = $monthDays = $bspList = [];
        $hasByMonth     = $hasByMonthDays = false;
        $byMonthList    = self::getRecurByMonth( $recur, $hasByMonth );
        $byMonthDayList = self::getRecurByMonthDay( $recur, $hasByMonthDays );
        $byDayList = ( isset( $recur[Vcalendar::BYDAY] )) // number for day in week
            ? self::getRecurByDaysWithNoRelativeWeekdays( $recur[Vcalendar::BYDAY] )
            : [];
        $wDate = $wDate->modify( $plusXdays );
        $x     = 1;
        $bck1Month = $bck2Month = null;
        while( $x < $count ) {
            $Ymd   = $wDate->format( self::$YMD );
            if(  $endYmd < $Ymd ) {
                break; // leave while
            }
            $currMonth = (int) $wDate->format( 'm' );
            if( $hasByMonth ) {
                if( $bck1Month != $currMonth ) {
                    // go forward to next 'BYMONTH'
                    while( ! in_array(
                        (int) $wDate->format( self::$LCM ),
                        $byMonthList
                    )) {
                        $wDate     = $wDate->modify( $plusXdays );
                        $currMonth = (int) $wDate->format( self::$LCM );
                    } // end while
                } // end if ( $bck1Month != $currMonth )
                $bck1Month = $currMonth;
                $bck2Month = null;
                $Ymd   = $wDate->format( self::$YMD );
            } // end if $hasByMonth
            if( $Ymd <= $fcnStartYmd ) {
                continue;
            }
            if( $hasByMonthDays && ( $bck2Month != $currMonth )) {
                $bck2Month = $currMonth;
                $monthDays = self::getMonthDaysFromByMonthDayList( // day numbers in month
                    (int) $wDate->format( self::$LCT ),
                    $byMonthDayList
                );
            } // end if
            if( self::inList( $wDate->format( self::$LCD ), $monthDays ) &&
                self::inList( $wDate->format( self::$LCW ), $byDayList )) { // number for day in week
                $result[$Ymd] = true;
                $x += 1;
            } // end if
            $wDate     = $wDate->modify( $plusXdays );
        } // end while
        return $result;
    }

    /**
     * Return array dates based on a DAILY/BYSETPOS recur pattern
     *
     * Recur INTERVAL required (or set to 1)
     * Recur BYDAYs opt, only fixed weekdays ex. 'TH', not '-1TH'
     * Recur BYMONTH opt
     * Recur BYMONTHDAY opt
     * BYSETPOS, only if BYMONTH or BYMONTHDAY exists
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     * "The BYDAY rule part MUST NOT be specified with a numeric value when the FREQ rule part
     * is  not  set to MONTHLY or YEARLY."
     *
     * @param array $recur      pattern for recurrency (only value part, params ignored)
     * @param mixed $wDateIn    component start date, string / array / (datetime) obj
     * @param mixed $fcnStartIn start date, string / array / (datetime) obj
     * @param mixed $fcnEndIn   end date, string / array / (datetime) obj
     * @return array            array([Ymd] => bool)
     * @throws Exception
     * @since  2.27.16 - 2019-03-03
     */
    public static function recurDaily2(
        array $recur,
        $wDateIn,
        $fcnStartIn,
        $fcnEndIn = false
    ) {
        list( $wDate, $wDateYmd, $fcnStartYmd, $endYmd ) =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $count       = self::getCount( $recur );
        unset( $recur[Vcalendar::COUNT] );
        self::hasSetByPos( $recur );
        $bySetPos    = $recur[Vcalendar::BYSETPOS];
        unset( $recur[Vcalendar::BYSETPOS] );
        $year        = (int) $wDate->format( self::$UCY );
        $month       = (int) $wDate->format( self::$LCM );
        $wDate->setDate( $year, $month, 1 );
        $wDateIn2    = clone $wDate;
        $fcnStartIn2 = $wDateIn2->format( self::$YMD );
        $yearEnd     = (int) substr( $endYmd, 0, 4 );
        $monthEnd    = (int) substr( $endYmd, 4, 2 );
        $dayEnd      = (int) substr( $endYmd, 6, 2 );
        $wDate->setDate( $yearEnd, $monthEnd, $dayEnd );
        $fcnEndIn2   = $wDate->setDate( $yearEnd, $monthEnd, (int) $wDate->format( self::$LCT ));
        $result1     = self::recurDaily1( $recur, $wDateIn2, $fcnStartIn2, $fcnEndIn2 );
        $recurLimits = [ $count, $bySetPos, $wDateYmd, $endYmd ];
        $result = $bspList = [];
        $currMonth = $month;
        $x         = 1;
        foreach( array_keys( $result1 ) as $Ymd ) {
            $month = (int) substr( $Ymd, 4, 2 );
            if( $currMonth != $month ) {
                self::bySetPosResultAppend($result, $x, $bspList, $recurLimits );
                if(( $x >= $count ) || ( $endYmd < $Ymd )) {
                    break;  // leave foreach !
                }
                $bspList   = [];
                $currMonth = $month;
            }
            $bspList[$Ymd] = true;
        } // end foreach
        return $result;
    }

    /**
     * Return array dates based on a simple WEEKLY recur pattern without BYDAYSs
     *
     * Recur INTERVAL required (or set to 1)
     * Recur BYMONTH opt.
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     * @param array $recur      pattern for recurrency (only value part, params ignored)
     * @param mixed $wDateIn    component start date, string / array / (datetime) obj
     * @param mixed $fcnStartIn start date, string / array / (datetime) obj
     * @param mixed $fcnEndIn   end date, string / array / (datetime) obj
     * @return array            array([Ymd] => bool)
     * @throws Exception
     * @since  2.27.16 - 2019-03-03
     */
    public static function recurWeekly1(
        array $recur,
        $wDateIn,
        $fcnStartIn,
        $fcnEndIn = false
    ) {
        list( $wDate, $wDateYmd, $fcnStartYmd, $endYmd ) =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $result      = [];
        $x           = 1;
        $count       = self::getCount( $recur );
        $hasByMonth  = false;
        $byMonthList = self::getRecurByMonth( $recur, $hasByMonth );
        $modifyX     = sprintf( self::$FMTX, ( $recur[Vcalendar::INTERVAL] * 7 ));
        while( $x < $count ) {
            $wDate   = $wDate->modify( $modifyX );
            $Ymd     = $wDate->format( self::$YMD );
            if( $endYmd < $Ymd ) {
                break;
            }
            if( $Ymd <= $fcnStartYmd ) {
                continue;
            }
            if( self::inList( $wDate->format( self::$LCM ), $byMonthList )) {
                $result[$Ymd] = true;
                $x           += 1;
            }
        } // end while
        return $result;
    }

    /**
     * Return array dates based on a simple WEEKLY recur pattern with BYDAYs
     *
     * Recur INTERVAL required (or set to 1)
     * Recur BYDAYS required, no positioned ones only ex 'WE', not '-1WE'
     * Recur BYMONTH opt.
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     * @param array $recur      pattern for recurrency (only value part, params ignored)
     * @param mixed $wDateIn    component start date, string / array / (datetime) obj
     * @param mixed $fcnStartIn start date, string / array / (datetime) obj
     * @param mixed $fcnEndIn   end date, string / array / (datetime) obj
     * @return array            array([Ymd] => bool)
     * @throws Exception
     * @since  2.27.28 - 2029-09-10
     */
    public static function recurWeekly2(
        array $recur,
        $wDateIn,
        $fcnStartIn,
        $fcnEndIn = false
    ) {
        static $MINUS1DAY = '-1 day';
        list( $wDate, $wDateYmd, $fcnStartYmd, $endYmd ) =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $result       = [];
        $x            = 1;
        $count        = self::getCount( $recur );
        $byDayList    = self::getRecurByDaysWithNoRelativeWeekdays(
            $recur[Vcalendar::BYDAY] // number(s) for day in week
        );
        $hasByMonth   = false;
        $byMonthList  = self::getRecurByMonth( $recur, $hasByMonth );
        $modify1      = sprintf( self::$FMTX, 1 );
        $targetWeekNo = (int) $wDate->format( self::$UCW );
        // go back to first day of week or first day in month
        while(( 1 != $wDate->format( self::$LCW )) &&
            ( 1 != $wDate->format( self::$LCJ ))) {
            $wDate = $wDate->modify( $MINUS1DAY );
        } // end while
        while( $x < $count ) {
            $currWeekNo = (int) $wDate->format( self::$UCW );
            $Ymd        = $wDate->format( self::$YMD );
            switch( true ) {
                case( $Ymd <= $fcnStartYmd ) :
                    $wDate = $wDate->modify( $modify1 );
                    break;
                case( $endYmd < $Ymd ) :
                    break 2; // leave while !
                case( $currWeekNo == $targetWeekNo ) :
                    if( self::inList( $wDate->format( self::$LCM ), $byMonthList )) {
                        if( self::inList( $wDate->format( self::$LCW ), $byDayList )) {
                            $result[$Ymd] = true;
                            $x           += 1;
                        }
                    }
                    $wDate = $wDate->modify( $modify1 );
                    break;
                default :
                    // now is the first day of next week
                    if( 1 < $recur[Vcalendar::INTERVAL] ) {
                        // advance interval weeks
                        $dayNo   = ( 7 * ( $recur[Vcalendar::INTERVAL] - 1 ));
                        $modifyX = sprintf( self::$FMTX,$dayNo );
                        $wDate   = $wDate->modify( $modifyX );
                    }
                    $targetWeekNo = (int) $wDate->format( self::$UCW );
                    break;
            } // end switch
        } // end while
        return $result;
    }

    /**
     * Return array dates based on a simple MONTHLY recur pattern
     *
     * Recur INTERVAL required (or set to 1)
     * Recur BYMONTH opt
     * Recur BYMONTHDAY opt
     * Recur BYDAYSs opt but no positioned ones only ex 'WE', not '-1WE'
     * Recur BYSETPOS if BYMONTHDAY exists
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     * @param array $recur      pattern for recurrency (only value part, params ignored)
     * @param mixed $wDateIn    component start date, string / array / (datetime) obj
     * @param mixed $fcnStartIn start date, string / array / (datetime) obj
     * @param mixed $fcnEndIn   end date, string / array / (datetime) obj
     * @return array            array([Ymd] => bool)
     * @throws Exception
     * @since  2.29.24 - 2020-08-29
     */
    public static function recurMonthly1(
        array $recur,
        $wDateIn,
        $fcnStartIn,
        $fcnEndIn = false
    ) {
        list( $wDate, $wDateYmd, $fcnStartYmd, $endYmd ) =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $hasBSP    = self::hasSetByPos( $recur );
        $count     = self::getCount( $recur );
        $result = $byMonthList = $byMonthDayList = $monthDays = $byDayList = $bspList = [];
        $hasByMonthDays = $hasByMonth = $hasByDay = false;
        $byMonthList    = self::getRecurByMonth( $recur, $hasByMonth );
        $byMonthDayList = self::getRecurByMonthDay( $recur, $hasByMonthDays );
        if( isset( $recur[Vcalendar::BYDAY] )) {
            $hasByDay = true;
            $byDayList = self::getRecurByDaysWithNoRelativeWeekdays(
                $recur[Vcalendar::BYDAY] // number(s) for day in week
            );
        }
        $year      = (int) $wDate->format( self::$UCY );
        $month     = (int) $wDate->format( self::$LCM );
        $currMonth = $daysInMonth = null;
        if( $hasByMonthDays || $hasByDay ) {
            if( $hasByMonthDays ) {
                $monthDays = self::getMonthDaysFromByMonthDayList(
                    (int)$wDate->format( self::$LCT ), // day numbers in month
                    $byMonthDayList
                );
                if( $hasBSP ) {
                    $recurLimits = [ $count, $recur[Vcalendar::BYSETPOS], $wDateYmd, $endYmd ];
                }
            }
            $day       = 1;
            $currMonth = $month;
        } // end if
        else {
            $day = (int) $wDate->format( self::$LCJ );
        }
        $plusXmonth = $recur[Vcalendar::INTERVAL] . Util::$SP0 . RecurFactory::$LCMONTH;
        $x         = 1;
        while( $x < $count ) {
            if( $month != $currMonth ) {
                if( $hasByMonthDays && $hasBSP ) { // has BySetPos !!
                    self::bySetPosResultAppend($result, $x, $bspList, $recurLimits );
                    $Ymd = sprintf( RecurFactory::$YMDs, $year, $month, $day );
                    if( $endYmd < $Ymd ) {  // leave while !
                        break;
                    }
                    $bspList = [];
                } // end if
                $wDate->setDate( $year, $month, 1 )->modify( $plusXmonth );
                $year  = (int) $wDate->format( self::$UCY );
                $month = (int) $wDate->format( self::$LCM );
                if( ! self::inList( $month, $byMonthList )) {
                    $currMonth = null;
                    continue;
                }
                $currMonth     = $month;
                if( $hasByMonthDays ) {
                    $day       = 1;
                    $monthDays = self::getMonthDaysFromByMonthDayList(
                        (int) $wDate->setDate( $year, $month, $day )->format( self::$LCT ),
                        $byMonthDayList
                    );
                }
                if( $hasByDay ) {
                    $day    = 1;
                }
            } // end if( $month != $currMonth )
            else {
                $day += 1;
            }
            if( ! checkdate((int) $month, (int) $day, (int) $year )) {
                $currMonth = null;
                continue;
            }
            $Ymd   = sprintf( RecurFactory::$YMDs, $year, $month, $day );
            $dayNo = (int) $wDate->setDate( $year, $month, $day )->format( self::$LCW );
            switch( true ) {
                case ( $hasByMonthDays && $hasBSP ) :  // has BySetPos !!
                    if( self::inList( $day, $monthDays ) &&
                        self::inList( $dayNo, $byDayList )) {
                        $bspList[$Ymd] = true;
                    }
                    break;
                case ( $endYmd < $Ymd ) :
                    break 2; // leave while !!
                case ( $Ymd <= $fcnStartYmd );
                    break;
                case ( self::inList( $day, $monthDays )) :
                    if( self::inList( $dayNo, $byDayList )) { // empty or hit
                        $result[$Ymd] = true;
                        $x            += 1;
                        if( $x >= $count ) {
                            break 2;  // leave while !!
                        }
                    } // end if
                    if( ! $hasByMonthDays && ! $hasByDay ) {
                        $currMonth = null;
                    }
                    break;
            } // end switch
        } // end while
        return $result;
    }

    /**
     * Return array dates based on a MONTHLY/YEARLY BYDAYSs recur pattern without BYMONTHDAY
     *
     * Recur INTERVAL required (or set to 1)
     * Recur BYDAY required
     * Recur BYMONTH MONTHLY opt, YEARLY required
     * Recur BYSETPOS opt
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     *  "Each BYDAY value can also be preceded by a positive (+n) or
     *    negative (-n) integer, indicates the nth
     *     occurrence of a specific day within the MONTHLY or YEARLY "RRULE".
     *
     * "The numeric value in a BYDAY rule part with the FREQ rule part set to YEARLY corresponds
     *    to an offset within the month when the BYMONTH rule part is present"
     *
     * @param array $recur      pattern for recurrency (only value part, params ignored)
     * @param mixed $wDateIn    component start date, string / array / (datetime) obj
     * @param mixed $fcnStartIn start date, string / array / (datetime) obj
     * @param mixed $fcnEndIn   end date, string / array / (datetime) obj
     * @return array            array([Ymd] => bool)
     * @throws Exception
     * @since  2.29.24 - 2020-08-29
     */
    public static function recurMonthlyYearly3(
        array $recur,
        $wDateIn,
        $fcnStartIn,
        $fcnEndIn = false
    ) {
        list( $wDate, $wDateYmd, $fcnStartYmd, $endYmd ) =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $isYearly    = isset( $recur[Vcalendar::YEARLY] );
        $hasBSP      = self::hasSetByPos( $recur );
        $count       = self::getCount( $recur );
        $result      = $byMonthList = $byDayList = $bspList = [];
        $year        = (int) $wDate->format( self::$UCY );
        $month       = (int) $wDate->format( self::$LCM );
        $hasByMonth  = false;
        $byMonthList = self::getRecurByMonth( $recur, $hasByMonth );
        if( $hasByMonth ) {
            while( ! self::inList( $month, $byMonthList )) {
                $wDate->modify( 1 . Util::$SP0 . RecurFactory::$LCMONTH );
                $year  = (int) $wDate->format( self::$UCY );
                $month = (int) $wDate->format( self::$LCM );
            } // end while
        } // end if
        $day      = 1;
        $modifier = $isYearly
            ? $recur[Vcalendar::INTERVAL] . Util::$SP0 . RecurFactory::$LCYEAR
            : $recur[Vcalendar::INTERVAL] . Util::$SP0 . RecurFactory::$LCMONTH;
        $weekDaysInMonth = self::getRecurByDaysInMonth(
            $recur[Vcalendar::BYDAY],
            $year,
            $month
        );
        if( $hasBSP ) {
            $recurLimits = [ $count, $recur[Vcalendar::BYSETPOS], $wDateYmd, $endYmd ];
            $wDate->setDate( $year, $month, 1 ); //
            $year  = (int) $wDate->format( self::$UCY );
            $month = (int) $wDate->format( self::$LCM );
            $day   = (int) $wDate->format( self::$LCJ );
        }
        $currMonth = $month;
        $currYear  = $year;
        $x         = 1;
        while( $x < $count ) {
            if( ! checkdate((int) $month, (int) $day, (int) $year )) {
                $currMonth = null;
                if( $isYearly && ( 12 == $month )) {
                    $currYear = null;
                }
            }
            if(( $month != $currMonth ) || ( $isYearly && ( $year != $currYear ))) {
                $day       = 1;
                if( $isYearly && ( $year != $currYear )) {
                    $wDate->setDate( $year, 1, $day )
                        ->modify( $modifier );
                    $year = (int) $wDate->format( self::$UCY );
                    $month = 1;
                }
                else {
                    $wDate->setDate( $year, $month, $day )
                        ->modify( $modifier );
                    $year  = (int) $wDate->format( self::$UCY );
                    $month = (int) $wDate->format( self::$LCM );
                }
                if( $hasBSP ) {
                    self::bySetPosResultAppend( $result, $x, $bspList, $recurLimits );
                    $Ymd = sprintf( RecurFactory::$YMDs, $year, $month, $day );
                    if( $endYmd < $Ymd ) {  // leave while !
                        break;
                    }
                    $bspList = [];
                } // end if
                if( ! self::inList( $month, $byMonthList )) {
                    $currMonth = null;
                    if( $isYearly && ( 12 == $month )) {
                        $currYear = null;
                    }
                    continue;
                }
                $currYear  = $year;
                $currMonth = $month;
                $weekDaysInMonth = self::getRecurByDaysInMonth(
                    $recur[Vcalendar::BYDAY],
                    $year,
                    $month
                );
            } // end if
            $Ymd = sprintf( RecurFactory::$YMDs, $year, $month, $day );
            switch( true ) {
                case $hasBSP :  // has BySetPos !!
                    if( self::inList( $day, $weekDaysInMonth )) {
                        $bspList[$Ymd] = true;
                    }
                    break;
                case ( $endYmd < $Ymd ) :
                    break 2; // leave while !
                case ( $Ymd <= $fcnStartYmd ) :
                    break;
                case self::inList( $day, $weekDaysInMonth ) :
                    $result[$Ymd] = true;
                    $x           += 1;
                    if( $x >= $count ) {
                        break 2;  // leave while !
                    }
                    break;
            } // end switch
            // count day up
            $day += 1;
        } // end while
        return $result;
    }

    /**
     * Append result from bspList in conjunction with x/count, bySetPos, start/endYmd
     *
     * @param array $result
     * @param int   $x
     * @param array $bspList
     * @param array $recurLimits  [ count, bySetPos, wDateYmd, endYmd ]
     * @return void
     */
    private static function bySetPosResultAppend(
        array & $result,
        & $x,
        array $bspList,
        array  $recurLimits
    ) {
        if( empty( $bspList )) {
            return;
        }
        $bspKeys = array_keys( $bspList );
        // order bspList items up
        $ix = 1;
        foreach( $bspKeys as $Ymd ) {
            $bspList[$Ymd] = [ $ix++ ];
        }
        // order bspList items down
        $ix = -1;
        foreach( array_reverse( $bspKeys ) as $Ymd ) {
            $bspList[$Ymd][1] = $ix--;
        }
        // match up/down items in bspList with each bySetPos item
        $temp = [];
        foreach( $bspList as $Ymd => $ydmOrder ) {
            if( in_array( $ydmOrder[0], $recurLimits[1] )) {
                $temp[] = $Ymd;
            }
            elseif( in_array( $ydmOrder[1], $recurLimits[1] )) {
                $temp[] = $Ymd;
            }
        }
        // if match, update result if Ymd within startYmd - endYmd
        foreach( $temp as $Ymd ) {
            if(( $recurLimits[2] < $Ymd) && ( $Ymd <= $recurLimits[3] )) {
                $result[$Ymd] = true;
                $x           += 1;
                if( $x >= $recurLimits[0] ) { // count
                    break;
                }
            }
        } // end foreach
    }

    /**
     * Return array dates based on a simple YEARLY recur pattern without BYDAYSs
     *
     * Recur INTERVAL required
     * Recur BYMONTH opt,
     * Recur BYMONTHDAY opt
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     * @param array $recur      pattern for recurrency (only value part, params ignored)
     * @param mixed $wDateIn    component start date, string / array / (datetime) obj
     * @param mixed $fcnStartIn start date, string / array / (datetime) obj
     * @param mixed $fcnEndIn   end date, string / array / (datetime) obj
     * @return array            array([Ymd] => bool)
     * @throws Exception
     * @since  2.29.21 - 2020-01-31
     */
    public static function recurYearly1(
        array $recur,
        $wDateIn,
        $fcnStartIn,
        $fcnEndIn = false
    ) {
        list( $wDate, $wDateYmd, $fcnStartYmd, $endYmd ) =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $result    = $byMonthList = $byWeeknoList = $byMonthDayList = $monthDaysList = [];
        $x         = 1;
        $count     = self::getCount( $recur );
        $hasByMonthDays = $hasByMonth = false;
        $byMonthList    = self::getRecurByMonth( $recur, $hasByMonth );
        $byMonthDayList = self::getRecurByMonthDay( $recur, $hasByMonthDays );
        $year      = $currYear = (int) $wDate->format( self::$UCY );
        $month     = (int) $wDate->format( self::$LCM );
        $day       = (int) $wDate->format( self::$LCJ );
        $currMonth = $month;
        if( ! $hasByMonth && ! $hasByMonthDays ) {
            $currYear  = null;
            $currMonth = null;
        }
        $isLastMonth = false;
        while(( $x < $count ) && ( $endYmd >= $wDate->format( self::$YMD ))) {
            if( $year != $currYear ) {
                $year     += $recur[Vcalendar::INTERVAL];
                $currYear  = $year;
                if( $hasByMonth ) {
                    $month = 1;
                    $currMonth = null;
                }
                $wDate->setDate( $year, $month, $day );
            }  // end if currYear
            if( $hasByMonth && ( $month != $currMonth )) {
                $continue2 = false;
                switch( true ) {
                    case( ! self::inList( $month, $byMonthList )) :
                        $currMonth = $month = reset( $byMonthList );
                        break;
                    case( $isLastMonth ) :
                        $currMonth = $month = reset( $byMonthList );
                        break;
                    case( 1 == count( $byMonthList )) :
                        $currYear    = null;
                        $isLastMonth = true;
                        $continue2   = true;
                        break;
                    default :
                        $nextKey       = array_keys( $byMonthList, $month )[0] + 1;
                        if( isset( $byMonthList[$nextKey] )) {
                            $currMonth = $month = $byMonthList[$nextKey];
                            break;
                        }
                        $currYear    = null;
                        $isLastMonth = true;
                        $continue2   = true;
                        break;
                } // end switch
                if( $continue2 ) {
                    continue; // i.e. while
                }
                $isLastMonth = ( $month == end( $byMonthList ));
                $wDate->setDate( $year, $month, $day );
            } // end if currMonth
            $Ymd = $wDate->format( self::$YMD );
            if( $endYmd < $Ymd ) {
                break; // leave while !!
            }
            if( $hasByMonthDays ) {
                foreach( self::getMonthDaysFromByMonthDayList(
                    (int) $wDate->format( self::$LCT ),
                    $byMonthDayList
                ) as $monthDay ) {
                    $Ymd = sprintf( RecurFactory::$YMDs, $year, $month, $monthDay );
                    if( $Ymd <= $fcnStartYmd ) {
                        continue;
                    }
                    if( $endYmd < $Ymd ) {
                        break 2;  // leave while !!
                    }
                    $result[$Ymd] = true;
                    $x           += 1;
                    if( $x >= $count ) {
                        break 2;
                    }
                } // end foreach
            } // end if $hasByMonthDays
            elseif( $Ymd > $fcnStartYmd ) {
                $result[$Ymd] = true;
                $x           += 1;
            }
            if( $hasByMonth ) {
                $currMonth = null;
                if( $isLastMonth ) {
                    $currYear = null;
                }
            }
            else {
                $currYear = null;
            }
        } // end while
        return $result;
    }

    /**
     * Return count occurrences (if found) or PHP_INT_MAX
     *
     * @param array $recur
     * @return int
     */
    private static function getCount( array $recur )
    {
        return ( isset( $recur[Vcalendar::COUNT] ))
            ? (int) $recur[Vcalendar::COUNT]
            : PHP_INT_MAX;
    }

    /*
     * Return bool true if byXxxList is empty or needle found in byXxxList
     *
     * @param int   $needle
     * @param array $byXxxList
     * @return bool
     * @since  2.27.16 - 2019-03-04
     */
    private static function inList( $needle, array $byXxxList )
    {
        if( empty( $byXxxList )) {
            return true;
        }
        return in_array( $needle, $byXxxList );
    }

    /**
     * Return bool true if recur setByPos exists, assure array
     *
     * @param array $recur
     * @return bool
     */
    private static function hasSetByPos( array & $recur )
    {
        if( ! isset( $recur[Vcalendar::BYSETPOS] )) {
            return false;
        }
        if( ! is_array( $recur[Vcalendar::BYSETPOS] )) {
            $recur[Vcalendar::BYSETPOS] =
                explode( self::$COMMA, (string) $recur[Vcalendar::BYSETPOS] );
        }
        sort( $recur[Vcalendar::BYSETPOS] );
        return true;
    }

   /*
     * Return array, recur BYMONTH (sorted month numbers)
     *
     * @param array $recur
     * @param bool  $hasMonth
     * @return array
     * @since  2.29.11 - 2019-08-30
     */
    private static function getRecurByMonth( array $recur, & $hasByMonth = false )
    {
        $byMonthList = [];
        if( isset( $recur[Vcalendar::BYMONTH] )) {
            $byMonthList = is_array( $recur[Vcalendar::BYMONTH] )
                ? $recur[Vcalendar::BYMONTH]
                : [ $recur[Vcalendar::BYMONTH] ];
            sort( $byMonthList, SORT_NUMERIC );
            $hasByMonth = true;
        }
        return $byMonthList;
    }

    /*
     * Return array BYMONTHDAY i.e. sorted day numbers in month
     *
     * @param array $recur
     * @param bool  $hasMonthDays
     * @return array
     * @since  2.29.11 - 2019-08-30
     */
    private static function getRecurByMonthDay( array $recur, & $hasByMonthDays = false )
    {
        $byMonthDayList = [];
        if( isset( $recur[Vcalendar::BYMONTHDAY] )) {
            $byMonthDayList = is_array( $recur[Vcalendar::BYMONTHDAY] )
                ? $recur[Vcalendar::BYMONTHDAY]
                : [ $recur[Vcalendar::BYMONTHDAY] ];
            sort( $byMonthDayList, SORT_NUMERIC );
            $hasByMonthDays = true;
        }
        return $byMonthDayList;
    }

    /*
     * Return array list of monthdays from byMonthDayList
     *
     * Fix also negative days, days before month end, conv to month day no
     *
     * @param int   $daysInMonth
     * @param array $byMonthDayList
     * @return array
     * @since  2.27.16 - 2019-03-06
     */
    public static function getMonthDaysFromByMonthDayList(
        $daysInMonth,
        array $byMonthDayList
    ) {
        $list = [];
        foreach( $byMonthDayList as $byMonthDay ) {
            $list[] = ( 0 < $byMonthDay )
                ? $byMonthDay
                : ( $daysInMonth + 1 + $byMonthDay );
        }
        $list = array_values( array_unique( $list ));
        sort( $list, SORT_NUMERIC );
        return $list;
    }

    /*
     * Return recur BYDAYs but the relative part of weekday(s) skipped ( ex '-1TH' to 'TH')
     *
     * @param array $recurByDay
     * @return array
     * @since  2.27.16 - 2019-03-03
     */
    private static function getRecurByDaysWithNoRelativeWeekdays( array $recurByDay )
    {
        $dayArr = array_flip( RecurFactory::$DAYNAMES );
        $list   = [];
        foreach( $recurByDay as $BYDAYx => $BYDAYv ) {
            if( ctype_digit((string) $BYDAYx ) && is_array( $BYDAYv )) {
                foreach( $BYDAYv as $BYDAYx2 => $BYDAYv2 ) {
                    if( Vcalendar::DAY == $BYDAYx2 ) {
                        $list[] = $dayArr[$BYDAYv2];
                    }
                }
            } // end if
            elseif( Vcalendar::DAY == $BYDAYx ) {
                $list[] = $dayArr[$BYDAYv];
            }
        } // end foreach
        return $list;
    }

    /*
     * Return bool true if recur BYDAYs has relative weekday(s) ( ex '-1 TH' )
     *
     * @param array $recurByDay
     * @return array
     * @since  2.27.16 - 2019-03-03
     */
     private static function hasRecurByDaysWithRelativeWeekdays( array $recurByDay )
     {
         if( empty( $recurByDay )) {
             return false;
         }
         foreach( $recurByDay as $BYDAYx => $BYDAYv ) {
             if(((int) $BYDAYx === $BYDAYx ) && is_array( $BYDAYv )) {
                 // multi ByDay recur
                 foreach( $BYDAYv as $BYDAYx2 => $BYDAYv2 ) {
                     if( Vcalendar::DAY === $BYDAYx2 ) {
                         continue;
                     }
                     else {
                         return true;
                     }
                 } // end foreach
                 continue;
             } // end if
             // single ByDay recur
             elseif( Vcalendar::DAY === $BYDAYx ) {
                 continue;
             }
             else {
                 return true;
             }
         } // end foreach
         return false;
     }

    /*
     * Return recur BYDAYs for spec. year/month, also '-1MO'-type BYDAYs
     *
     * @param array $recurByDay
     * @param int $year
     * @param int $month
     * @return array
     * @since  2.27.16 - 2019-03-03
     */
    public static function getRecurByDaysInMonth( array $recurByDay, $year, $month )
    {
        static $wFmt1 = '%d-%02d-%02d';
        static $wFmt2 = '+1 day';
        $wDay  = 1;
        $wDate = new DateTime( sprintf( $wFmt1, $year, $month, $wDay ));
        // get days in month
        $daysInMonth = (int) $wDate->format( self::$LCT );
        $monthDays = [];
        $dayPos    = [];
        // get monthDay as weekDay and occurence pos from start
        while( $wDay  <= $daysInMonth ) {
            $weekDayNo = $wDate->format( self::$LCW );
            $dayName   = RecurFactory::$DAYNAMES[$weekDayNo];
            $monthDays[$wDay] = [ $dayName, 0, 0 ];
            $dayPos[$dayName] = isset( $dayPos[$dayName] ) ? ( $dayPos[$dayName] + 1 ) : 1;
            $monthDays[$wDay][1] = $dayPos[$dayName];
            $wDay     += 1;
            $wDate->modify( $wFmt2 );
        } // end while
        // get occurence pos from end
        $dayPos    = [];
        for( $wDay = $daysInMonth; $wDay > 0; $wDay-- ) {
            $dayName = $monthDays[$wDay][0];
            $dayPos[$dayName]    = isset( $dayPos[$dayName] ) ? ( $dayPos[$dayName] - 1 ) : -1;
            $monthDays[$wDay][2] = $dayPos[$dayName];
        } // end for
        $result = [];
        $dayN = $pos = false;
        foreach( $recurByDay as $BYDAYx => $BYDAYv ) {
            if(((int) $BYDAYx === $BYDAYx ) && is_array( $BYDAYv )) {
                // multi ByDay recur
                foreach( $BYDAYv as $BYDAYx2 => $BYDAYv2 ) {
                    if( Vcalendar::DAY === $BYDAYx2 ) {
                        $dayN = $BYDAYv2;
                    }
                    else {
                        $pos = (int) $BYDAYv2;
                    }
                } // end foreach
                if( ! empty( $dayN )) {
                    $result = array_merge(
                        $result,
                        self::getMonthDaysFromByDay( $monthDays, $pos, $dayN )
                    );
                }
                $dayN = $pos = false;
                continue;
            } // end if
            // single ByDay recur
            elseif( Vcalendar::DAY === $BYDAYx ) {
                $dayN = $BYDAYv;
            }
            else {
                $pos = (int) $BYDAYv;
            }
        } // end foreach
        // single ByDay recur
        if( ! empty( $dayN )) {
            $result = array_merge(
                $result,
                self::getMonthDaysFromByDay( $monthDays, $pos, $dayN )
            );
        }
        sort( $result, SORT_NUMERIC );
        return $result;
    }

    /**
     * Return (array) dayNo hits, found in monthDays
     *
     * @param array    $monthDays with element dayN, posFromStart, posFromEnd
     * @param bool|int $pos
     * @param string   $dayN      weekday name abbr
     * @return array    dayNo hits in month
     */
    private static function getMonthDaysFromByDay( array $monthDays, $pos, $dayN ) {
        $list = [];
        foreach( $monthDays as $dayNo => $dayData ) {
            if( $dayN != $dayData[0] ) {
                continue;
            }
            if( empty( $pos )) {
                $list[] = $dayNo;
                continue;
            }
            if(( $dayData[1] == $pos ) ||  // positive, posFromStart
                ( $dayData[2] == $pos )) { // negative, posFromEnd
                $list[] = $dayNo;
            }
        } // end foreach
        return $list;
    }

    /*
     *  Return DateTime
     *
     * @param DateTime|string $input
     * @return DateTime
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.27.16 - 2019-03-03
     */
    private static function dateToDateTime( $input )
    {
        if( $input instanceof DateTime ) {
            return clone $input;
        }
        list( $dateStr, $timezonePart ) =
            DateTimeFactory::splitIntoDateStrAndTimezone( $input );
        try {
            $output = DateTimeFactory::getDateTimeWithTimezoneFromString(
                $dateStr,
                $timezonePart,
                Vcalendar::UTC
            );
        }
        catch( Exception $e ) {
            throw $e;
        }
        return $output;
    }
}
