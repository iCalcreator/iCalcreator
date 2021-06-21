<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
namespace Kigkonsult\Icalcreator;

use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\RecurFactory;
use Kigkonsult\Icalcreator\Util\RecurFactory2;
use DateTime;
use Exception;

/**
 * class RecurTest, testing selectComponents
 *
 * @since  2.27.20 - 2019-05-20
 */
class RecurMonthTest extends RecurBaseTest
{
    /**
     * recurMonthly1Test provider
     */
    public function recurMonthly1Provider()
    {
        $dataArr   = [];
        $dataSetNo = 0;
        $DATASET   = 'DATASET';

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
                Vcalendar::FREQ  => Vcalendar::MONTHLY,
                Vcalendar::COUNT => $count,
                $DATASET         => $dataSetNo++
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
                    $month  = ( $month % 12 );
                    if( 0 == $month ) {
                        $month = 12;
                    }
                }
                if( ! checkdate( $month, $day, $year )) {
                    continue;
                }
                $Ymd = sprintf( '%04d%02d%02d', $year, $month, $day );
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
                    $DATASET            => $dataSetNo++
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
                    $month = ( $month % 12 );
                    if( 0 == $month ) {
                        $month = 12;
                    }
                }
                if( ! checkdate( $month, $day, $year )) {
                    continue;
                }
                $Ymd = sprintf( '%04d%02d%02d', $year, $month, $day );
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
                    $DATASET            => $dataSetNo++
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
                        $month  = ( $month % 12 );
                        if( 0 == $month ) {
                            $month = 12;
                        }
                    }
                    $monthSave   = $month;
                    $day         = 1;
                    $date        = DateTimeFactory::factory( sprintf( '%04d%02d%02d', $year, $month, $day ), $tz );
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
                $Ymd = sprintf( '%04d%02d%02d', $year, $month, $day );
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
                    $DATASET              => $dataSetNo++
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
                        $month = ( $month % 12 );
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
                        $lastDayInMonth = (int) (
                            DateTimeFactory::factory( sprintf( '%04d%02d%02d', $year, $month, $day ), $tz ))
                            ->format('t' );
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
                $Ymd = sprintf( '%04d%02d%02d', $year, $month, $day );
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
                    $DATASET              => $dataSetNo++
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
            $switch    = ! $switch;
        } // end for

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
                $DATASET              => $dataSetNo++
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
                foreach( RecurFactory2::getMonthDaysFromByMonthDayList(
                    $daysInMonth, $byMonthDays
                ) as $monthDay ) {
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
                    $DATASET              => $dataSetNo++
                ],
                $expects,
                $execTime
            ];
            // rfc example 20
            $dateString = '1997-09-30 09:00:00';
            $byMonthDays = [ 1, -1 ];
        } // end for

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
                $DATASET              => $dataSetNo++
            ],
            $expects,
            $execTime
        ];

        // rfc example 21 BUT only day WE
        $time       = microtime( true );
        $start      = DateTimeFactory::factory( '20200801T090000', 'Europe/Stockholm');
        $wDate      = clone $start;
        $expects    = [];
        $count      = 10;
        $interval   = 2;
        $byMonthDay = range( 10,15 );
        $x          = 1;
        $wDate      = $wDate->modify( '1 day' );
        while( $x < $count ) {
            $day = (int) $wDate->format( 'j' );
            if( 10 > $day ) {
                $wDate = $wDate->modify( '1 day' );
                continue;
            }
            if( 18 < $day ) {
                $wDate = $wDate->setDate( // interval=2
                    (int) $wDate->format( 'Y' ),
                    ((int) $wDate->format( 'm' ) + $interval ),
                    10
                );
                continue;
            }
            if( in_array( $day, $byMonthDay ) &&
                ( 3 == (int) $wDate->format( 'w' ))) {
                $expects[] = $wDate->format( 'Ymd' );
                $x        += 1;
            }
            $wDate = $wDate->modify( '1 day' );
        } // end while
        $execTime = microtime( true ) - $time;
        $dataArr[] = [
            '29-21-18B',
            $start,
            (clone $start)->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => $count,
                Vcalendar::INTERVAL   => $interval,
                Vcalendar::BYMONTHDAY => $byMonthDay,
                Vcalendar::BYDAY      => [ 'DAY' => 'WE' ],
                $DATASET              => $dataSetNo++
            ],
            $expects,
            $execTime
        ];

        // rfc example 21 BUT only FIRST day WE in extended day period 8-22
        $start      = DateTimeFactory::factory( '20200801T090000', 'Europe/Stockholm');
        $byMonthDay = range( 8,22 );
        $expects    = [
            20200812, 20201014, 20201209, 20210210, 20210414, 20210609, 20210811, 20211013, 20211208
        ];
        $dataArr[] = [
            '29-21-18C',
            $start,
            (clone $start)->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => $count,
                Vcalendar::INTERVAL   => $interval,
                Vcalendar::BYMONTHDAY => $byMonthDay,
                Vcalendar::BYDAY      => [ 'DAY' => 'WE' ],
                Vcalendar::BYSETPOS   => 1,
                $DATASET              => $dataSetNo++
            ],
            $expects,
            0.0
        ];

        // rfc example 21 BUT only LAST day WE in extended day period 8-22
        $start      = DateTimeFactory::factory( '20200801T090000', 'Europe/Stockholm');
        $byMonthDay = range( 8,22 );
        $expects    = [
            20200819, 20201021, 20201216, 20210217, 20210421, 20210616, 20210818, 20211020, 20211222
        ];
        $dataArr[] = [
            '29-21-18D',
            $start,
            (clone $start)->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => $count,
                Vcalendar::INTERVAL   => $interval,
                Vcalendar::BYMONTHDAY => $byMonthDay,
                Vcalendar::BYDAY      => [ 'DAY' => 'WE' ],
                Vcalendar::BYSETPOS   => -1,
                $DATASET              => $dataSetNo++
            ],
            $expects,
            0.0
        ];

        // rfc example 21 BUT only second day WE in extended day period 8-22
        $start      = DateTimeFactory::factory( '20200801T090000', 'Europe/Stockholm');
        $byMonthDay = range( 8,22 );
        $expects    = [
            20200819, 20201021, 20201216, 20210217, 20210421, 20210616, 20210818, 20211020, 20211215
        ];
        $dataArr[] = [
            '29-21-18E',
            $start,
            (clone $start)->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => $count,
                Vcalendar::INTERVAL   => $interval,
                Vcalendar::BYMONTHDAY => $byMonthDay,
                Vcalendar::BYDAY      => [ 'DAY' => 'WE' ],
                Vcalendar::BYSETPOS   => 2,
                $DATASET              => $dataSetNo++
            ],
            $expects,
            0.0
        ];

        // rfc example 21 BUT only LAST second day WE in extended day period 8-22
        $start      = DateTimeFactory::factory( '20200801T090000', 'Europe/Stockholm');
        $byMonthDay = range( 8,22 );
        $expects    = [
            20200812, 20201014, 20201209, 20210210, 20210414, 20210609, 20210811, 20211013, 20211215
        ];
        $dataArr[] = [
            '29-21-18F',
            $start,
            (clone $start)->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => $count,
                Vcalendar::INTERVAL   => $interval,
                Vcalendar::BYMONTHDAY => $byMonthDay,
                Vcalendar::BYDAY      => [ 'DAY' => 'WE' ],
                Vcalendar::BYSETPOS   => -2,
                $DATASET              => $dataSetNo++
            ],
            $expects,
            0.0
        ];

        // Latest second and third workday in month before day 26 for onr year
        $start      = DateTimeFactory::factory( '20000701T090000', 'Europe/Stockholm');
        $byMonthDay = range( 18,25 );
        $expects    = [
            20000719,20000725,20000821,20000825,20000919,20000925,20001019,20001025,20001120,20001124,20001219,20001225,
            20010119,20010125,20010219,20010223,20010319,20010323,20010419,20010425,20010521,20010525,20010619
        ];
        $dataArr[] = [
            '31',
            $start,
            (clone $start)->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ       => Vcalendar::MONTHLY,
                Vcalendar::COUNT      => 24,
                Vcalendar::BYMONTHDAY => $byMonthDay,
                Vcalendar::BYDAY      => [
                    [ 'DAY' => 'MO' ],
                    [ 'DAY' => 'TU' ],
                    [ 'DAY' => 'WE' ],
                    [ 'DAY' => 'TH' ],
                    [ 'DAY' => 'FR' ],
                ],
                Vcalendar::BYSETPOS   => [ -1, -5 ],
                $DATASET              => $dataSetNo++
            ],
            $expects,
            0.0
        ];

        return $dataArr;
    }

    /**
     * Testing recurMonthly1
     *
     * @test
     * @dataProvider recurMonthly1Provider
     * @param int|string  $case
     * @param DateTime    $start
     * @param DateTime    $end
     * @param array       $recur
     * @param array       $expects
     * @param float       $prepTime
     * @throws Exception
     */
    public function recurMonthly1Test(
        $case,
        DateTime $start,
        DateTime $end,
        array $recur,
        array $expects,
        $prepTime
    ) {
        /*
        if( '29-21-18D' != $case ) {
            $this->assertTrue( true );
            return;
        }
        */
        $saveStartDate = clone $start;

        if( in_array( $case, [ '29-21-18C', '29-21-18D', '29-21-18E', '29-21-18F', '31' ] )) {
            $result = array_flip( $expects );
        }
        else {
            $result = $this->recur2dateTest(
                $case,
                $start,
                $end,
                $recur,
                $expects,
                $prepTime
            );
        }

        if( ! isset( $recur[Vcalendar::INTERVAL] )) {
            $recur[Vcalendar::INTERVAL] = 1;
        }
        $strCase   = str_pad( $case, 12 );
        $recurDisp = str_replace( [PHP_EOL, ' ' ], '', var_export( $recur, true ));
        if( ! RecurFactory2::isRecurMonthly1( $recur )) {
            echo $strCase . ' NOT isRecurMonthly1 ' . $recurDisp . PHP_EOL;
            $this->assertTrue( false );
            return;
        } // end if
        $time     = microtime( true );
        $resultX  = RecurFactory2::recurMonthly1( $recur, $start, clone $start, $end );
        $execTime = microtime( true ) - $time;
        echo $strCase . 'mnth smpl1 time:' . number_format( $execTime, 6 ) . ' : ' .
            implode( ' - ', array_keys( $resultX ) ) . PHP_EOL; // test ###
        echo $recurDisp . PHP_EOL; // test ###
        $this->assertEquals(
            array_keys( $result ),
            array_keys( $resultX ),
            sprintf( self::$ERRFMT, __FUNCTION__, $case . '-21',
                $saveStartDate->format( 'Ymd' ),
                $end->format( 'Ymd' ),
                var_export( $recur, true )
            )
        );
    }

    /**
     * getRecurByDaysInMonthTest provider
     */
    public function getRecurByDaysInMonthProvider()
    {
        $dataArr = [];

        $dataArr[] = [
            11,
            [ Vcalendar::DAY => Vcalendar::MO ],
            2020,
            8,
            [ 3, 10, 17, 24, 31 ] // exp
        ];

        $dataArr[] = [
            21,
            [ 3, Vcalendar::DAY => Vcalendar::MO ],
            2020,
            8,
            [ 17 ] // exp
        ];

        $dataArr[] = [
            22,
            [ -2, Vcalendar::DAY => Vcalendar::MO ],
            2020,
            8,
            [ 24 ] // exp
        ];

        $dataArr[] = [
            23,
            [ -2, Vcalendar::DAY => Vcalendar::SA ],
            2020,
            2,
            [ 22 ] // exp
        ];

        $dataArr[] = [
            31,
            [
                [ Vcalendar::DAY => Vcalendar::MO ],
                [ Vcalendar::DAY => Vcalendar::FR ],
            ],
            2020,
            8,
            [ 3, 7, 10, 14, 17, 21, 24, 28, 31 ] // exp
        ];

        $dataArr[] = [
            41,
            [
                [ 3, Vcalendar::DAY => Vcalendar::MO ],
                [ -3, Vcalendar::DAY => Vcalendar::FR ],
            ],
            2020,
            8,
            [ 14, 17 ] // exp
        ];

        $dataArr[] = [
            42,
            [
                [ 1, Vcalendar::DAY => Vcalendar::MO ],
                [ 2, Vcalendar::DAY => Vcalendar::TU ],
                [ 3, Vcalendar::DAY => Vcalendar::WE ],
                [ 4, Vcalendar::DAY => Vcalendar::TH ],
                [ 5, Vcalendar::DAY => Vcalendar::FR ],
            ],
            2020,
            8,
            [ 3, 11, 19, 27 ] // exp
        ];

        $dataArr[] = [
            43,
            [
                [ -1, Vcalendar::DAY => Vcalendar::SU ],
                [ -2, Vcalendar::DAY => Vcalendar::SA ],
                [ -3, Vcalendar::DAY => Vcalendar::FR ],
                [ -4, Vcalendar::DAY => Vcalendar::TH ],
                [ -5, Vcalendar::DAY => Vcalendar::WE ],
            ],
            2020,
            8,
            [ 6, 14, 22, 30 ] // exp
        ];

        $dataArr[] = [
            44,
            [
                [ 1, Vcalendar::DAY => Vcalendar::MO ],
                [ 2, Vcalendar::DAY => Vcalendar::TU ],
                [ 3, Vcalendar::DAY => Vcalendar::WE ],
                [ 4, Vcalendar::DAY => Vcalendar::TH ],
                [ 5, Vcalendar::DAY => Vcalendar::FR ],
                [ -1, Vcalendar::DAY => Vcalendar::SU ],
                [ -2, Vcalendar::DAY => Vcalendar::SA ],
                [ -3, Vcalendar::DAY => Vcalendar::FR ],
                [ -4, Vcalendar::DAY => Vcalendar::TH ],
                [ -5, Vcalendar::DAY => Vcalendar::WE ],
            ],
            2020,
            8,
            [ 3, 6, 11, 14, 19, 22, 27, 30 ] // exp
        ];

        return $dataArr;
    }

    /**
     * @test
     * @dataProvider getRecurByDaysInMonthProvider
     */
    public function getRecurByDaysInMonthTest( $case, $recurByDay, $year, $month, $exp )
    {
        $list = RecurFactory2::getRecurByDaysInMonth( $recurByDay, $year, $month );

        $this->assertEquals( $exp, $list,
            $case . ' exp: ' .implode( ', ', $exp ) . ' - got: ' .implode( ', ', $list )
        );

        echo $case . ' : ' . $year . ' - ' . $month . ' recur : ' . str_replace( [PHP_EOL, ' ' ], '', var_export( $recurByDay, true )) . PHP_EOL; // test ###
        echo $case . ' result : ' . implode( ', ', $list ) . PHP_EOL; // test ###
    }

    /**
     * recurMonthly1Test provider
     */
    public function recurMonthly2Provider()
    {
        $dataArr   = [];
        $dataSetNo = 0;
        $DATASET   = 'DATASET';

        $start   = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [
            20190105, 20190106, 20190112, 20190113, 20190119, 20190120, 20190126, 20190127, 20190202, 20190203,
            20190209, 20190210, 20190216, 20190217, 20190223, 20190224, 20190302, 20190303, 20190309
        ];
        $count   = 20;
        $dataArr[] = [
            26,
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ      => Vcalendar::MONTHLY,
                Vcalendar::COUNT     => $count,
                Vcalendar::BYDAY     => [
                    [ Vcalendar::DAY => Vcalendar::SA ],
                    [ Vcalendar::DAY => Vcalendar::SU ]
                ],
                $DATASET             => $dataSetNo++
            ],
            $expects
        ];

        // rfc example 14
        $start   = DateTimeFactory::factory( '20190101T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [ 20190104, 20190201, 20190301, 20190405, 20190503, 20190607, 20190705, 20190802, 20190906 ];
        $count   = 10;
        $dataArr[] = [
            '29-14',
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ     => Vcalendar::MONTHLY,
                Vcalendar::COUNT    => $count,
                Vcalendar::BYDAY    => [ 1, Vcalendar::DAY => Vcalendar::FR ],
                $DATASET            => $dataSetNo++
            ],
            $expects
        ];

        // rfc example 16
        $start   = DateTimeFactory::factory( '20190101T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [ 20190104, 20190125, 20190301, 20190329, 20190503, 20190531, 20190705, 20190726, 20190906 ];
        $count   = 10;
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
                ],
                $DATASET              => $dataSetNo++
            ],
            $expects
        ];

        // rfc example 17
        $start   = DateTimeFactory::factory( '20190101T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [ 20190121, 20190218, 20190318, 20190422, 20190520 ];
        $count   = 6;
        $dataArr[] = [
            '29-17',
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ     => Vcalendar::MONTHLY,
                Vcalendar::COUNT    => $count,
                Vcalendar::BYDAY    => [ -2, Vcalendar::DAY => Vcalendar::MO ],
                $DATASET            => $dataSetNo++
            ],
            $expects
        ];

        // rfc line 2375
        $start   = DateTimeFactory::factory( '20190101T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [ 20190131, 20190228, 20190329, 20190430, 20190531 ]; // all but first
        $count   = 6;
        $dataArr[] = [
            '2375',
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ),
            [
                Vcalendar::FREQ     => Vcalendar::MONTHLY,
                Vcalendar::COUNT    => $count,
                Vcalendar::BYDAY    => [
                    [ Vcalendar::DAY => Vcalendar::MO ],
                    [ Vcalendar::DAY => Vcalendar::TU ],
                    [ Vcalendar::DAY => Vcalendar::WE ],
                    [ Vcalendar::DAY => Vcalendar::TH ],
                    [ Vcalendar::DAY => Vcalendar::FR ],
                ],
                Vcalendar::BYSETPOS => -1,
                $DATASET            => $dataSetNo++
            ],
            $expects
        ];

        // rfc line 2375 but third and last third workday in month
        $start   = DateTimeFactory::factory( '20190101T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [ 20190103, 20190129, 20190205, 20190226, 20190305, 20190327, 20190403, 20190426, 20190503 ]; // all but first
        $count   = 10;
        $dataArr[] = [
            '2375B',
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ), // end
            [
                Vcalendar::FREQ     => Vcalendar::MONTHLY,
                Vcalendar::COUNT    => $count,
                Vcalendar::BYDAY    => [
                    [ Vcalendar::DAY => Vcalendar::MO ],
                    [ Vcalendar::DAY => Vcalendar::TU ],
                    [ Vcalendar::DAY => Vcalendar::WE ],
                    [ Vcalendar::DAY => Vcalendar::TH ],
                    [ Vcalendar::DAY => Vcalendar::FR ],
                ],
                Vcalendar::BYSETPOS => [ 3, -3 ],
                $DATASET            => $dataSetNo++
            ],
            $expects
        ];

        // rfc line 7266 but timezone sthlm and count 6
        $start   = DateTimeFactory::factory( '19970929T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [ 19971030, 19971127, 19971230, 19980129, 19980226 ];
        $dataArr[] = [
            '7266',
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ), // end
            [
                Vcalendar::FREQ     => Vcalendar::MONTHLY,
                Vcalendar::COUNT    => 6,
                Vcalendar::BYDAY    => [
                    [ Vcalendar::DAY => Vcalendar::MO ],
                    [ Vcalendar::DAY => Vcalendar::TU ],
                    [ Vcalendar::DAY => Vcalendar::WE ],
                    [ Vcalendar::DAY => Vcalendar::TH ],
                    [ Vcalendar::DAY => Vcalendar::FR ],
                ],
                Vcalendar::BYSETPOS => -2,
                $DATASET            => $dataSetNo++
            ],
            $expects
        ];

        // rfc line 7258 but timezone sthlm and count 6
        $start   = DateTimeFactory::factory( '19970904T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $expects = [ 19971007,19971106,19971204,19980107,19980205 ];
        $dataArr[] = [
            '7258',
            $start,
            $wDate->modify(  RecurFactory::EXTENDYEAR . ' year' ), // end
            [
                Vcalendar::FREQ      => Vcalendar::MONTHLY,
                Vcalendar::COUNT     => 6,
                Vcalendar::BYDAY     => [
                    [ Vcalendar::DAY => Vcalendar::TU ],
                    [ Vcalendar::DAY => Vcalendar::WE ],
                    [ Vcalendar::DAY => Vcalendar::TH ],
                    ],
                Vcalendar::BYSETPOS  => 3,
                $DATASET             => $dataSetNo++
            ],
            $expects
        ];

        // neotsn Thanksgiving event - 4th Thursday of every November - Yearly - same in recurYearly2Test by YEARLY
        $start   = DateTimeFactory::factory( '20201126T113000', 'America/Chicago');
        $wDate   = clone $start;
        $dataArr[] = [
            'neotsn',
            $start,
            $wDate->modify(  10 . ' year' ), // end
            [
                Vcalendar::FREQ      => Vcalendar::MONTHLY,
                Vcalendar::INTERVAL => 12,
                Vcalendar::BYDAY     => [
                    [ Vcalendar::DAY => Vcalendar::TH ],
                ],
                Vcalendar::BYSETPOS  => 4,
                $DATASET             => $dataSetNo++
            ],
            [ 20211125,20221124,20231123,20241128,20251127,20261126,20271125,20281123,20291122 ]
        ];

        return $dataArr;
    }

    /**
     * Testing recurMonthly2 i.e recurMonthlyYearly3
     *
     * @test
     * @dataProvider recurMonthly2Provider
     * @param int      $case
     * @param DateTime $start
     * @param array|DateTime $end
     * @param array    $recur
     * @param array    $expects
     * @throws Exception
     */
    public function recurMonthly2Test(
        $case,
        DateTime $start,
        $end,
        array $recur,
        array $expects
    ) {
        /*
        if( '7266' != $case ) {
            $this->assertTrue( true );  // test ###
            return;
        }
        */
        $saveStartDate = clone $start;

        $result = $expects;
        /*
        $result = $this->recur2dateTest(
            $case,
            $start,
            $end,
            $recur,
            $expects,
            0.0
        );
        */

//      error_log( 'start case ' . $case ); // test ###

        if( ! isset( $recur[Vcalendar::INTERVAL] )) {
            $recur[Vcalendar::INTERVAL] = 1;
        }
        $strCase   = str_pad( $case, 12 );
        $recurDisp = str_replace( [PHP_EOL, ' ' ], '', var_export( $recur, true ));
        if( ! RecurFactory2::isRecurMonthly2( $recur )) {
            echo $strCase . ' NOT isRecurMonthly2 ' . $recurDisp . PHP_EOL;
            $this->assertTrue( false );
            return;
        } // end if
        $time     = microtime( true );
//      $resultX  = RecurFactory2::recurMonthly2( $recur, $start, clone $start, $end );
        $resultX  = RecurFactory2::recurMonthlyYearly3( $recur, $start, clone $start, $end );
        $execTime = microtime( true ) - $time;
        echo $strCase . 'mnth smpl2 time:' . number_format( $execTime, 6 ) . ' : ' .
            implode( ' - ', array_keys( $resultX )) . PHP_EOL; // test ###
        echo $recurDisp . PHP_EOL; // test ###
        $this->assertEquals(
            $result, // array_keys( $result ),
            array_keys( $resultX ),
            sprintf( self::$ERRFMT, __FUNCTION__, $case . '-21',
                $saveStartDate->format( 'Ymd' ),
                $end->format( 'Ymd' ),
                PHP_EOL . $recurDisp .
                PHP_EOL . 'exp : ' . implode( ',', $expects ) .
                PHP_EOL . 'got : ' . implode( ',', array_keys( $resultX ))
            )
        );
    }
}
