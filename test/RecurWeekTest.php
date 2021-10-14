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
 * @since  2.29.29 - 2020-09-11
 */
class RecurWeekTest extends RecurBaseTest
{
    /**
     * recur2dateTest3Weekly provider
     * @throws Exception
     */
    public function recur2dateTest3WeeklyProvider() : array
    {

        $dataArr = [];

        $interval = 1;
        for( $ix = 301; $ix <= 309; $ix++ ) {
            $time    = microtime( true );
            $start   = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $wDate   = clone $start;
            $expects = [];
            $count   = 5;
            $x       = 1;
            while( $x < $count ) {
                $expects[] = $wDate->modify( ( $interval * 7 ) . ' days' )->format( 'Ymd' );
                ++$x;
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                (clone $start)->modify( RecurFactory::EXTENDYEAR . ' year' ),
                [
                    IcalInterface::FREQ     => IcalInterface::WEEKLY,
                    IcalInterface::COUNT    => $count,
                    IcalInterface::INTERVAL => $interval
                ],
                $expects,
                $execTime
            ];
            $interval += 2;
        } // end for

        // same as above but with BYDAY MO
        $interval = 1;
        $expects = [
            311 => [ 20200914, 20200921, 20200928, 20201005 ],
            312 => [ 20200928, 20201019, 20201109, 20201130 ],
            313 => [ 20201012, 20201116, 20201221, 20210125 ],
            314 => [ 20201026, 20201214, 20210201, 20210322 ],
            315 => [ 20201109, 20210111, 20210315, 20210517 ]
        ];
        for( $ix = 311; $ix <= 315; $ix++ ) {
            $time    = microtime( true );
            $start   = DateTimeFactory::factory( '20200909', 'Europe/Stockholm' );
            $count   = 5;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                (clone $start)->modify( RecurFactory::EXTENDYEAR . ' year' ),
                [
                    IcalInterface::FREQ     => IcalInterface::WEEKLY,
                    IcalInterface::COUNT    => $count,
                    IcalInterface::INTERVAL => $interval,
                    IcalInterface::BYDAY    => [ [ IcalInterface::DAY => IcalInterface::MO ] ]
                ],
                $expects[$ix],
                0.0
            ];
            $interval += 2;
        } // end for

