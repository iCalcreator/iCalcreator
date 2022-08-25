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

use DateTime;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\IcalInterface;
use LogicException;

use function array_flip;
use function array_keys;
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
 * @since  2.40.10 - 2021-12-03
 */
class RecurFactory2
{
    /**
     * @var string  DateTime format keys
     */
    private static string $YMD  = 'Ymd';

    /**
     * @var string  dito
     */
    private static string $LCD  = 'd'; // day NN

    /**
     * @var string  dito
     */
    private static string $LCJ  = 'j'; // day [N]N

    /**
     * @var string  dito
     */
    private static string $LCM  = 'm'; // month

    /**
     * @var string  dito
     */
    private static string $LCT  = 't'; // number of days in month

    /**
     * @var string  dito
     */
    private static string $UCY  = 'Y'; // year NNNN

    /**
     * @var string  dito
     */
    private static string $LCW  = 'w'; // day of week number

    /**
     * @var string  dito
     */
    private static string $UCW  = 'W'; // week number

    /**
     * @var string  DateTime nodify string
     */
    private static string $FMTX = '%d days';

    /**
     * @var string
     */
    private static string $COMMA = ',';

    /**
     * @var string[]   troublesome simple recurs keys, all but BYDAY and BYMONTH
     */
    private static array $RECURBYX  = [
        IcalInterface::BYSECOND,
        IcalInterface::BYMINUTE,
        IcalInterface::BYHOUR,
        IcalInterface::BYMONTHDAY,
        IcalInterface::BYYEARDAY,
        IcalInterface::BYWEEKNO,
        IcalInterface::BYSETPOS,
        IcalInterface::WKST
    ];

