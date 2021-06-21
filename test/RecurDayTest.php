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
class RecurDayTest extends RecurBaseTest
{
    /**
     * recurDaily1Test provider
     */
    public function recurDaily1TestProvider() {

        $dataArr = [];
        $dataSetNo = 0;
        $DATASET   = 'DATASET';

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
                    $DATASET            => $dataSetNo++
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
                    $DATASET            => $dataSetNo++
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
                    Vcalendar::FREQ      => Vcalendar::DAILY,
                    Vcalendar::COUNT     => $count,
                    Vcalendar::INTERVAL  => $interval,
                    Vcalendar::BYDAY     => [
                        [ Vcalendar::DAY => Vcalendar::TH ],
                        [ Vcalendar::DAY => Vcalendar::FR ],
                    ],
                    $DATASET             => $dataSetNo++
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
                    Vcalendar::BYMONTH  => $byMonth,
                    $DATASET            => $dataSetNo++
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
                    $DATASET            => $dataSetNo++
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
                    $DATASET              => $dataSetNo++
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
                    $DATASET              => $dataSetNo++
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
                    Vcalendar::BYMONTHDAY => $byMonthDay,
                    $DATASET              => $dataSetNo++
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
                    $DATASET              => $dataSetNo++
                ],
                $expects,
                $execTime,
            ];
            $interval += 1;
        } // end for

        return $dataArr;
    }

    /**
     * Testing recur2date Daily without BYSETPOS
     *
     * @test
     * @dataProvider recurDaily1TestProvider
     * @param int      $case
     * @param DateTime $start
     * @param array|DateTime $end
     * @param array    $recur
     * @param array    $expects
     * @param float    $prepTime
     * @throws Exception
     */
    public function recurDaily1Test(
        $case,
        DateTime $start,
        $end,
        array $recur,
        array $expects,
        $prepTime
    ) {
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
        $strCase   = str_pad( $case, 12 );
        $recurDisp = str_replace( [PHP_EOL, ' ' ], '', var_export( $recur, true ));
        if( ! RecurFactory2::isRecurDaily1( $recur )) {
            echo $strCase . ' NO isRecurDaily1' . $recurDisp . PHP_EOL;
            $this->assertTrue( false );
            return;
        } // end if
        $time     = microtime( true );
        $resultX  = RecurFactory2::recurDaily1( $recur, $start, clone $start, $end );
        $execTime = microtime( true ) - $time;
        echo $strCase . 'rcrDaily1  time:' . number_format( $execTime, 6 ) . ' : ' .
            implode( ' - ', array_keys( $resultX )) . PHP_EOL; // test ###
        echo $recurDisp . PHP_EOL; // test ###
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

    /**
     * recurDaily11Test provider
     */
    public function recurDaily11TestProvider()
    {
        $dataArr = [];
        $dataSetNo = 0;
        $DATASET   = 'DATASET';

        // + BYDAY, - BYMONTH, + BYDAYMONTH   same as recurDaily2 BUT no BYSETPOS
        $start = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
        $end = ( clone $start )->modify( RecurFactory::EXTENDYEAR . ' years' );
        $dataArr[] = [
            '2001-1',
            $start,
            $end,
            [
                Vcalendar::FREQ     => Vcalendar::DAILY,
                Vcalendar::COUNT    => 20,
                Vcalendar::INTERVAL => 1,
//              Vcalendar::BYMONTH  => [ 2, 4, 6, 8, 10, 12 ],
                Vcalendar::BYMONTHDAY => range( -1,-14 ),
                Vcalendar::BYDAY    => [ Vcalendar::DAY => Vcalendar::TH ],
                $DATASET            => $dataSetNo++,
            ],
            [
                20190124, 20190131, 20190221, 20190228, 20190321, 20190328, 20190418, 20190425, 20190523,
                20190530, 20190620, 20190627, 20190718, 20190725, 20190822, 20190829, 20190919, 20190926, 20191024
            ],
            0.0,
        ];

        // + BYDAY, + BYMONTH, + BYDAYMONTH   same as recurDaily2 BUT no BYSETPOS
        $start = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
        $end = ( clone $start )->modify( RecurFactory::EXTENDYEAR . ' years' );
        $dataArr[] = [
            '2001-2',
            $start,
            $end,
            [
                Vcalendar::FREQ     => Vcalendar::DAILY,
                Vcalendar::COUNT    => 20,
                Vcalendar::INTERVAL => 1,
                Vcalendar::BYMONTH  => [ 2, 4, 6, 8, 10, 12 ],
                Vcalendar::BYMONTHDAY => range( -1,-14 ),
                Vcalendar::BYDAY    => [ Vcalendar::DAY => Vcalendar::TH ],
                $DATASET            => $dataSetNo++,
            ],
            [
                20190221, 20190228, 20190418, 20190425, 20190620, 20190627, 20190822, 20190829,
                20191024, 20191031, 20191219, 20191226, 20200220, 20200227, 20200423, 20200430,
                20200618, 20200625, 20200820
            ],
            0.0,
        ];

        // + BYDAY, + BYMONTH, - BYDAYMONTH   same as recurDaily2 BUT no BYSETPOS
        $start = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
        $end = ( clone $start )->modify( RecurFactory::EXTENDYEAR . ' years' );
        $dataArr[] = [
            '2001-3',
            $start,
            $end,
            [
                Vcalendar::FREQ     => Vcalendar::DAILY,
                Vcalendar::COUNT    => 20,
                Vcalendar::INTERVAL => 1,
                Vcalendar::BYMONTH  => [ 2, 4, 6, 8, 10, 12 ],
                Vcalendar::BYDAY    => [ Vcalendar::DAY => Vcalendar::TH ],
                $DATASET            => $dataSetNo++,
            ],
            [
                20190207, 20190214, 20190221, 20190228,
                20190404, 20190411, 20190418, 20190425,
                20190606, 20190613, 20190620, 20190627,
                20190801, 20190808, 20190815, 20190822, 20190829,
                20191003, 20191010
            ],
            0.0,
        ];

        return $dataArr;
    }

    /**
     * Testing recur1date Daily without BYSETPOS for cmp with recurDaily2Test output
     *
     * @test
     * @dataProvider recurDaily11TestProvider
     * @param int      $case
     * @param DateTime $start
     * @param array|DateTime $end
     * @param array    $recur
     * @param array    $expects
     * @param float    $prepTime
     * @throws Exception
     */
    public function recurDaily11Test(
        $case,
        DateTime $start,
        $end,
        array $recur,
        array $expects,
        $prepTime
    ) {
        $this->recurDaily1Test( $case, $start, $end, $recur, $expects, $prepTime );
    }

    /**
     * recurDaily2Test provider
     */
    public function recurDaily2TestProvider()
    {
        $dataArr   = [];
        $dataSetNo = 0;
        $DATASET   = 'DATASET';

        // + BYDAY, - BYMONTH, + BYDAYMONTH   same as recurDaily11 BUT with BYSETPOS
        $start = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
        $end   = ( clone $start )->modify( RecurFactory::EXTENDYEAR . ' years' );
        $dataArr[] = [
            '2001-1',
            $start,
            $end,
            [
                Vcalendar::FREQ     => Vcalendar::DAILY,
                Vcalendar::COUNT    => 10,
                Vcalendar::INTERVAL => 1,
//              Vcalendar::BYMONTH  => [ 2, 4, 6, 8, 10, 12 ],
                Vcalendar::BYMONTHDAY => range( -1,-14 ),
                Vcalendar::BYDAY    => [ Vcalendar::DAY => Vcalendar::TH ],
                Vcalendar::BYSETPOS => -2,
                $DATASET            => $dataSetNo++,
            ],
            [
                20190124,20190221,20190321,20190418,20190523,20190620,20190718,20190822,20190919
            ],
            0.0,
        ];

        // + BYDAY, + BYMONTH, + BYDAYMONTH   same as recurDaily11 BUT with BYSETPOS
        $start = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
        $end   = ( clone $start )->modify( RecurFactory::EXTENDYEAR . ' years' );
        $dataArr[] = [
            '2001-2',
            $start,
            $end,
            [
                Vcalendar::FREQ     => Vcalendar::DAILY,
                Vcalendar::COUNT    => 10,
                Vcalendar::INTERVAL => 1,
                Vcalendar::BYMONTH  => [ 2, 4, 6, 8, 10, 12 ],
                Vcalendar::BYMONTHDAY => range( -1,-14 ),
                Vcalendar::BYDAY    => [ Vcalendar::DAY => Vcalendar::TH ],
                Vcalendar::BYSETPOS => -2,
                $DATASET            => $dataSetNo++,
            ],
            [
                20190221, 20190418, 20190620, 20190822,
                20191024, 20191219, 20200220, 20200423,
                20200618
            ],
            0.0,
        ];

        // + BYDAY, + BYMONTH, - BYDAYMONTH   same as recurDaily11 BUT with BYSETPOS
        $start = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
        $end = ( clone $start )->modify( RecurFactory::EXTENDYEAR . ' years' );
        $dataArr[] = [
            '2001-3',
            $start,
            $end,
            [
                Vcalendar::FREQ     => Vcalendar::DAILY,
                Vcalendar::COUNT    => 10,
                Vcalendar::INTERVAL => 1,
                Vcalendar::BYMONTH  => [ 2, 4, 6, 8, 10, 12 ],
                Vcalendar::BYDAY    => [ Vcalendar::DAY => Vcalendar::TH ],
                Vcalendar::BYSETPOS => [ -3, -2 ],
                $DATASET            => $dataSetNo++,
            ],
            [
                20190214, 20190221,
                20190411, 20190418,
                20190613, 20190620,
                20190815, 20190822,
                20191017
            ],
            0.0,
        ];

        return $dataArr;
    }

    /**
     * Testing recur2date Daily, same recur as recurDaily2Test BUT with BYSETPOS
     *
     * @test
     * @dataProvider recurDaily2TestProvider
     * @param int      $case
     * @param DateTime $start
     * @param array|DateTime $end
     * @param array    $recur
     * @param array    $expects
     * @param float    $prepTime
     * @throws Exception
     */
    public function recurDaily2Test(
        $case,
        DateTime $start,
        $end,
        array $recur,
        array $expects,
        $prepTime
    ) {
        $saveStartDate = clone $start;

        if( ! isset( $recur[Vcalendar::INTERVAL] )) {
            $recur[Vcalendar::INTERVAL] = 1;
        }
        $strCase   = str_pad( $case, 12 );
        $recurDisp = str_replace( [PHP_EOL, ' ' ], '', var_export( $recur, true ));
        if( ! RecurFactory2::isRecurDaily2( $recur )) {
            echo $strCase . ' NO isRecurDaily1' . $recurDisp . PHP_EOL;
            $this->assertTrue( false );
            return;
        } // end if
        $time     = microtime( true );
        $resultX  = RecurFactory2::recurDaily2( $recur, $start, clone $start, $end );
        $execTime = microtime( true ) - $time;
        echo $strCase . 'rcrDaily2  time:' . number_format( $execTime, 6 ) . ' : ' .
            implode( ' - ', array_keys( $resultX )) . PHP_EOL; // test ###
        echo $recurDisp . PHP_EOL; // test ###
        $this->assertEquals(
            $expects,
            array_keys( $resultX ),
            sprintf( self::$ERRFMT, __FUNCTION__, $case . '-41',
                $saveStartDate->format( 'Ymd' ),
                $end->format( 'Ymd' ),
                var_export( $recur, true )
            )
        );
    }
}
