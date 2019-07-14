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
class RecurWeekTest extends RecurBaseTest
{

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

}
