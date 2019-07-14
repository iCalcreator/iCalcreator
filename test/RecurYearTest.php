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
class RecurYearTest extends RecurBaseTest
{
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
        }

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


}