    /**
     * Asserts recur
     *
     * @param array $recur
     * @return void
     * @throws LogicException
     * @since  2.27.26 - 2020-09-10
     */
    public static function assertRecur( array $recur ) : void
    {
        $recurDisp = var_export( $recur, true );
        static $ERR1TXT = '#1 The FREQ rule part MUST be specified in the recurrence rule.';
        if( ! isset( $recur[IcalInterface::FREQ] )) {
            throw new LogicException( $ERR1TXT . $recurDisp );
        }
        static $ERR2TXT = '#2 NO BYDAY days : ';
        static $ERR3TXT = '#3 Unkown BYDAY day : ';
        $cntDays        = 0;
        if( isset( $recur[IcalInterface::BYDAY] )) {
            foreach( $recur[IcalInterface::BYDAY] as $BYDAYx => $BYDAYv ) {
                if(((int) $BYDAYx === $BYDAYx ) && is_array( $BYDAYv )) {
                    foreach( $BYDAYv as $BYDAYx2 => $BYDAYv2 ) {
                        if( IcalInterface::DAY === $BYDAYx2 ) {
                            if( ! in_array( $BYDAYv2, RecurFactory::$DAYNAMES, true )) {
                                throw new LogicException( $ERR3TXT . $recurDisp );
                            }
                            ++$cntDays;
                        }
                    } // end foreach
                } // end if
                elseif(( IcalInterface::DAY === $BYDAYx )) {
                    if( ! in_array( $BYDAYv, RecurFactory::$DAYNAMES, true )) {
                        throw new LogicException( $ERR3TXT . var_export( $recur, true ));
                    }
                    ++$cntDays;
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
        static $FREQ1 = [ IcalInterface::YEARLY, IcalInterface::MONTHLY ];
        if( isset( $recur[IcalInterface::BYDAY] ) &&
            ! in_array( $recur[IcalInterface::FREQ], $FREQ1, true ) &&
            self::hasRecurByDaysWithRelativeWeekdays( $recur[IcalInterface::BYDAY] )) {
            throw new LogicException( $ERR4TXT . $recurDisp );
        } // end if
        static $ERR5TXT =
            '#4 The BYDAY rule part MUST NOT ' .
            'be specified with a numeric value ' .
            'with the FREQ rule part set to YEARLY ' .
            'when the BYWEEKNO rule part is specified. ';
        if( isset( $recur[IcalInterface::BYDAY], $recur[IcalInterface::BYWEEKNO] ) &&
            ( $recur[IcalInterface::FREQ] === IcalInterface::YEARLY ) &&
            self::hasRecurByDaysWithRelativeWeekdays( $recur[IcalInterface::BYDAY] )) {
            throw new LogicException( $ERR5TXT . $recurDisp );
        } // end if
        static $ERR6TXT =
            '#5 The BYMONTHDAY rule part MUST NOT be specified ' .
            'when the FREQ rule part is set to WEEKLY. ';
        if(( $recur[IcalInterface::FREQ] === IcalInterface::WEEKLY ) &&
            isset( $recur[IcalInterface::BYMONTHDAY] )) {
            throw new LogicException( $ERR6TXT . $recurDisp );
        } // end if
        static $ERR7TXT =
            '#6 The BYYEARDAY rule part MUST NOT be specified ' .
            'when the FREQ rule part is set to DAILY, WEEKLY, or MONTHLY. ';
        static $FREQ4 = [ IcalInterface::DAILY, IcalInterface::WEEKLY, IcalInterface::MONTHLY ];
        if( isset( $recur[IcalInterface::BYYEARDAY] ) &&
            in_array( $recur[IcalInterface::FREQ], $FREQ4, true )) {
            throw new LogicException( $ERR7TXT . $recurDisp );
        } // end if
        static $ERR8TXT =
            '#7 The BYWEEKNO rule part MUST NOT be used ' .
            'when the FREQ rule part is set to anything other than YEARLY.';
        if( isset( $recur[IcalInterface::BYWEEKNO] ) &&
            ( $recur[IcalInterface::FREQ] !== IcalInterface::YEARLY )) {
            throw new LogicException( $ERR8TXT . $recurDisp );
        } // end if
    }

    /**
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
    public static function isRecurDaily1( array $recur ) : bool
    {
        static $ACCEPT = [ IcalInterface::BYMONTHDAY, IcalInterface::WKST ];
        if( IcalInterface::DAILY !== $recur[IcalInterface::FREQ ] ) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) { // all but BYDAY and BYMONTH
            if( in_array( $byX, $ACCEPT, true )) {
                continue;
            }
            if( isset( $recur[$byX])) {
                return false;
            }
        } // end foreach
        if( ! isset( $recur[IcalInterface::BYDAY] )) {
            return true;
        }
        if( empty( self::getRecurByDaysWithNoRelativeWeekdays( $recur[IcalInterface::BYDAY] ))) {
            return false;
        }
        return true;
    }

    /**
     *  Return Bool true if it is an DAILY recur
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYDAYs opt, only fixed weekdays ex. 'TH', not '-1TH'
     * BYSETPOS, only if one of BYMONTH or BYMONTHDAY exists
     * Recur BYMONTH opt
     * Recur BYMONTHDAY opt
     *
     * "The BYDAY rule part MUST NOT be specified with a numeric value when the FREQ rule part
     * is  not  set to MONTHLY or YEARLY."
     *
     * @param array $recur
     * @return bool
     * @since  2.27.24 - 2020-08-27
     */
    public static function isRecurDaily2( array $recur ) : bool
    {
        if( IcalInterface::DAILY !== $recur[IcalInterface::FREQ ] ) {
            return false;
        }
        if( isset( $recur[IcalInterface::BYSETPOS] ) &&
            ( isset( $recur[IcalInterface::BYMONTH] ) ||
                isset( $recur[IcalInterface::BYMONTHDAY] ))) {
            unset( $recur[IcalInterface::BYSETPOS] );
            return self::isRecurDaily1( $recur );
        }
        return false;
    }

    /**
     *  Return Bool true if it is an simple WEEKLY recur without BYDAYs
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYMONTH opt.
     *
     * @param array $recur
     * @return bool
     * @since  2.27.22 - 2020-08-18
     */
    public static function isRecurWeekly1( array $recur ) : bool
    {
        if( IcalInterface::WEEKLY !== $recur[IcalInterface::FREQ ] ) {
            return false;
        }
        if( isset( $recur[IcalInterface::BYDAY] )) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) { // all but BYDAY and BYMONTH
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /**
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
    public static function isRecurWeekly2( array $recur ) : bool
    {
        if( IcalInterface::WEEKLY !== $recur[IcalInterface::FREQ ] ) {
            return false;
        }
        if( ! isset( $recur[IcalInterface::BYDAY] )) {
            return false;
        }
        if( empty( self::getRecurByDaysWithNoRelativeWeekdays(
            $recur[IcalInterface::BYDAY]
        ))) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) { // all but BYDAY and BYMONTH
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /**
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
    public static function isRecurMonthly1( array $recur ) : bool
    {
        static $ACCEPT = [ IcalInterface::BYMONTHDAY, IcalInterface::BYSETPOS, IcalInterface::WKST ];
        if( IcalInterface::MONTHLY !== $recur[IcalInterface::FREQ ] ) {
            return false;
        }
        if( isset( $recur[IcalInterface::BYDAY] ) &&
            self::hasRecurByDaysWithRelativeWeekdays( $recur[IcalInterface::BYDAY] )) {
            return false;
        }
        if( isset( $recur[IcalInterface::BYSETPOS] ) &&
            ! isset( $recur[IcalInterface::BYMONTHDAY] )) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) { // all but BYDAY and BYMONTH
            if( in_array( $byX, $ACCEPT, true )) {
                continue;
            }
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /**
     *  Return Bool true if it is an simple MONTHLY recur with only BYDAYs
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYDAYs required
     * Recur BYMONTH opt
     * Recur BYSETPOS opt
     *
     * @param array $recur
     * @return bool
     * @since  2.27.22 - 2020-08-18
     */
    public static function isRecurMonthly2( array $recur ) : bool
    {
        static $ACCEPT = [ IcalInterface::BYSETPOS, IcalInterface::WKST ];
        if( IcalInterface::MONTHLY !== $recur[IcalInterface::FREQ ] ) {
            return false;
        }
        if( ! isset( $recur[IcalInterface::BYDAY] )) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) { // all but BYDAY and BYMONTH
            if( in_array( $byX, $ACCEPT, true )) {
                continue;
            }
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /**
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
    public static function isRecurYearly1( array $recur ) : bool
    {
        static $ACCEPT = [ IcalInterface::BYMONTHDAY, IcalInterface::WKST ];
        if( IcalInterface::YEARLY !== $recur[IcalInterface::FREQ ] ) {
            return false;
        }
        if( isset( $recur[IcalInterface::BYDAY] )) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) { // all but BYDAY and BYMONTH
            if( in_array( $byX, $ACCEPT, true )) {
                continue;
            }
            if( isset( $recur[$byX])) {
                return false;
            }
        }
        return true;
    }

    /**
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
    public static function isRecurYearly2( array $recur ) : bool
    {
        static $ACCEPT = [ IcalInterface::BYSETPOS, IcalInterface::WKST ];
        if( IcalInterface::YEARLY !== $recur[IcalInterface::FREQ ] ) {
            return false;
        }
        if( ! isset( $recur[IcalInterface::BYDAY], $recur[IcalInterface::BYMONTH] )) {
            return false;
        }
        foreach( self::$RECURBYX as $byX ) { // all but BYDAY and BYMONTH
            if( in_array( $byX, $ACCEPT, true )) {
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
     * @param string|DateTime $wDateIn
     * @param string|DateTime $fcnStartIn
     * @param null|string|DateTime $fcnEndIn
     * @return array
     * @throws Exception
     * @since  2.29.2 - 2019-03-03
     */
    private static function getRecurSimpleBase(
        array $recur,
        string|DateTime $wDateIn,
        string|DateTime $fcnStartIn,
        null|string|DateTime $fcnEndIn = null
    ) : array
    {
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
        if( isset( $recur[IcalInterface::UNTIL] )) {
            $untilYmd = $recur[IcalInterface::UNTIL]->format( self::$YMD );
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
     * @param array $recur    pattern for recurrency (only value part, params ignored)
     * @param string|DateTime      $wDateIn    component start date
     * @param string|DateTime      $fcnStartIn start date
     * @param null|string|DateTime $fcnEndIn   end date
     * @return array  array([Ymd] => bool)
     * @throws Exception
     * @since  2.27.16 - 2019-03-03
     */
    public static function recurDaily1(
        array $recur,
        string|DateTime $wDateIn,
        string|DateTime $fcnStartIn,
        null|string|DateTime $fcnEndIn = null
    ) : array
    {
        [ $wDate, $wDateYmd, $fcnStartYmd, $endYmd ] =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $plusXdays = sprintf( self::$FMTX, $recur[IcalInterface::INTERVAL] );
        $count     = self::getCount( $recur );
        $result    = $monthDays = [];
        $hasByMonth     = $hasByMonthDays = false;
        $byMonthList    = self::getRecurByMonth( $recur, $hasByMonth );
        $byMonthDayList = self::getRecurByMonthDay( $recur, $hasByMonthDays );
        $byDayList = ( isset( $recur[IcalInterface::BYDAY] )) // number for day in week
            ? self::getRecurByDaysWithNoRelativeWeekdays( $recur[IcalInterface::BYDAY] )
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
                if( $bck1Month !== $currMonth ) {
                    // go forward to next 'BYMONTH'
                    while( ! in_array( (int)$wDate->format( self::$LCM ), $byMonthList, true )) {
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
            if( $hasByMonthDays && ( $bck2Month !== $currMonth )) {
                $bck2Month = $currMonth;
                $monthDays = self::getMonthDaysFromByMonthDayList( // day numbers in month
                    (int) $wDate->format( self::$LCT ),
                    $byMonthDayList
                );
            } // end if
            if( self::inList((int) $wDate->format( self::$LCD ), $monthDays ) &&
                self::inList((int) $wDate->format( self::$LCW ), $byDayList )) { // number for day in week
                $result[$Ymd] = true;
                ++$x;
            } // end if
            $wDate     = $wDate->modify( $plusXdays );
        } // end while
        return $result;
    }

    /**
     * Return array dates based on a DAILY/BYSETPOS recur pattern
     *
     * Recur UNTIL/COUNT/INTERVAL opt (INTERVAL default 1)
     * Recur BYDAYs opt, only fixed weekdays ex. 'TH', not '-1TH'
     * BYSETPOS, only if one of BYMONTH or BYMONTHDAY exists
     * Recur BYMONTH opt
     * Recur BYMONTHDAY opt
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     * @param array $recur    pattern for recurrency (only value part, params ignored)
     * @param string|DateTime $wDateIn    component start date
     * @param string|DateTime $fcnStartIn start date
     * @param null|string|DateTime $fcnEndIn   end date
     * @return array   array([Ymd] => bool)
     * @throws Exception
     * @since  2.40.10 - 2021-12-03
     */
    public static function recurDaily2(
        array $recur,
        string|DateTime $wDateIn,
        string|DateTime $fcnStartIn,
        null|string|DateTime $fcnEndIn = null
    ) : array
    {
        static $TOPREVDAY = '-1 day';
        [ $wDate, $wDateYmd, $fcnStartYmd, $endYmd ] =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $count       = self::getCount( $recur );
        unset( $recur[IcalInterface::COUNT] );
        self::hasSetByPos( $recur );
        $bySetPos    = $recur[IcalInterface::BYSETPOS];
        unset( $recur[IcalInterface::BYSETPOS] );
        $year        = (int) $wDate->format( self::$UCY );
        $month       = (int) $wDate->format( self::$LCM );
        $wDate->setDate( $year, $month, 1 )->modify( $TOPREVDAY );
        $wDateIn2    = clone $wDate;
        $fcnStartIn2 = $wDateIn2->format( self::$YMD );
        $yearEnd     = (int) substr( $endYmd, 0, 4 );
        $monthEnd    = (int) substr( $endYmd, 4, 2 );
        $dayEnd      = (int) substr( $endYmd, 6, 2 );
        $wDate->setDate( $yearEnd, $monthEnd, $dayEnd );
        $fcnEndIn2   = $wDate->setDate( $yearEnd, $monthEnd, (int) $wDate->format( self::$LCT ));
        $result1     = self::recurDaily1( $recur, $wDateIn2, $fcnStartIn2, $fcnEndIn2 );
        $YmdX        = array_keys( $result1 );
        $recurLimits = [ $count, $bySetPos, $wDateYmd, $endYmd ];
        $bspList = $result = [];
        $currYm  = reset( $YmdX );
        $currYm  = (int) substr((string) $currYm  , 0, 6 );
        $x        = 1;
        foreach( $YmdX as $Ymd ) {
            $xYm = (int) substr((string) $Ymd, 0, 6 );
            if( $currYm !== $xYm ) {
                if( ! empty( $bspList )) {
                    self::bySetPosResultAppend( $result, $x, $bspList, $recurLimits );
                    $bspList = [];
                    if( ( $x >= $count ) || ( $endYmd < $Ymd ) ) {
                        break;  // leave foreach !
                    }
                } // endif
                $currYm = $xYm;
            } // end if
            $bspList[$Ymd] = true;
        } // end foreach
        if( ! empty( $bspList )) {
            self::bySetPosResultAppend($result, $x, $bspList, $recurLimits );
        }
        return $result;
    }

    /**
     * Return array dates based on a simple WEEKLY recur pattern without BYDAYSs
     *
     * Recur INTERVAL required (or set to 1)
     * Recur BYMONTH opt.
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     * @param array $recur    pattern for recurrency (only value part, params ignored)
     * @param string|DateTime      $wDateIn    component start date
     * @param string|DateTime      $fcnStartIn start date
     * @param null|string|DateTime $fcnEndIn   end date
     * @return array     array([Ymd] => bool)
     * @throws Exception
     * @since  2.27.16 - 2019-03-03
     */
    public static function recurWeekly1(
        array $recur,
        string|DateTime $wDateIn,
        string|DateTime $fcnStartIn,
        null|string|DateTime $fcnEndIn = null
    ) : array
    {
        [ $wDate, $wDateYmd, $fcnStartYmd, $endYmd ] =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $result      = [];
        $x           = 1;
        $count       = self::getCount( $recur );
        $hasByMonth  = false;
        $byMonthList = self::getRecurByMonth( $recur, $hasByMonth );
        $modifyX     = sprintf( self::$FMTX, ( $recur[IcalInterface::INTERVAL] * 7 ));
        while( $x < $count ) {
            $wDate   = $wDate->modify( $modifyX );
            $Ymd     = $wDate->format( self::$YMD );
            if( $endYmd < $Ymd ) {
                break;
            }
            if( $Ymd <= $fcnStartYmd ) {
                continue;
            }
            if( self::inList((int) $wDate->format( self::$LCM ), $byMonthList )) {
                $result[$Ymd] = true;
                ++$x;
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
     * @param array $recur    pattern for recurrency (only value part, params ignored)
     * @param string|DateTime $wDateIn         component start date
     * @param string|DateTime $fcnStartIn     start date
     * @param null|string|DateTime $fcnEndIn  end date
     * @return array   array([Ymd] => bool)
     * @throws Exception
     * @since  2.27.28 - 2029-09-10
     */
    public static function recurWeekly2(
        array $recur,
        string|DateTime $wDateIn,
        string|DateTime $fcnStartIn,
        null|string|DateTime $fcnEndIn = null
    ) : array
    {
        static $MINUS1DAY = '-1 day';
        [$wDate, $wDateYmd, $fcnStartYmd, $endYmd] =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $result       = [];
        $x            = 1;
        $count        = self::getCount( $recur );
        $byDayList    = self::getRecurByDaysWithNoRelativeWeekdays(
            $recur[IcalInterface::BYDAY] // number(s) for day in week
        );
        $hasByMonth   = false;
        $byMonthList  = self::getRecurByMonth( $recur, $hasByMonth );
        $modify1      = sprintf( self::$FMTX, 1 );
        $targetWeekNo = (int) $wDate->format( self::$UCW );
        // go back to first day of week or first day in month
        while(( 1 !== (int) $wDate->format( self::$LCW )) &&
            ( 1 !== (int) $wDate->format( self::$LCJ ))) {
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
                case( $currWeekNo === $targetWeekNo ) :
                    if( self::inList((int) $wDate->format( self::$LCM ), $byMonthList ) &&
                        self::inList((int) $wDate->format( self::$LCW ), $byDayList )) {
                        $result[$Ymd] = true;
                        ++$x;
                    }
                    $wDate = $wDate->modify( $modify1 );
                    break;
                default :
                    // now is the first day of next week
                    if( 1 < $recur[IcalInterface::INTERVAL] ) {
                        // advance interval weeks
                        $dayNo   = ( 7 * ( $recur[IcalInterface::INTERVAL] - 1 ));
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
     * Recur BYMONTH opt, may have hits in start year
     * Recur BYMONTHDAY opt
     * Recur BYDAYSs opt but no positioned ones only ex 'WE', not '-1WE'
     * Recur BYSETPOS if BYMONTHDAY exists
     * If missing endDate/UNTIL, stopDate is set to (const) EXTENDYEAR year from startdate (emergency break)
     *
     * @param array $recur    pattern for recurrency (only value part, params ignored)
     * @param string|DateTime $wDateIn    component start date, string / Datetime
     * @param string|DateTime $fcnStartIn start date, string / Datetime
     * @param null|string|DateTime $fcnEndIn   end date, string / Datetime
     * @return array  array([Ymd] => bool)
     * @throws Exception
     * @since  2.29.24 - 2020-08-29
     */
    public static function recurMonthly1(
        array $recur,
        string|DateTime $wDateIn,
        string|DateTime $fcnStartIn,
        null|string|DateTime $fcnEndIn = null
    ) : array
    {
        [ $wDate, $wDateYmd, $fcnStartYmd, $endYmd ] =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $firstYmd  = $wDateYmd;
        $hasBSP    = self::hasSetByPos( $recur );
        $count     = self::getCount( $recur );
        $result    = $monthDays = $byDayList = $bspList = [];
        $hasByMonthDays = $hasByMonth = $hasByDay = false;
        $byMonthList    = self::getRecurByMonth( $recur, $hasByMonth );
        $byMonthDayList = self::getRecurByMonthDay( $recur, $hasByMonthDays );
        if( isset( $recur[IcalInterface::BYDAY] )) {
            $hasByDay = true;
            $byDayList = self::getRecurByDaysWithNoRelativeWeekdays(
                $recur[IcalInterface::BYDAY] // number(s) for day in week
            );
        }
        $year        = (int) $wDate->format( self::$UCY );
        $month       = (int) $wDate->format( self::$LCM );
        $recurLimits = [];
        $currMonth   = -1;
        if( $hasByMonthDays || $hasByDay ) {
            if( $hasByMonthDays ) {
                $monthDays = self::getMonthDaysFromByMonthDayList(
                    (int)$wDate->format( self::$LCT ), // day numbers in month
                    $byMonthDayList
                );
                if( $hasBSP ) {
                    $recurLimits = [ $count, $recur[IcalInterface::BYSETPOS], $wDateYmd, $endYmd ];
                }
            }
            $day       = 1;
            $currMonth = $month;
//            $x         = 0;
        } // end if
        else {
            $day = (int) $wDate->format( self::$LCJ );
//            $x         = 1;
        }
        $plusXmonth = $recur[IcalInterface::INTERVAL] . Util::$SP0 . RecurFactory::$LCMONTH;
        $x          = 1;
        while( $x <= $count ) {
            if( $month !== $currMonth ) {
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
                    $currMonth = -1;
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
                ++$day;
            }
            if( ! checkdate( $month, $day, $year )) {
                $currMonth = -1;
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
                case ( $Ymd <= $firstYmd ); // accept all but first
                    break;
                case ( self::inList( $day, $monthDays )) :
                    if( self::inList( $dayNo, $byDayList )) { // empty or hit
                        ++$x;
                        $result[$Ymd] = true;
                        if( $x >= $count ) {
                            break 2;  // leave while !!
                        }
                    } // end if
                    if( ! $hasByMonthDays && ! $hasByDay ) {
                        $currMonth = -1;
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
     * Recur BYMONTH MONTHLY opt, with YEARLY required, may have hits in start year
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
     * @param array $recur    pattern for recurrency (only value part, params ignored)
     * @param string|DateTime $wDateIn        component start date
     * @param string|DateTime $fcnStartIn     start date
     * @param null|string|DateTime $fcnEndIn  end date
     * @return array  array([Ymd] => bool)
     * @throws Exception
     * @since  2.40.7 - 2021-11-19
     */
    public static function recurMonthlyYearly3(
        array $recur,
        string|DateTime $wDateIn,
        string|DateTime $fcnStartIn,
        null|string|DateTime $fcnEndIn = null
    ) : array
    {
        [ $wDate, $wDateYmd, $fcnStartYmd, $endYmd ] =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $isYearly    = isset( $recur[IcalInterface::YEARLY] );
        $hasBSP      = self::hasSetByPos( $recur );
        $count       = self::getCount( $recur );
        $result      = $bspList = [];
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
            ? $recur[IcalInterface::INTERVAL] . Util::$SP0 . RecurFactory::$LCYEAR
            : $recur[IcalInterface::INTERVAL] . Util::$SP0 . RecurFactory::$LCMONTH;
        $weekDaysInMonth = self::getRecurByDaysInMonth( $recur[IcalInterface::BYDAY], $year, $month );
        $recurLimits = [];
        if( $hasBSP ) {
            $recurLimits = [ $count, $recur[IcalInterface::BYSETPOS], $wDateYmd, $endYmd ];
            $wDate->setDate( $year, $month, 1 ); //
            $year  = (int) $wDate->format( self::$UCY );
            $month = (int) $wDate->format( self::$LCM );
            $day   = (int) $wDate->format( self::$LCJ );
        }
        $currMonth = $month;
        $currYear  = $year;
        $x         = ( isset( $recur[IcalInterface::BYDAY] ) &&
            self::hasRecurByDaysWithRelativeWeekdays( $recur[IcalInterface::BYDAY] )) ? 0 : 1;
        while( $x < $count ) {
            if( ! checkdate( $month, $day, $year )) {
                $currMonth    = -1;
                if( $isYearly && ( 12 === $month )) {
                    $currYear = -1;
                }
            }
            if(( $month !== $currMonth ) || ( $isYearly && ( $year !== $currYear ))) {
                $day       = 1;
                if( $isYearly && ( $year !== $currYear )) {
                    $wDate->setDate( $year, 1, $day )->modify( $modifier );
                    $year  = (int) $wDate->format( self::$UCY );
                    $month = 1;
                }
                else {
                    $wDate->setDate( $year, $month, $day )->modify( $modifier );
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
                    $currMonth = -1;
                    if( $isYearly && ( 12 === $month )) {
                        $currYear = -1;
                    }
                    continue;
                }
                $currYear  = $year;
                $currMonth = $month;
                $weekDaysInMonth = self::getRecurByDaysInMonth( $recur[IcalInterface::BYDAY], $year, $month );
            } // end if(( $month !== $currMonth ) || ( $isYearly && ( $year !== $currYear )))
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
                    ++$x;
                    if( $x >= $count ) {
                        break 2;  // leave while !
                    }
                    break;
            } // end switch
            // count day up
            ++$day;
        } // end while
        return $result;
    }

    /**
     * Append result from bspList in conjunction with x/count, bySetPos, start/endYmd
     *
     * @param array $result
     * @param int     $x
     * @param array $bspList
     * @param array $recurLimits  [ count, bySetPos, wDateYmd, endYmd ]
     * @return void
     */
    private static function bySetPosResultAppend(
        array & $result,
        int & $x,
        array $bspList,
        array  $recurLimits
    ) : void
    {
        if( empty( $bspList ) || ( $x >= $recurLimits[0] )) {
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
            if( in_array( $ydmOrder[0], $recurLimits[1], true )) {
                $temp[] = $Ymd;
            }
            elseif( in_array( $ydmOrder[1], $recurLimits[1], true )) {
                $temp[] = $Ymd;
            }
        }
        // if match, update result if Ymd within startYmd - endYmd
        foreach( $temp as $Ymd ) {
            if(( $recurLimits[2] < $Ymd) && ( $Ymd <= $recurLimits[3] )) {
                ++$x;
                $result[$Ymd] = true;
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
     * @param array $recur    pattern for recurrency (only value part, params ignored)
     * @param string|DateTime $wDateIn        component start date
     * @param string|DateTime $fcnStartIn     start date
     * @param null|string|DateTime $fcnEndIn  end date
     * @return array  array([Ymd] => bool)
     * @throws Exception
     * @since  2.29.21 - 2020-01-31
     */
    public static function recurYearly1(
        array $recur,
        string|DateTime $wDateIn,
        string|DateTime $fcnStartIn,
        null|string|DateTime $fcnEndIn = null
    ) : array
    {
        [ $wDate, $wDateYmd, $fcnStartYmd, $endYmd ] =
            self::getRecurSimpleBase( $recur, $wDateIn, $fcnStartIn, $fcnEndIn );
        if( $wDateYmd > $endYmd ) {
            return [];
        }
        $startYmd  = ( $wDateYmd > $fcnStartYmd ) ? $wDateYmd : $fcnStartYmd;
        $result    = [];
        $x         = 1;
        $count     = self::getCount( $recur );
        $hasByMonthDays = $hasByMonth = false;
        $byMonthList    = self::getRecurByMonth( $recur, $hasByMonth );
        $byMonthDayList = self::getRecurByMonthDay( $recur, $hasByMonthDays );
        $currYear  = $year = (int) $wDate->format( self::$UCY );
        $month     = (int) $wDate->format( self::$LCM );
        $day       = (int) $wDate->format( self::$LCJ );
        $currMonth = $firstMonth = $lastMonth = $month;
        if( $hasByMonth || $hasByMonthDays ) {
            $firstMonth = reset( $byMonthList );
            $lastMonth  = end( $byMonthList );
        }
        else {
            $currMonth  = -1;
            $currYear   = -1;
        }
        $isLastMonth = false;
        while(( $x < $count ) && ( $endYmd >= $wDate->format( self::$YMD ))) {
            if( $year !== $currYear ) {
                $year    += (int) $recur[IcalInterface::INTERVAL];
                $currYear = $year;
                if( $hasByMonth ) {
                  $month     = 1;
                  $currMonth = -1;
                }
                $wDate->setDate( $year, $month, $day );
            }  // end if new currYear
            if( $hasByMonth && ( $month !== $currMonth )) {
                switch( true ) {
                    case( ! self::inList( $month, $byMonthList )) : // set first month
                        $currMonth = $month = $firstMonth;
                        break;
                    case( $isLastMonth ) : // set first month
                        $currMonth = $month = $firstMonth;
                        break;
                    case( 1 === count( $byMonthList )) : // step
                        $currYear    = -1;
                        $isLastMonth = true;
                        continue 2; // i.e. cont. while
                    default : // next month in list
                        $nextKey       = array_keys( $byMonthList, $month )[0] + 1;
                        if( isset( $byMonthList[$nextKey] )) {
                            $currMonth = $month = $byMonthList[$nextKey];
                            break;
                        }
                        $currYear    = -1;
                        $isLastMonth = true;
                        continue 2; // i.e. cont. while
                } // end switch
                $isLastMonth = ( $month === $lastMonth );
                $wDate->setDate( $year, $month, $day );
            } // end if month != currMonth
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
                    ++$x;
                    if( $x >= $count ) {
                        break 2;
                    }
                } // end foreach
            } // end if $hasByMonthDays
            elseif( $Ymd > $startYmd ) {
                $result[$Ymd] = true;
                ++$x;
            }
            if( $hasByMonth ) {
                $currMonth = -1;
                if( $isLastMonth ) {
                    $currYear = -1;
                }
            }
            else {
                $currYear = -1;
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
    private static function getCount( array $recur ) : int
    {
        return ( isset( $recur[IcalInterface::COUNT] ))
            ? (int) $recur[IcalInterface::COUNT]
            : PHP_INT_MAX;
    }

    /**
     * Return bool true if byXxxList is empty or needle found in byXxxList
     *
     * @param int   $needle
     * @param int[] $byXxxList
     * @return bool
     * @since  2.27.16 - 2019-03-04
     */
    private static function inList( int $needle, array $byXxxList ) : bool
    {
        if( empty( $byXxxList )) {
            return true;
        }
        return in_array( $needle, $byXxxList, true );
    }

    /**
     * Return bool true if recur setByPos exists, assure array
     *
     * @param array $recur
     * @return bool
     */
    private static function hasSetByPos( array & $recur ) : bool
    {
        if( ! isset( $recur[IcalInterface::BYSETPOS] )) {
            return false;
        }
        if( ! is_array( $recur[IcalInterface::BYSETPOS] )) {
            $recur[IcalInterface::BYSETPOS] =
                explode( self::$COMMA, (string) $recur[IcalInterface::BYSETPOS] );
        }
        self::assureIntArray( $recur[IcalInterface::BYSETPOS] );
        return true;
    }

   /**
     * Return array, recur BYMONTH (sorted month numbers)
     *
     * @param array $recur
     * @param null|bool  $hasByMonth
     * @return int[]
     * @since  2.29.11 - 2019-08-30
     */
    private static function getRecurByMonth( array $recur, ? bool & $hasByMonth = false ) : array
    {
        $byMonthList = [];
        if( isset( $recur[IcalInterface::BYMONTH] )) {
            $byMonthList = is_array( $recur[IcalInterface::BYMONTH] )
                ? $recur[IcalInterface::BYMONTH]
                : [ $recur[IcalInterface::BYMONTH] ];
            self::assureIntArray( $byMonthList );
            sort( $byMonthList, SORT_NUMERIC );
            $hasByMonth = true;
        }
        return $byMonthList;
    }

    /**
     * Return array BYMONTHDAY i.e. sorted day numbers in month
     *
     * @param array $recur
     * @param null|bool $hasByMonthDays
     * @return int[]
     * @since  2.29.11 - 2019-08-30
     */
    private static function getRecurByMonthDay( array $recur, ? bool & $hasByMonthDays = false ) : array
    {
        $byMonthDayList = [];
        if( isset( $recur[IcalInterface::BYMONTHDAY] )) {
            $byMonthDayList = is_array( $recur[IcalInterface::BYMONTHDAY] )
                ? $recur[IcalInterface::BYMONTHDAY]
                : [ $recur[IcalInterface::BYMONTHDAY] ];
            self::assureIntArray( $byMonthDayList );
            $hasByMonthDays = true;
        }
        return $byMonthDayList;
    }

    /**
     * Return array list of monthdays from byMonthDayList
     *
     * Fix also negative days, days before month end, conv to month day no
     *
     * @param int   $daysInMonth
     * @param int[] $byMonthDayList
     * @return int[]
     * @since  2.27.16 - 2019-03-06
     */
    public static function getMonthDaysFromByMonthDayList( int $daysInMonth, array $byMonthDayList ) : array
    {
        $list = [];
        foreach( $byMonthDayList as $byMonthDay ) {
            $list[] = ( 0 < $byMonthDay )
                ? $byMonthDay
                : ( $daysInMonth + 1 + $byMonthDay );
        }
        $list = array_values( array_unique( $list ));
        self::assureIntArray( $list );
        return $list;
    }

    /**
     * Return recur BYDAYs but the relative part of weekday(s) skipped ( ex '-1TH' to 'TH')
     *
     * @param array $recurByDay
     * @return array
     * @since  2.27.16 - 2019-03-03
     */
    private static function getRecurByDaysWithNoRelativeWeekdays( array $recurByDay ) : array
    {
        $dayArr = array_flip( RecurFactory::$DAYNAMES );
        $list   = [];
        foreach( $recurByDay as $BYDAYx => $BYDAYv ) {
            if( is_array( $BYDAYv ) && ctype_digit((string) $BYDAYx )) {
                foreach( $BYDAYv as $BYDAYx2 => $BYDAYv2 ) {
                    if( IcalInterface::DAY === $BYDAYx2 ) {
                        $list[] = $dayArr[$BYDAYv2];
                    }
                }
            } // end if
            elseif( IcalInterface::DAY === $BYDAYx ) {
                $list[] = $dayArr[$BYDAYv];
            }
        } // end foreach
        return $list;
    }

    /**
     * Return bool true if recur BYDAYs has relative weekday(s) ( ex '-1 TH' )
     *
     * @param array $recurByDay
     * @return bool
     * @since  2.27.16 - 2019-03-03
     */
     private static function hasRecurByDaysWithRelativeWeekdays( array $recurByDay ) : bool
     {
         if( empty( $recurByDay )) {
             return false;
         }
         foreach( $recurByDay as $BYDAYx => $BYDAYv ) {
             if(((int) $BYDAYx === $BYDAYx ) && is_array( $BYDAYv )) {
                 // multi ByDay recur
                 foreach( $BYDAYv as $BYDAYx2 => $BYDAYv2 ) {
                     if( IcalInterface::DAY === $BYDAYx2 ) {
                         continue;
                     }
                     return true;
                 } // end foreach
             } // end if
             // single ByDay recur
             elseif( IcalInterface::DAY === $BYDAYx ) {
                 continue;
             }
             else {
                 return true;
             }
         } // end foreach
         return false;
     }

    /**
     * Return recur BYDAYs for spec. year/month, also '-1MO'-type BYDAYs
     *
     * @param array $recurByDay
     * @param int      $year
     * @param int      $month
     * @return int[]
     * @throws Exception
     * @since  2.27.16 - 2019-03-03
     */
    public static function getRecurByDaysInMonth( array $recurByDay, int $year, int $month ) : array
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
            $weekDayNo = (int) $wDate->format( self::$LCW );
            $dayName   = RecurFactory::$DAYNAMES[$weekDayNo];
            $monthDays[$wDay] = [ $dayName, 0, 0 ];
            $dayPos[$dayName] = isset( $dayPos[$dayName] ) ? ( $dayPos[$dayName] + 1 ) : 1;
            $monthDays[$wDay][1] = $dayPos[$dayName];
            ++$wDay;
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
                    if( IcalInterface::DAY === $BYDAYx2 ) {
                        $dayN = $BYDAYv2;
                    }
                    else {
                        $pos = (int) $BYDAYv2;
                    }
                } // end foreach
                if( ! empty( $dayN )) {
                    foreach( self::getMonthDaysFromByDay( $monthDays, $pos, $dayN ) as $monthDay ) {
                        $result[$monthDay] = $monthDay;
                    }
                }
                $dayN = $pos = false;
            } // end if
            // single ByDay recur
            elseif( IcalInterface::DAY === $BYDAYx ) {
                $dayN = $BYDAYv;
            }
            else {
                $pos = (int) $BYDAYv;
            }
        } // end foreach
        // single ByDay recur
        if( ! empty( $dayN )) {
            foreach( self::getMonthDaysFromByDay( $monthDays, $pos, $dayN ) as $monthDay ) {
                $result[$monthDay] = $monthDay;
            }
        }
        self::assureIntArray( $result );
        return array_values( $result );
    }

    /**
     * Return (array) dayNo hits, found in monthDays
     *
     * @param array $monthDays with element dayN, posFromStart, posFromEnd
     * @param bool|int $pos
     * @param string   $dayN      weekday name abbr
     * @return int[]              dayNo hits in month
     */
    private static function getMonthDaysFromByDay( array $monthDays, bool | int $pos, string $dayN ) : array
    {
        $list = [];
        foreach( $monthDays as $dayNo => $dayData ) {
            if( $dayN !== $dayData[0] ) {
                continue;
            }
            if( empty( $pos )) {
                $list[] = (int) $dayNo;
                continue;
            }
            if(( $dayData[1] === $pos ) ||  // positive, posFromStart
                ( $dayData[2] === $pos )) { // negative, posFromEnd
                $list[] = (int) $dayNo;
            }
        } // end foreach
        self::assureIntArray( $list );
        return $list;
    }

    /**
     *  Return DateTime
     *
     * @param DateTime|string $input
     * @return DateTime
     * @throws Exception
     * @throws InvalidArgumentException
     * @since  2.27.16 - 2019-03-03
     */
    private static function dateToDateTime( DateTime | string $input ) : DateTime
    {
        if( $input instanceof DateTime ) {
            return clone $input;
        }
        [ $dateStr, $timezonePart ] =
            DateTimeFactory::splitIntoDateStrAndTimezone((string) $input );
        return DateTimeFactory::getDateTimeWithTimezoneFromString(
            $dateStr,
            $timezonePart,
            IcalInterface::UTC
        );
    }

    /**
     *  Return int[], opt sorted asc
     *
     * @param array $input
     * @param null|bool $sort
     * @return void
     */
    public static function assureIntArray( array & $input, ? bool $sort = true ) : void
    {
        static $CALLBACK = 'intval';
        $output = array_map( $CALLBACK, $input );
        if( $sort ) {
            sort( $output, SORT_NUMERIC );
        }
        $input = $output;
    }
}
