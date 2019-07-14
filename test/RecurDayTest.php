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
use DateTime;
use Exception;

/**
 * class RecurTest, testing selectComponents
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.20 - 2019-05-20
 */
class RecurDayTest extends RecurBaseTest
{

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
}