        $interval = 1;
        for( $ix = 321; $ix <= 329; $ix++ ) {
            $time    = microtime( true );
            $start   = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $end     = (clone $start)->modify( RecurFactory::EXTENDYEAR . ' year' );
            $endYmd  = $end->format( 'Ymd' );
            $expects = [];
            $wDate   = clone $start;
            $saveWeekNo = (int) $wDate->format( 'W' );
            while( true ) {
                if( $saveWeekNo === (int) $wDate->format( 'W' )) {
                    $wDate = $wDate->modify( '1 day' );
                }
                else {
                    $wDate      = $wDate->modify( (( $interval * 7 ) - 6 ) . ' days' );
                    $saveWeekNo = (int) $wDate->format( 'W' );
                }
                $ymd = $wDate->format( 'Ymd' );
                if( $endYmd < $ymd ) {
                    break;
                }
                if( in_array((int) $wDate->format( 'w' ), [ 4, 5 ], true )) {
                    $expects[] = $ymd;
                }
            } // end while
            $execTime  = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    IcalInterface::FREQ     => IcalInterface::WEEKLY,
                    IcalInterface::UNTIL    => clone $end,
                    IcalInterface::INTERVAL => $interval,
                    IcalInterface::BYDAY    => [
                        [ IcalInterface::DAY => IcalInterface::TH ],
                        [ IcalInterface::DAY => IcalInterface::FR ]
                    ]
                ],
                $expects,
                $execTime
            ];
            $interval += 2;
        } // end for

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
                if( ! in_array((int) $wDate->format( 'm' ), $byMonth, true )) {
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
                    IcalInterface::FREQ     => IcalInterface::WEEKLY,
                    IcalInterface::UNTIL    => clone $end,
                    IcalInterface::INTERVAL => $interval,
                    IcalInterface::BYMONTH  => $byMonth
                ],
                $expects,
                $execTime
            ];
            $interval += 2;
            $byMonth[] = $interval;
            sort( $byMonth );
        } // end for

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
            while(( 1 !== (int) $wDate->format( 'w' )) &&
                  ( 1 !== (int) $wDate->format( 'd' ))) {
                $wDate = $wDate->modify( '-1 day' );
            }
            while( true ) {
                $currWeekNo = (int) $wDate->format( 'W' );
                $Ymd        = $wDate->format( 'Ymd' );
                switch( true ) {
                    case( $Ymd <= $startYmd ) :
                        $wDate = $wDate->modify( '1 day' );
                        break;
                    case( $endYmd < $Ymd ) :
                        break 2;
                    case( $currWeekNo === $targetWeekNo ) :
                        if( in_array((int) $wDate->format( 'w' ), [ 4, 5 ], true ) && // TH+FR
                            in_array((int) $wDate->format( 'm' ), $byMonth, true )) {
                            $expects[] = $Ymd;
                        }
                        $wDate = $wDate->modify( '1 day' );
                        break;
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
                    IcalInterface::FREQ     => IcalInterface::WEEKLY,
                    IcalInterface::UNTIL    => clone $end,
                    IcalInterface::INTERVAL => $interval,
                    IcalInterface::BYMONTH  => $byMonth,
                    IcalInterface::BYDAY    => [
                        [ IcalInterface::DAY => IcalInterface::TH ],
                        [ IcalInterface::DAY => IcalInterface::FR ]
                    ]
                ],
                $expects,
                $execTime
            ];
            $interval += 3;
            $byMonth[] = $interval;
            sort( $byMonth );
        } // end for

        return $dataArr;
    }

    /**
     * Testing recur2date
     *
     * @test
     * @dataProvider recur2dateTest3WeeklyProvider
     * @param string   $case
     * @param DateTime $start
     * @param DateTime|array $end
     * @param array    $recur
     * @param array    $expects
     * @param float $prepTime
     * @throws Exception
     */
    public function recur2dateTest3Weekly(
        string           $case,
        DateTime         $start,
        DateTime | array $end,
        array            $recur,
        array            $expects,
        float            $prepTime
    ) : void
    {
        $saveStartDate = clone $start;

        $case3 = substr( $case, 0, 3 );
        if(( '311' <= $case3 ) && ( '319' >= $case3 )) {
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

        if( ! isset( $recur[IcalInterface::INTERVAL] )) {
            $recur[IcalInterface::INTERVAL] = 1;
        }
        $strCase  = str_pad( $case, 12 );
        $recurDisp = str_replace( [PHP_EOL, ' ' ], '', var_export( $recur, true ));
        if( RecurFactory2::isRecurWeekly1( $recur )) {
            $time     = microtime( true );
            $resultX  = RecurFactory2::recurWeekly1( $recur, $start, clone $start, $end );
            $execTime = microtime( true ) - $time;
            echo $strCase . 'week smpl1 time:' . number_format( $execTime, 6 ) . ' : ' .
                implode( ' - ', array_keys( $resultX )) . PHP_EOL; // test ###
            echo $recurDisp . PHP_EOL; // test ###
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
        elseif( RecurFactory2::isRecurWeekly2( $recur )) {
            $time     = microtime( true );
            $resultX  = RecurFactory2::recurWeekly2( $recur, $start, clone $start, $end );
            $execTime = microtime( true ) - $time;
            echo $strCase . 'week smpl2 time:' . number_format( $execTime, 6 ) . ' : ' .
                implode( ' - ', array_keys( $resultX ) ) . PHP_EOL; // test ###
            echo $recurDisp . PHP_EOL; // test ###
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
        else {
            echo $strCase . ' NOT isRecurWeekly1/2 ' . $recurDisp . PHP_EOL;
            $this->fail();
        }
    }
}
