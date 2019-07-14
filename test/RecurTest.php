<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.28
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

use PHPUnit\Framework\TestCase;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\RecurFactory;
use Kigkonsult\Icalcreator\Util\Util;
use DateTime;
use Exception;

/**
 * class RecurTest, testing selectComponents
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.15 - 2019-03-07
 */
class RecurTest extends TestCase
{
    protected static $ERRFMT = "%s error in case #%s, start %s, end %s, recur:%s";

    protected static $totExpectTime = 0.0;
    protected static $totResultTime = 0.0;

    public static function tearDownAfterClass()
    {
        echo PHP_EOL;
        echo 'Tot result time:' . number_format( self::$totResultTime, 6 ) . PHP_EOL; // test ###
        echo 'Tot expect time:' . number_format( self::$totExpectTime, 6 ) . PHP_EOL; // test ###
    }

    /**
     * recur2dateTest1Yearly provider
     *
     * @throws Exception
     */
    public function recur2dateTest1YearlyProvider() {

        $dataArr = [];

        $interval = 1;
        $count    = 5;
        for( $ix = 111; $ix <= 112; $ix++ ) {
            $time    = microtime( true );
            $start   = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $year    = (int) $start->format( 'Y' );
            $month   = (int) $start->format( 'm' );
            $day     = (int) $start->format( 'd' );
            $end     = ( clone $start )->modify( '10 years' );
            $expects = [];
            $x       = 1;
            while( $x < $count ) {
                $year += $interval;
                $Ymd   = DateTimeFactory::getYMDString( [
                    Util::$LCYEAR  => $year,
                    Util::$LCMONTH => $month,
                    Util::$LCDAY   => $day
                ] );
                $expects[] = $Ymd;
                $x        += 1;
            }
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ     => Vcalendar::YEARLY,
                    Vcalendar::INTERVAL => $interval,
                    Vcalendar::COUNT    => $count,
                ],
                $expects,
                $execTime
            ];
            $interval += 1;
        } // end for

        // rfc example 23 - with interval for-loop
        $count    = 10;
        $mRange   = [];
        for( $ix1 = 1; $ix1 < 5; $ix1++ ) {
            $interval = 1;
            for( $ix2 = 1; $ix2 <= 10; $ix2++ ) {
                $mRange[] = random_int( 1, 12 );
                sort( $mRange );
                $mRange   = array_unique( $mRange );
                $time     = microtime( true );
                $start    = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
                $startYmd = $start->format( 'Ymd' );
                $end      = ( clone $start )->setDate(
                    ((int) $start->format( 'Y' ) + 10 ),
                    (int) $start->format( 'm' ),
                    (int) $start->format( 'd' )
                );
                $endYmd   = $end->format( 'Ymd' );
                $expects  = [];
                $x        = 1;
                $wDate    = clone $start;
                $wDate    = $wDate->setDate(
                    (int) $wDate->format( 'Y' ),
                    1,
                    (int) $wDate->format( 'd' )
                );
                $currYear = (int) $wDate->format( 'Y' );
                while( $x < $count ) {
                    if( $currYear != (int) $wDate->format( 'Y' )) {
                        $wDate   = $wDate->setDate(
                            (int) $wDate->format( 'Y' ) + $interval,
                            1,
                            (int) $wDate->format( 'd' )
                        );
                        $currYear = (int) $wDate->format( 'Y' );
                    }
                    if( $endYmd < $wDate->format( 'Ymd' )) {
                        break;
                    }
                    if( $startYmd < $wDate->format( 'Ymd' )) {
                        if( in_array( $wDate->format( 'm' ), $mRange )) {
                            $expects[] = $wDate->format( 'Ymd' );
                            $x         += 1;
                        }
                        if( 12 == (int) $wDate->format( 'm' )) {
                            $currYear = null;
                            continue;
                        }
                    }
                    $wDate = $wDate->setDate(
                        (int)  $wDate->format( 'Y' ),
                        ((int) $wDate->format( 'm' ) + 1 ),
                        (int)  $wDate->format( 'd' )
                    );
                } // end while
                $execTime  = microtime( true ) - $time;
                $dataArr[] = [
                    '19-23-' . $ix1 . $ix2 . '-' . $interval,
                    $start,
                    $end,
                    [
                        Vcalendar::FREQ     => Vcalendar::YEARLY,
                        Vcalendar::INTERVAL => $interval,
                        Vcalendar::COUNT    => $count,
                        Vcalendar::BYMONTH  => $mRange,
                    ],
                    $expects,
                    $execTime,
                ];
                $interval  += 1;
            } // end for... $x2
        } // end for... $x1

        // rfc example 23 -extended, both byMonth and byMonthDay
        $count    = 20;
        $mRange   = [ 1 ]; // month
        $dRange   = []; // days in month
        $baseDays = [ 4, 8, 12, 16, -16, -12, -8, -4 ];
        for( $ix1 = 1; $ix1 < 5; $ix1++ ) {
            $interval = 1;
            for( $ix2 = 1; $ix2 <= 2; $ix2++ ) {
                $mRange[] = random_int( 4, 12 );
                $mRange   = array_unique( $mRange );
                $mRange   = array_values( $mRange );
                sort( $mRange );
                $dKey     = array_rand( $baseDays );
                $dRange[] = $baseDays[$dKey];
                sort( $dRange );
                $dRange   = array_unique( $dRange );
                $time     = microtime( true );
                $start    = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
                $startYmd = $start->format( 'Ymd' );
                $startYm  = $start->format( 'Ym' );
                $end      = ( clone $start )->setDate(
                    ((int) $start->format( 'Y' ) + 10 ),
                     (int) $start->format( 'm' ),
                     (int) $start->format( 'd' )
                );
                $endYmd    = $end->format( 'Ymd' );
                $expects   = [];
                $x         = 1;
                $wDate     = clone $start;
                $year      = $currYear = (int) $wDate->format( 'Y' );
                $month     = 1;
                $day       = (int) $wDate->format( 'd' );
                $wDate->setDate( $year, $month, $day );
                $currMonth = $month;
                $lastMonth = false;
//                $y = 0;
                while(( $x < $count ) && ( $endYmd > $wDate->format( 'Ymd' ))) {
//                    if( 4000 < ++$y ) break;
                    if( $currYear != (int) $wDate->format( 'Y' )) {
                        $year    += $interval;
                        $currYear = $year;
                        $month         = 1;
                        $currMonth = null;
                    } // end if
                    if( $currMonth != $month ) {
                        if( ! in_array( $month, $mRange )) {
                            $currMonth = $month = reset( $mRange );
                        }
                        elseif( $lastMonth ) {
                            $currMonth = $month = reset( $mRange );
                        }
                        else {
                            $nextKey   = array_keys( $mRange, $month )[0] + 1;
                            $currMonth = $month = $mRange[$nextKey];
                        }
                        $currMonth = $month; 
                        $lastMonth = ( $month == end( $mRange ));
                    }
                    $wDate->setDate( $year, $month, $day );
                    if( $endYmd < $wDate->format( 'Ymd' )) {
                        break;
                    }
                    if( $startYm > $wDate->format( 'Ym' )) {
                        $currMonth    = null;
                        if( 12 == (int) $wDate->format( 'm' )) {
                            $currYear = null;
                        }
                        continue;
                    }
                    if( in_array( $wDate->format( 'm' ), $mRange )) {
                        $xDate = clone $wDate;
                        foreach( RecurFactory::getMonthDaysFromByMonthDayList(
                            (int) $wDate->format( 't' ),
                            $dRange
                        ) as $monthDay ) {
                            if( $x >= $count ) {
                                break 2;
                            }
                            $xDate->setDate(
                                (int) $wDate->format( 'Y' ),
                                (int) $wDate->format( 'm' ),
                                $monthDay
                            );
                            $Ymd = $xDate->format( 'Ymd' );
                            if( $startYmd > $Ymd ) {
                                continue;
                            }
                            if( $endYmd < $Ymd ) {
                                break 2;
                            }
                            $expects[] = $Ymd;
                            $x         += 1;
                        } // end foreach
                    } // end if ... in mRange
                    $currMonth = null;
                    if( $lastMonth ) {
                        $currYear = null;
                    }
                } // end while
                $execTime  = microtime( true ) - $time;
                $dataArr[] = [
                    '19-23e-' . $ix1 . $ix2 . '-' . $interval,
                    $start,
                    $end,
                    [
                        Vcalendar::FREQ       => Vcalendar::YEARLY,
                        Vcalendar::INTERVAL   => $interval,
                        Vcalendar::COUNT      => $count,
                        Vcalendar::BYMONTH    => $mRange,
                        Vcalendar::BYMONTHDAY => $dRange,
                    ],
                    $expects,
                    $execTime,
                ];
                $interval  += 1;
            } // end for... $x2
        } // end for... $x1

        return $dataArr;
    }

    /**
     * Testing recur2date Yearly
     *
     * @test
     * @dataProvider recur2dateTest1YearlyProvider
     * @param int      $case
     * @param DateTime $start
     * @param array|DateTime $end
     * @param array    $recur
     * @param array    $expects
     * @param float    $prepTime
     * @throws Exception
     */
    public function recur2dateTest1Yearly(
        $case,
        DateTime $start,
        $end,
        array $recur,
        array $expects,
        $prepTime ) {
        $saveStartDate = clone $start;

        if( ! isset( $recur[Vcalendar::INTERVAL] )) {
            $recur[Vcalendar::INTERVAL] = 1;
        }

        $result = $this->recur2dateTest(
            $case,
            $start,
            $end,
            $recur,
            $expects,
            $prepTime
        );

//        error_log( $case . ' result : ' . implode( ',', array_keys( $result ))); // test ###
//        error_log( $case . ' expects: ' . implode( ',', $expects )); // test ###

        if( RecurFactory::isSimpleYearlyRecur1( $recur )) {
            $time     = microtime( true );
            $resultX  = RecurFactory::recurYearlySimple1( $recur, $start, clone $start, $end );
            $execTime = microtime( true ) - $time;
            $strCase  = str_pad( $case, 12 );
            echo $strCase . 'year smpl1 time:' . number_format( $execTime, 6 ) . ' : ' .
                implode( ' - ', array_keys( $resultX )) . PHP_EOL; // test ###
            $this->assertEquals(
                array_keys( $result ),
                array_keys( $resultX ),
                sprintf( self::$ERRFMT, __FUNCTION__, $case,
                         $saveStartDate->format( 'Ymd' ),
                         $end->format( 'Ymd' ),
                         var_export( $recur, true )
                )
            );
        } // end if

    }


    /**
     * recur2dateTest2Monthly provider
     */
    public function recur2dateTest2MonthlyProvider() {

        $dataArr = [];

        $time    = microtime( true );
        $start   = DateTimeFactory::factory( '20190105T090000', 'Europe/Stockholm' );
        $wDate   = clone $start;
        $expects = [];
        $count   = 10;
        $x       = 1;
        while( $x < $count ) {
            $wDate    = $wDate->setDate(
                 (int) $wDate->format( 'Y' ),
                ((int) $wDate->format( 'm' ) + 1 ),
                 (int) $wDate->format( 'd' )
            );
            $expects[] = $wDate->format( 'Ymd' );
            $x        += 1;
        }
        $execTime  = microtime( true ) - $time;
        $dataArr[] = [
            21,
            $start,
            $wDate->modify( RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => $count,
            ],
            $expects,
            $execTime
        ];


        $interval = 1;
        $count    = 10;
        for( $ix = 221; $ix <= 229; $ix++ ) {
            $time    = microtime( true );
            $start   = DateTimeFactory::factory( '20190130T0900', 'Europe/Stockholm' );
            $end     = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd  = $end->format( 'Ymd' );
            $wDate   = clone $start;
            $expects = [];
            $x       = 1;
            $day     = (int) $wDate->format( 'd' );
            $month   = (int) $wDate->format( 'm' );
            $year    = (int) $wDate->format( 'Y' );
            while( $x < $count ) {
                $month += $interval;
                if( 12 < $month ) {
                    $year  += (int) floor( $month / 12 );
                    $month  = (int) ( $month % 12 );
                    if( 0 == $month ) {
                        $month = 12;
                    }
                }
                if( ! checkdate( $month, $day, $year )) {
                    continue;
                }
                $Ymd = DateTimeFactory::getYMDString( [
                    Util::$LCYEAR => $year,
                    Util::$LCMONTH => $month,
                    Util::$LCDAY => $day
                ] );
                if( $endYmd < $Ymd ) {
                    break;
                }
                $expects[] = $Ymd;
                $x        += 1;
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' ),
                [
                    Vcalendar::FREQ     => Vcalendar::MONTHLY,
                    Vcalendar::INTERVAL => $interval,
                    Vcalendar::COUNT    => $count,
                ],
                $expects,
                $execTime
            ];
            $interval += 1;
        }

        $interval = 1;
        $byMonth  = [ 1, 5, 12 ];
        $count    = 9;
        for( $ix = 231; $ix <= 239; $ix++ ) {
            $time    = microtime( true );
            $start   = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
//            $end     = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $end     = (clone $start)->modify( 5 . ' years' );
            $endYmd  = $end->format( 'Ymd' );
            $wDate   = clone $start;
            $expects = [];
            $x       = 1;
            $day     = (int) $wDate->format( 'd' );
            $month   = (int) $wDate->format( 'm' );
            $year    = (int) $wDate->format( 'Y' );
            while( $x < $count ) {
                $month += $interval;
                if( 12 < $month ) {
                    $year += (int) floor( $month / 12 );
                    $month = (int) ( $month % 12 );
                    if( 0 == $month ) {
                        $month = 12;
                    }
                }
                if( ! checkdate( $month, $day, $year )) {
                    continue;
                }
                $Ymd = DateTimeFactory::getYMDString( [
                    Util::$LCYEAR  => $year,
                    Util::$LCMONTH => $month,
                    Util::$LCDAY   => $day
                ] );
                if( $endYmd < $Ymd ) {
                    break;
                }
                if( ! in_array( $month, $byMonth )) {
                    continue;
                }
                $expects[] = $Ymd;
                $x        += 1;
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ     => Vcalendar::MONTHLY,
                    Vcalendar::INTERVAL => $interval,
                    Vcalendar::COUNT    => $count,
                    Vcalendar::BYMONTH  => $byMonth,
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
        }

        $interval   = 1;
        $byMonthDay = [ 1 ];
        $count      = 20;
        $switch     = true;
        for( $ix = 241; $ix <= 249; $ix++ ) {
            $time        = microtime( true );
            $start       = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end         = clone $start;
            $end->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd      = $end->format( 'Ymd' );
            $wDate       = clone $start;
            $expects     = [];
            $x           = 1;
            $day         = (int) $wDate->format( 'd' );
            $month       = (int) $wDate->format( 'm' );
            $year        = (int) $wDate->format( 'Y' );
            $monthSave   = $month;
            $lastDayInMonth = (int) $wDate->format( 't' );
            $tz          = $wDate->getTimezone()->getName();
            while( $x < $count ) {
                if( $month != $monthSave ) {
                    $month += $interval;
                    if( 12 < $month ) {
                        $year  += (int) floor( $month / 12 );
                        $month = (int) ( $month % 12 );
                        if( 0 == $month ) {
                            $month = 12;
                        }
                    }
                    $monthSave   = $month;
                    $day         = 1;
                    $date        = DateTimeFactory::factory(
                        DateTimeFactory::getYMDString(
                            [ Util::$LCYEAR => $year, Util::$LCMONTH => $month, Util::$LCDAY => $day ]
                        ),
                        $tz
                    );
                    $lastDayInMonth = (int) $date->format( 't' );
                } // end if
                elseif( $day == $lastDayInMonth ) {
                    $monthSave = null;
                    continue;
                }
                else {
                    $day += 1;
                }
                $match = false;
                foreach( $byMonthDay as $monthDay ) {
                    if( 0 < $monthDay ) {
                        if( $monthDay == $day ) {
                            $match = true;
                            break;
                        }
                    }
                    else {
                        if( ( $lastDayInMonth + 1 + $monthDay ) == $day ) {
                            $match = true;
                            break;
                        }
                    }
                } // end foreach
                $Ymd = DateTimeFactory::getYMDString(
                    [ Util::$LCYEAR => $year, Util::$LCMONTH => $month, Util::$LCDAY => $day ]
                );
                if( $endYmd < $Ymd ) {
                    break;
                }
                if( $match ) {
                    $expects[] = $Ymd;
                    $x += 1;
                }
                if( $x >= $count ) {
                    break;
                }
            } // end while
            $execTime     = microtime( true ) - $time;
            $dataArr[]    = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ       => Vcalendar::MONTHLY,
                    Vcalendar::INTERVAL   => $interval,
                    Vcalendar::COUNT      => $count,
                    Vcalendar::BYMONTHDAY => $byMonthDay,
                ],
                $expects,
                $execTime,
            ];
            $interval    += 2;
            $byMonthDay[] = $switch ? ( 0 - $interval ) : $interval;
            $switch       = ! $switch;
        } // end for

        $interval   = 1;
        $byMonthDay = [ 1, 3, 5, 7, -5, -3, -1 ];
        $byMonth    = [ 1, 12 ];
        $count      = 20;
        $switch     = true;
        for( $ix = 251; $ix <= 259; $ix++ ) {
            $time        = microtime( true );
            $start       = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end         = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd      = $end->format( 'Ymd' );
            $wDate       = clone $start;
            $expects     = [];
            $x           = 1;
            $day         = (int) $wDate->format( 'd' );
            $month       = (int) $wDate->format( 'm' );
            $year        = (int) $wDate->format( 'Y' );
            $monthSave   = $month;
            $lastDayInMonth = (int) $wDate->format( 't' );
            $tz          = $wDate->getTimezone()->getName();
            while( $x < $count ) {
                if( $month != $monthSave ) {
                    $month += $interval;
                    if( 12 < $month ) {
                        $year += (int) floor( $month / 12 );
                        $month = (int) ( $month % 12 );
                        if( 0 == $month ) {
                            $month = 12;
                        }
                    }
                    if( ! in_array( $month, $byMonth )) {
                        continue;
                    }
                    $monthSave   = $month;
                    if( ! empty( $byMonthDay )) {
                        $day    = 1;
                        $lastDayInMonth = (int) ( DateTimeFactory::factory(
                            DateTimeFactory::getYMDString(
                                [ Util::$LCYEAR => $year, Util::$LCMONTH => $month, Util::$LCDAY => $day ]
                            ),
                            $tz
                        ))->format('t' );
                    }
                } // end if
                elseif( $day == $lastDayInMonth ) {
                    $monthSave = null;
                    continue;
                }
                else {
                    $day += 1;
                }
                if( ! checkdate( $month, $day, $year )) {
                    continue;
                }
                $Ymd = DateTimeFactory::getYMDString( [
                    Util::$LCYEAR => $year,
                    Util::$LCMONTH => $month,
                    Util::$LCDAY => $day
                ] );
                if( $endYmd < $Ymd ) {
                    break;
                }
                $match = false;
                foreach( $byMonthDay as $monthDay ) {
                    if( 0 < $monthDay ) {
                        if( $monthDay == $day ) {
                            $match = true;
                            break;
                        }
                    }
                    else {
                        if( ( $lastDayInMonth + 1 + $monthDay ) == $day ) {
                            $match = true;
                            break;
                        }
                    }
                } // end foreach
                if( $match  ) {
                    $expects[] = $Ymd;
                    $x += 1;
                }
            } // end while
            $execTime     = microtime( true ) - $time;
            $dataArr[]    = [
                $ix,
                $start,
                $end,
                [
                    Vcalendar::FREQ       => Vcalendar::MONTHLY,
                    Vcalendar::INTERVAL   => $interval,
                    Vcalendar::COUNT      => $count,
                    Vcalendar::BYMONTH    => $byMonth,
                    Vcalendar::BYMONTHDAY => $byMonthDay,
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
            $switch    = ! $switch;
        } // end for


        $time    = microtime( true );
        $start   = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [];
        $count   = 20;
        $x       = 1;
        $wDate = $wDate->modify( '1 day' );
        while( $x < $count ) {
            if( in_array( $wDate->format( 'w' ), [ 0, 6 ] )) {
                $expects[] = $wDate->format( 'Ymd' );
                $x += 1;
            }
            $wDate = $wDate->modify( '1 day' );
        }
        $execTime = microtime( true ) - $time;
        $dataArr[] = [
            26,
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ     => Vcalendar::MONTHLY,
                Vcalendar::COUNT    => $count,
                Vcalendar::BYDAY    => [
                    [ Vcalendar::DAY => Vcalendar::SA ],
                    [ Vcalendar::DAY => Vcalendar::SU ]
                ]
            ],
            $expects,
            $execTime
        ];

        // rfc example 14
        $time    = microtime( true );
        $start   = DateTimeFactory::factory( '20190101T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [];
        $count   = 10;
        $x       = 1;
        $saveM   = null;
        $wDate   = $wDate->modify( '1 day' );
        $firstFr = false;
        while( $x < $count ) {
            if( $saveM  != $wDate->format( 'm' )) {
                $saveM   = $wDate->format( 'm' );
                $firstFr = true;
            }
            if( $firstFr && ( 'Fri' == $wDate->format( 'D' ))) {
                $expects[] = $wDate->format( 'Ymd' );
                $x += 1;
                $firstFr = false;
                $wDate = $wDate->setDate(
                     (int) $wDate->format( 'Y' ),
                    ((int) $wDate->format( 'm' ) + 1 ),
                    1
                );
            }
            else {
                $wDate = $wDate->modify( '1 day' );
            }
        } // end while
        $execTime = microtime( true ) - $time;
        $dataArr[] = [
            '29-14',
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => $count,
                Vcalendar::BYDAY      => [ 1, Vcalendar::DAY => Vcalendar::FR ],
            ],
            $expects,
            $execTime
        ];

        // rfc example 16
        $time    = microtime( true );
        $start   = DateTimeFactory::factory( '20190101T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [];
        $count   = 10;
        $x       = 1;
        $saveYm  = $wDate->format( 'Ym' );
        $wDate   = $wDate->modify( '1 day' );
        $firstFr = false;
        $lastFr  = null;
        while( $x < $count ) {
            if( $saveYm != $wDate->format( 'Ym' )) {
                if( ! empty( $lastFr )) {
                    $expects[] = $lastFr;
                    $x += 1;
                    $lastFr  = null;
                    continue;
                }
                $wDate = $wDate->setDate( // interval=2
                     (int) $wDate->format( 'Y' ),
                    ((int) $wDate->format( 'm' ) + 1 ),
                    1
                );
                $saveYm   = $wDate->format( 'Ym' );
                $firstFr = false;
            }
            if( 'Fri' == $wDate->format( 'D' )) {
                if( ! $firstFr ) {
                    $expects[] = $wDate->format( 'Ymd' );
                    $x += 1;
                    $firstFr   = true;
                }
                else {
                    $lastFr = $wDate->format( 'Ymd' );
                }
            }
            $wDate  = $wDate->modify( '1 day' );
        } // end while
        $execTime = microtime( true ) - $time;
        $dataArr[] = [
            '29-16-2',
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::INTERVAL   => 2,
                Vcalendar::COUNT      => $count,
                Vcalendar::BYDAY      => [
                    [  1, Vcalendar::DAY => Vcalendar::FR ],
                    [ -1, Vcalendar::DAY => Vcalendar::FR ],
                ]
            ],
            $expects,
            $execTime
        ];

        // rfc example 17
        $time    = microtime( true );
        $start   = DateTimeFactory::factory( '20190101T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [];
        $count   = 6;
        $x       = 1;
        $saveYm  = $wDate->format( 'Ym' );
        $wDate   = $wDate->modify( '1 day' );
        $mondays = [];
        while( $x < $count ) {
            if( $saveYm != $wDate->format( 'Ym' )) {
                if( ! empty( $mondays )) {
                    $expects[] =current( array_slice( $mondays, -2, 1 ));
                    $x += 1;
                    $mondays = [];
                    continue;
                }
                $saveYm = $wDate->format( 'Ym' );
            }
            if( 'Mon' == $wDate->format( 'D' )) {
                $mondays[] = $wDate->format( 'Ymd' );
            }
            $wDate  = $wDate->modify( '1 day' );
        } // end while
        $execTime = microtime( true ) - $time;
        $dataArr[] = [
            '29-17',
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => $count,
                Vcalendar::BYDAY      => [ -2, Vcalendar::DAY => Vcalendar::MO ],
            ],
            $expects,
            $execTime
        ];

        // rfc example 18 (extended) see also #23, above
        $time    = microtime( true );
        $start   = DateTimeFactory::factory( '20190101T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [];
        $count   = 10;
        $x       = 1;
        $saveYm  = $wDate->format( 'Ym' );
        $wDate   = $wDate->modify( '1 day' );
        $mDays   = [];
        while( $x < $count ) {
            if( $saveYm != $wDate->format( 'Ym' )) {
                if( ! empty( $mDays )) {
                    $expects[] =current( array_slice( $mDays, -3, 1 ));
                    $x    += 1;
                    $mDays = [];
                    continue;
                }
                $saveYm = $wDate->format( 'Ym' );
            }
            $mDays[] = $wDate->format( 'Ymd' );
            $wDate   = $wDate->modify( '1 day' );
        } // end while
        $execTime = microtime( true ) - $time;
        $dataArr[] = [
            '29-18',
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => $count,
                Vcalendar::BYMONTHDAY => -3,
            ],
            $expects,
            $execTime
        ];

        // rfc example 19 - 20
        $dateString   = '1997-09-02 09:00:00';
        $byMonthDays  = [ 2, 15 ];
        for( $ix = 19; $ix <= 20; $ix++ ) {
            $time     = microtime( true );
            $start    = DateTimeFactory::factory( $dateString, 'America/Los_Angeles' );
            $startYmd = $start->format( 'Ymd' );
            $end      = ( clone $start )->modify( RecurFactory::EXTENDYEAR . ' year' );
            $wDate    = clone $start;
            $expects  = [];
            $count    = 10;
            $x        = 1;
            $year     = (int) $wDate->format( 'Y' );
            $month    = (int) $wDate->format( 'm' );
            while( $x < $count ) {
                $daysInMonth = (int) $wDate->format( 't' );
                foreach( RecurFactory::getMonthDaysFromByMonthDayList(
                    $daysInMonth, $byMonthDays ) as $monthDay ) {
                    $wDate = $wDate->setDate(
                        $year,
                        $month,
                        $monthDay
                    );
                    $Ymd = $wDate->format( 'Ymd' );
                    if( $startYmd >= $Ymd ) {
                        continue;
                    }
                    if( $x >= $count ) {
                        break;
                    }
                    $expects[] = $Ymd;
                    $x         += 1;
                } // end foreach
                $wDate = $wDate->setDate(
                    $year,
                    $month + 1,
                    1
                );
                $year  = (int) $wDate->format( 'Y' );
                $month = (int) $wDate->format( 'm');
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                '29-' . $ix,
                $start,
                $end,
                [
                    Vcalendar::FREQ       => Vcalendar::MONTHLY,
                    Vcalendar::COUNT      => $count,
                    Vcalendar::BYMONTHDAY => $byMonthDays,
                ],
                $expects,
                $execTime
            ];
            $dateString = '1997-09-30 09:00:00';
            $byMonthDays = [ 1, -1 ];

        }

        // rfc example 21
        $time       = microtime( true );
        $start      = DateTimeFactory::factory( '20190101T090000', 'Europe/Stockholm');
        $wDate      = clone $start;
        $expects    = [];
        $count      = 10;
        $interval   = 18;
        $byMonthDay = range( 10,15 );
        $x          = 1;
        $wDate      = $wDate->modify( '1 day' );
        while( $x < $count ) {
            if( 10 > $wDate->format( 'd' )) {
                $wDate     = $wDate->modify( '1 day' );
            }
            elseif( in_array( $wDate->format( 'd' ), $byMonthDay )) {
                $expects[] = $wDate->format( 'Ymd' );
                $x        += 1;
                $wDate     = $wDate->modify( '1 day' );
            }
            else {
                $wDate = $wDate->setDate( // interval=18
                     (int) $wDate->format( 'Y' ),
                    ((int) $wDate->format( 'm' ) + 18 ),
                    10
                );
            }
        } // end while
        $execTime = microtime( true ) - $time;
        $dataArr[] = [
            '29-21-18',
            $start,
            (clone $start)->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => $count,
                Vcalendar::INTERVAL   => $interval,
                Vcalendar::BYMONTHDAY => $byMonthDay,
            ],
            $expects,
            $execTime
        ];


        return $dataArr;
    }

    /**
     * Testing recur2date Monthly
     *
     * @test
     * @dataProvider recur2dateTest2MonthlyProvider
     * @param int      $case
     * @param DateTime $start
     * @param array|DateTime $end
     * @param array    $recur
     * @param array    $expects
     * @param float    $prepTime
     * @throws Exception
     */
    public function recur2dateTest2Monthly(
        $case,
        DateTime $start,
        $end,
        array $recur,
        array $expects,
        $prepTime ) {
        $saveStartDate = clone $start;

        $result = $this->recur2dateTest(
            $case,
            $start,
            $end,
            $recur,
            $expects,
            $prepTime
        );

        if( ! isset( $recur[Vcalendar::INTERVAL] )) {
            $recur[Vcalendar::INTERVAL] = 1;
        }
        if( RecurFactory::isSimpleMonthlyRecur1( $recur )) {
            $time     = microtime( true );
            $resultX  = RecurFactory::recurMonthlySimple1( $recur, $start, clone $start, $end );
            $execTime = microtime( true ) - $time;
            $strCase  = str_pad( $case, 12 );
            echo $strCase . 'mnth smpl1 time:' . number_format( $execTime, 6 ) . ' : ' .
                implode( ' - ', array_keys( $resultX )) . PHP_EOL; // test ###
            $this->assertEquals(
                array_keys( $result ),
                array_keys( $resultX ),
                sprintf( self::$ERRFMT, __FUNCTION__, $case . '-21',
                         $saveStartDate->format( 'Ymd' ),
                         $end->format( 'Ymd' ),
                         var_export( $recur, true )
                )
            );
        } // end if
    }


    /**
     * recur2dateTest3Weekly provider
     */
    public function recur2dateTest3WeeklyProvider() {

        $dataArr = [];

        $interval = 1;
        for( $ix = 311; $ix <= 319; $ix++ ) {
            $time    = microtime( true );
            $start   = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $wDate   = clone $start;
            $expects = [];
            $count   = 5;
            $x       = 1;
            while( $x < $count ) {
                $expects[] = $wDate->modify( ( $interval * 7 ) . ' days' )->format( 'Ymd' );
                $x         += 1;
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                (clone $start)->modify( RecurFactory::EXTENDYEAR . ' year' ),
                [
                    Vcalendar::FREQ     => Vcalendar::WEEKLY,
                    Vcalendar::COUNT    => $count,
                    Vcalendar::INTERVAL => $interval
                ],
                $expects,
                $execTime
            ];
            $interval += 2;
        }

        $interval = 1;
        for( $ix = 321; $ix <= 329; $ix++ ) {
            $time    = microtime( true );
            $start   = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end     = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' year' );
            $endYmd  = $end->format( 'Ymd' );
            $expects = [];
            $wDate   = clone $start;
            $saveWeekNo = $wDate->format( 'W' );
            while( true ) {
                if( $saveWeekNo == $wDate->format( 'W' )) {
                    $wDate = $wDate->modify( '1 day' );
                }
                else {
                    $wDate = $wDate->modify( (( $interval * 7 ) - 6 ) . ' days' );
                    $saveWeekNo = $wDate->format( 'W' );
                }
                $ymd = $wDate->format( 'Ymd' );
                if( $endYmd < $ymd ) {
                    break;
                }
                if( in_array( $wDate->format( 'w' ), [ 4, 5 ] )) {
                    $expects[] = $ymd;
                }
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ     => Vcalendar::WEEKLY,
                    Vcalendar::UNTIL    => DateTimeFactory::getDateArrayFromDateTime( $end ),
                    Vcalendar::INTERVAL => $interval,
                    Vcalendar::BYDAY    => [
                        [ Vcalendar::DAY => Vcalendar::TH ],
                        [ Vcalendar::DAY => Vcalendar::FR ]
                    ]
                ],
                $expects,
                $execTime
            ];
            $interval += 2;
        }

        $interval = 1;
        $byMonth  = [ 12 ];
        for( $ix = 331; $ix <= 339; $ix++ ) {
            $time    = microtime( true );
            $start   = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end     = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd  = $end->format( 'Ymd' );
            $expects = [];
            $wDate   = clone $start;
            while( true ) {
                $wDate = $wDate->modify( ( $interval * 7 ) . ' days' );
                if( ! in_array( $wDate->format( 'm' ), $byMonth )) {
                    continue;
                }
                $ymd = $wDate->format( 'Ymd' );
                if( $endYmd <= $ymd ) {
                    break;
                }
                $expects[] = $ymd;
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ     => Vcalendar::WEEKLY,
                    Vcalendar::UNTIL    => DateTimeFactory::getDateArrayFromDateTime( $end ),
                    Vcalendar::INTERVAL => $interval,
                    Vcalendar::BYMONTH  => $byMonth
                ],
                $expects,
                $execTime
            ];
            $interval += 2;
            $byMonth[] = $interval;
            sort( $byMonth );
        }

        $interval = 1;
        $byMonth  = [ 1, 12 ];
        for( $ix = 341; $ix <= 349; $ix++ ) {
            $time     = microtime( true );
            $start    = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $startYmd = $start->format( 'Ymd' );
            $end      = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd   = $end->format( 'Ymd' );
            $expects  = [];
            $wDate    = clone $start;
            $targetWeekNo = (int) $wDate->format( 'W' );
            // go back to first day of week or first day in month
            while(( 1 != $wDate->format( 'w' )) &&
                  ( 1 != $wDate->format( 'd' ))) {
                $wDate = $wDate->modify( '-1 day' );
            }
            while( true ) {
                $currWeekNo = (int) $wDate->format( 'W' );
                $Ymd        = $wDate->format( 'Ymd' );
                switch( true ) {
                    case( $Ymd <= $startYmd ) :
                        $wDate = $wDate->modify( '1 day' );
                        continue;
                        break;
                    case( $endYmd < $Ymd ) :
                        break 2;
                    case( $currWeekNo == $targetWeekNo ) :
                        if( in_array( $wDate->format( 'w' ), [ 4, 5 ] )) { // TH+FR
                            if( in_array( $wDate->format( 'm' ), $byMonth )) {
                                $expects[] = $Ymd;
                            }
                        }
                        $wDate = $wDate->modify( '1 day' );
                        continue;
                    default :
                        // now is the first day of next week
                        if( 1 < $interval ) {
                            $wDate = $wDate->modify( ( 7 * ( $interval - 1 )) . ' days' );
                        }
                        $targetWeekNo = (int) $wDate->format( 'W' );
                } // end switch
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ     => Vcalendar::WEEKLY,
                    Vcalendar::UNTIL    => DateTimeFactory::getDateArrayFromDateTime( $end ),
                    Vcalendar::INTERVAL => $interval,
                    Vcalendar::BYMONTH  => $byMonth,
                    Vcalendar::BYDAY    => [
                        [ Vcalendar::DAY => Vcalendar::TH ],
                        [ Vcalendar::DAY => Vcalendar::FR ]
                    ]
                ],
                $expects,
                $execTime
            ];
            $interval += 3;
            $byMonth[] = $interval;
            sort( $byMonth );
        }

        return $dataArr;
    }


    /**
     * Testing recur2date
     *
     * @test
     * @dataProvider recur2dateTest3WeeklyProvider
     * @param int      $case
     * @param DateTime $start
     * @param array|DateTime $end
     * @param array    $recur
     * @param array    $expects
     * @param float    $prepTime
     * @throws Exception
     */
    public function recur2dateTest3Weekly(
        $case,
        DateTime $start,
        $end,
        array $recur,
        array $expects,
        $prepTime ) {
        $saveStartDate = clone $start;

        $result = $this->recur2dateTest(
            $case,
            $start,
            $end,
            $recur,
            $expects,
            $prepTime
        );

        if( ! isset( $recur[Vcalendar::INTERVAL] )) {
            $recur[Vcalendar::INTERVAL] = 1;
        }
        if( RecurFactory::isSimpleWeeklyRecur1( $recur )) {
            $time     = microtime( true );
            $resultX  = RecurFactory::recurWeeklySimple1( $recur, $start, clone $start, $end );
            $execTime = microtime( true ) - $time;
            $strCase  = str_pad( $case, 12 );
            echo $strCase . 'week smpl1 time:' . number_format( $execTime, 6 ) . ' : ' .
                implode( ' - ', array_keys( $resultX )) . PHP_EOL; // test ###
            $this->assertEquals(
                array_keys( $result ),
                array_keys( $resultX ),
                sprintf( self::$ERRFMT, __FUNCTION__, $case . '-31',
                         $saveStartDate->format( 'Ymd' ),
                         $end->format( 'Ymd' ),
                         var_export( $recur, true )
                )
            );
        }
        elseif( RecurFactory::isSimpleWeeklyRecur2( $recur )) {
            $time     = microtime( true );
            $resultX  = RecurFactory::recurWeeklySimple2( $recur, $start, clone $start, $end );
            $execTime = microtime( true ) - $time;
            $strCase  = str_pad( $case, 12 );
            echo $strCase . 'week smpl2 time:' . number_format( $execTime, 6 ) . ' : ' .
                implode( ' - ', array_keys( $resultX )) . PHP_EOL; // test ###
            $this->assertEquals(
                array_keys( $result ),
                array_keys( $resultX ),
                sprintf( self::$ERRFMT, __FUNCTION__, $case . '-32',
                         $saveStartDate->format( 'Ymd' ),
                         $end->format( 'Ymd' ),
                         var_export( $recur, true )
                )
            );
        }
    }

    /**
     * recur2dateTest4Daily provider
     */
    public function recur2dateTest4DailyProvider() {

        $dataArr = [];

        // - BYDAY, - BYMONTH, - BYDAYMONTH
        $interval = 1;
        for( $ix = 411; $ix <= 419; $ix++ ) {
            $time     = microtime( true );
            $start    = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $wDate    = clone $start;
            $expects  = [];
            $count    = 10;
            $x        = 1;
            while( $x < $count ) {
                $wDate     = $wDate->modify( $interval . ' days' );
                $expects[] = $wDate->format( 'Ymd' );
                $x += 1;
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                (clone $start)->modify( RecurFactory::EXTENDYEAR . ' year' ),
                [
                    Vcalendar::FREQ     => Vcalendar::DAILY,
                    Vcalendar::COUNT    => $count,
                    Vcalendar::INTERVAL => $interval,
                ],
                $expects,
                $execTime,
            ];
            $interval += 2;
        } // end for

        // + BYDAY, - BYMONTH, - BYDAYMONTH
        $interval = 1; // NOT 7 !!
        for( $ix = 421; $ix <= 429; $ix++ ) {
            if( 7 == $interval ) {
                $interval += 1;
                continue;
            }
            $time     = microtime( true );
            $start    = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $wDate    = clone $start;
            $expects  = [];
            $count    = 10;
            $x        = 1;
            while( $x < $count ) {
                $wDate  = $wDate->modify( $interval . ' days' );
                if( 4 == (int) $wDate->format( 'w' )) { //TH
                    $expects[] = $wDate->format( 'Ymd' );
                    $x += 1;
                }
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                (clone $start)->modify( RecurFactory::EXTENDYEAR . ' year' ),
                [
                    Vcalendar::FREQ     => Vcalendar::DAILY,
                    Vcalendar::COUNT    => $count,
                    Vcalendar::INTERVAL => $interval,
                    Vcalendar::BYDAY    => [ Vcalendar::DAY => Vcalendar::TH ],
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
        } // end for

        // + BYDAY, - BYMONTH, - BYDAYMONTH
        $interval = 1; // NOT 7 !!
        for( $ix = 421; $ix <= 429; $ix++ ) { // same as above but two days
            if( 7 == $interval ) {
                $interval += 1;
                continue;
            }
            $time    = microtime( true );
            $start   = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $wDate   = clone $start;
            $expects = [];
            $count   = 10;
            $x       = 1;
            while( $x < $count ) {
                $wDate = $wDate->modify( $interval . ' days' );
                if( in_array((int)  $wDate->format( 'w' ), [ 4, 5 ] )) {
                    $expects[] = $wDate->format( 'Ymd' );
                    $x += 1;
                }
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-2-' . $interval,
                $start,
                (clone $start)->modify( RecurFactory::EXTENDYEAR . ' year' ),
                [
                    Vcalendar::FREQ     => Vcalendar::DAILY,
                    Vcalendar::COUNT    => $count,
                    Vcalendar::INTERVAL => $interval,
                    Vcalendar::BYDAY    => [
                        [ Vcalendar::DAY => Vcalendar::TH ],
                        [ Vcalendar::DAY => Vcalendar::FR ],
                    ],
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
        } // end for

        // - BYDAY, + BYMONTH, - BYDAYMONTH
        $interval = 1;
        $byMonth  = [ 2, 4, 6 ];
        for( $ix = 431; $ix <= 439; $ix++ ) {
            if( 7 == $interval ) {
                $interval += 1;
                continue;
            }
            $time      = microtime( true );
            $start     = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end       = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd    = $end->format( 'Ymd' );
            $wDate     = clone $start;
            $expects   = [];
            $count     = 20;
            $x         = 1;
            $saveMonth = null;
            $wDate     = $wDate->modify( $interval . ' days' );
            while( $x < $count ) {
                if( $endYmd < $wDate->format( 'Ymd' )) {
                    break;
                }
                $currMonth = (int) $wDate->format( 'm' );
                if( $saveMonth != $currMonth ) {
                    while( ! in_array( $currMonth, $byMonth )) {
                        $wDate     = $wDate->modify( $interval . ' days' );
                        $currMonth = (int) $wDate->format( 'm' );
                    } // end while
                    $saveMonth = $currMonth;
                }
                $expects[] = $wDate->format( 'Ymd' );
                $x        += 1;
                $wDate = $wDate->modify( $interval . ' days' );
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ     => Vcalendar::DAILY,
                    Vcalendar::COUNT    => $count,
                    Vcalendar::INTERVAL => $interval,
                    Vcalendar::BYMONTH  => $byMonth
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
        } // end for

        // + BYDAY, + BYMONTH, - BYDAYMONTH
        $interval = 1;
        $byMonth  = [ 2, 4, 6, 8, 10, 12 ];
        for( $ix = 441; $ix <= 449; $ix++ ) {
            if( 7 == $interval ) {
                $interval += 1;
                continue;
            }
            $time      = microtime( true );
            $start     = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end       = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd    = $end->format( 'Ymd' );
            $wDate     = clone $start;
            $expects   = [];
            $count     = 10;
            $x         = 1;
            $saveMonth = null;
            $wDate     = $wDate->modify( $interval . ' days' );
            while( $x < $count ) {
                if( $endYmd < $wDate->format( 'Ymd' )) {
                    break;
                }
                $currMonth = (int) $wDate->format( 'm' );
                if( $saveMonth != $currMonth ) {
                    while( ! in_array( $currMonth, $byMonth )) {
                        $wDate     = $wDate->modify( $interval . ' days' );
                        $currMonth = (int) $wDate->format( 'm' );
                    } // end while
                    $saveMonth = $currMonth;
                }
                if( 4 == (int) $wDate->format( 'w' )) { //TH
                    $expects[] = $wDate->format( 'Ymd' );
                    $x += 1;
                }
                $wDate = $wDate->modify( $interval . ' days' );
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ     => Vcalendar::DAILY,
                    Vcalendar::COUNT    => $count,
                    Vcalendar::INTERVAL => $interval,
                    Vcalendar::BYMONTH  => $byMonth,
                    Vcalendar::BYDAY    => [ Vcalendar::DAY => Vcalendar::TH ],
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
        } // end for

        // - BYDAY, - BYMONTH, + BYDAYMONTH
        $interval = 1;
        for( $ix = 451; $ix <= 459; $ix++ ) {
            if( 7 == $interval ) {
                $interval += 1;
                continue;
            }
            $time       = microtime( true );
            $start      = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end        = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd     = $end->format( 'Ymd' );
            $wDate      = clone $start;
            $byMonthDay = range( 10,15 );
            $expects    = [];
            $count      = 10;
            $x          = 1;
            $wDate      = $wDate->modify( $interval . ' days' );
            while( $x < $count ) {
                if( $endYmd < $wDate->format( 'Ymd' )) {
                    break;
                }
                if( in_array( $wDate->format( 'd' ), $byMonthDay )) {
                    $expects[] = $wDate->format( 'Ymd' );
                    $x        += 1;
                }
                $wDate = $wDate->modify( $interval . ' days' );
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ       => Vcalendar::DAILY,
                    Vcalendar::COUNT      => $count,
                    Vcalendar::INTERVAL   => $interval,
                    Vcalendar::BYMONTHDAY => $byMonthDay,
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
        } // end for

        // + BYDAY, - BYMONTH, + BYDAYMONTH
        $interval = 1;
        for( $ix = 461; $ix <= 469; $ix++ ) {
            if( 7 == $interval ) {
                $interval += 1;
                continue;
            }
            $time       = microtime( true );
            $start      = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end        = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd     = $end->format( 'Ymd' );
            $wDate      = clone $start;
            $byMonthDay = range( 10,15 );
            $expects    = [];
            $count      = 10;
            $x          = 1;
            $wDate      = $wDate->modify( $interval . ' days' );
            while( $x < $count ) {
                if( $endYmd < $wDate->format( 'Ymd' )) {
                    break;
                }
                if( in_array( $wDate->format( 'd' ), $byMonthDay ) &&
                    ( 4 == $wDate->format( 'w' ))) { //TH
                    $expects[] = $wDate->format( 'Ymd' );
                    $x        += 1;
                }
                $wDate = $wDate->modify( $interval . ' days' );
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ       => Vcalendar::DAILY,
                    Vcalendar::COUNT      => $count,
                    Vcalendar::INTERVAL   => $interval,
                    Vcalendar::BYMONTHDAY => $byMonthDay,
                    Vcalendar::BYDAY      => [ Vcalendar::DAY => Vcalendar::TH ],
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
        } // end for

        // - BYDAY, + BYMONTH, + BYDAYMONTH
        $interval = 1;
        $byMonth  = [ 2, 4, 6 ];
        for( $ix = 471; $ix <= 479; $ix++ ) {
            if( 7 == $interval ) {
                $interval += 1;
                continue;
            }
            $time       = microtime( true );
            $start      = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end        = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd     = $end->format( 'Ymd' );
            $wDate      = clone $start;
            $byMonthDay = range( 10,15 );
            $expects    = [];
            $count      = 10;
            $x          = 1;
            $wDate      = $wDate->modify( $interval . ' days' );
            while( $x < $count ) {
                if( $endYmd < $wDate->format( 'Ymd' )) {
                    break;
                }
                while( ! in_array( $wDate->format( 'm' ), $byMonth )) {
                    $wDate = $wDate->modify( $interval . ' days' );
                }
                if( in_array( $wDate->format( 'd' ), $byMonthDay )) {
                    $expects[] = $wDate->format( 'Ymd' );
                    $x        += 1;
                }
                $wDate = $wDate->modify( $interval . ' days' );
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ       => Vcalendar::DAILY,
                    Vcalendar::COUNT      => $count,
                    Vcalendar::INTERVAL   => $interval,
                    Vcalendar::BYMONTH    => $byMonth,
                    Vcalendar::BYMONTHDAY => $byMonthDay
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
        } // end for

        // + BYDAY, + BYMONTH, + BYDAYMONTH
        $interval = 1;
        $byMonth  = [ 2, 4, 6, 8, 10, 12 ];
        for( $ix = 481; $ix <= 489; $ix++ ) {
            if( 7 == $interval ) {
                $interval += 1;
                continue;
            }
            $time       = microtime( true );
            $start      = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end        = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' years' );
            $endYmd     = $end->format( 'Ymd' );
            $wDate      = clone $start;
            $byMonthDay = range( 10,15 );
            $expects    = [];
            $count      = 10;
            $x          = 1;
            $wDate      = $wDate->modify( $interval . ' days' );
            while( $x < $count ) {
                if( $endYmd < $wDate->format( 'Ymd' )) {
                    break;
                }
                while( ! in_array( $wDate->format( 'm' ), $byMonth )) {
                    $wDate = $wDate->modify( $interval . ' days' );
                }
                if( in_array( $wDate->format( 'd' ), $byMonthDay ) &&
                  ( 4 == $wDate->format( 'w' ))) { //TH
                    $expects[] = $wDate->format( 'Ymd' );
                    $x        += 1;
                }
                $wDate = $wDate->modify( $interval . ' days' );
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    Vcalendar::FREQ       => Vcalendar::DAILY,
                    Vcalendar::COUNT      => $count,
                    Vcalendar::INTERVAL   => $interval,
                    Vcalendar::BYMONTH    => $byMonth,
                    Vcalendar::BYMONTHDAY => $byMonthDay,
                    Vcalendar::BYDAY      => [ Vcalendar::DAY => Vcalendar::TH ],
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
        } // end for


        return $dataArr;
    }

    /**
     * Testing recur2date Daily
     *
     * @test
     * @dataProvider recur2dateTest4DailyProvider
     * @param int      $case
     * @param DateTime $start
     * @param array|DateTime $end
     * @param array    $recur
     * @param array    $expects
     * @param float    $prepTime
     * @throws Exception
     */
    public function recur2dateTest4Daily(
        $case,
        DateTime $start,
        $end,
        array $recur,
        array $expects,
        $prepTime ) {
        $saveStartDate = clone $start;

        $result = $this->recur2dateTest(
            $case,
            $start,
            $end,
            $recur,
            $expects,
            $prepTime
        );

        if( ! isset( $recur[Vcalendar::INTERVAL] )) {
            $recur[Vcalendar::INTERVAL] = 1;
        }
        if( RecurFactory::isSimpleDailyRecur( $recur )) {
            $time     = microtime( true );
            $resultX  = RecurFactory::recurDailySimple( $recur, $start, clone $start, $end );
            $execTime = microtime( true ) - $time;
            $strCase = str_pad( $case, 12 );
            echo $strCase . 'day smpl   time:' . number_format( $execTime, 6 ) . ' : ' .
                implode( ' - ', array_keys( $resultX )) . PHP_EOL; // test ###
            $this->assertEquals(
                array_keys( $result ),
                array_keys( $resultX ),
                sprintf( self::$ERRFMT, __FUNCTION__, $case . '-41',
                         $saveStartDate->format( 'Ymd' ),
                         $end->format( 'Ymd' ),
                         var_export( $recur, true )
                )
            );
        }
    }
    /**
     * Testing recur2date
     *
     * @param int      $case
     * @param DateTime $start
     * @param array|DateTime $end
     * @param array    $recur
     * @param array    $expects
     * @param float    $prepTime
     * @return array
     * @throws Exception
     */
    public function recur2dateTest(
        $case,
        DateTime $start,
        $end,
        array $recur,
        array $expects,
        $prepTime ) {
        $saveStartDate = clone $start;
        /*
//        $e = Vcalendar::factory()->newVevent(); ??
        $c = Vcalendar::factory();
        $e = $c->newVevent();
        $e->setDtstart( $start )
          ->setRrule( $recur );
        echo PHP_EOL . $case . ' recur ' . var_export( $e->getRrule(), true ) . PHP_EOL; // test ###
        */

        $time1     = microtime( true );
        $result1   = [];
        RecurFactory::fullRecur2date( $result1, $recur, $start, ( clone $start ), $end );
        $execTime1 = microtime( true ) - $time1;
        $time2     = microtime( true );
        $result2   = [];
        RecurFactory::Recur2date( $result2, $recur, $start, ( clone $start ), $end );
        $execTime2 = microtime( true ) - $time2;

        self::$totResultTime += $execTime1;
        self::$totResultTime += $execTime2;
        self::$totExpectTime += $prepTime;

        $strCase = str_pad( $case, 12 );
        echo PHP_EOL .  // test ###
            $strCase . 'resultOld  time:' . number_format( $execTime1, 6 ) . ' : ' . implode( ' - ', array_keys( $result1 )
            ) . PHP_EOL; // test ###
        echo   // test ###
            $strCase . 'resultNew  time:' . number_format( $execTime2, 6 ) . ' : ' . implode( ' - ', array_keys( $result2 )
            ) . PHP_EOL; // test ###
        echo
            $strCase . 'expects    time:' . number_format( $prepTime, 6 ) . ' : ' . implode( ' - ', $expects
            ) . PHP_EOL; // test ###

        $this->assertEquals(
            $expects,
            array_keys( $result1 ),
            sprintf( self::$ERRFMT, __FUNCTION__, $case,
                     $saveStartDate->format( 'Ymd' ),
                     $end->format( 'Ymd' ),
                     var_export( $recur, true )
            )
        );
        $this->assertEquals(
            $expects,
            array_keys( $result2 ),
            sprintf( self::$ERRFMT, __FUNCTION__, $case,
                     $saveStartDate->format( 'Ymd' ),
                     $end->format( 'Ymd' ),
                     var_export( $recur, true )
            )
        );
        return $result1;
    }

}
