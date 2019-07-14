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
 * @since  2.27.20 - 2019-05-20
 */
class RecurMonthTest extends RecurBaseTest
{

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

}
