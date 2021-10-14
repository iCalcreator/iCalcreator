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
use Kigkonsult\Icalcreator\Util\RecurFactory2;
use DateTime;
use Exception;

/**
 * class RecurTest, testing selectComponents
 *
 * @since  2.27.20 - 2019-05-20
 */
class RecurYearTest extends RecurBaseTest
{
    /**
     * recurYearlyTest111x provider
     *
     * @return array
     * @throws Exception
     */
    public function recurYearlyTest111xProvider() : array
    {
        $dataArr = [];
        $dataSetNo = 0;
        $DATASET = 'DATASET';

        $interval = 1;
        $count = 10;
        for( $ix = 111; $ix <= 112; $ix++ ) {
            $time = microtime( true );
            $start = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
            $year = (int)$start->format( 'Y' );
            $month = (int)$start->format( 'm' );
            $day = (int)$start->format( 'd' );
            $end = ( clone $start )->modify( '20 years' );
            $expects = [];
            $x = 1;
            while( $x < $count ) {
                $year += $interval;
                $Ymd = sprintf( '%04d%02d%02d', $year, $month, $day );
                $expects[] = $Ymd;
                ++$x;
            }
            $execTime = microtime( true ) - $time;
            $dataArr[] = [
                $ix . '-' . $interval,
                $start,
                $end,
                [
                    IcalInterface::FREQ => IcalInterface::YEARLY,
                    IcalInterface::INTERVAL => $interval,
                    IcalInterface::COUNT => $count,
                    $DATASET => $dataSetNo++
                ],
                $expects,
                $execTime
            ];
            ++$interval;
        }
        return $dataArr;
    }

    /**
     * Testing recur2date Yearly simple
     *
     * @test
     * @dataProvider recurYearlyTest111xProvider
     * @param string $case
     * @param DateTime $start
     * @param DateTime|array $end
     * @param array    $recur
     * @param array    $expects
     * @param float $prepTime
     * @throws Exception
     */
    public function recurYearlyTest111x(
        string           $case,
        DateTime         $start,
        DateTime | array $end,
        array            $recur,
        array            $expects,
        float            $prepTime
    ) : void
    {
        $this->recurYearlyTest1X( $case, $start, $end, $recur, $expects, $prepTime );
    }

    /**
     * recurYearlyTest23 provider
     *
     * @return array
     * @throws Exception
     */
    public function recurYearlyTest23Provider() : array
    {
        $dataArr   = [];
        $dataSetNo = 0;
        $DATASET   = 'DATASET';

        // rfc example 23 - exact (no interval)
        $dataArr[] = [
            '19-23-0-0-0',
            DateTimeFactory::factory( '19970610T0900', 'Europe/Stockholm' ),
            DateTimeFactory::factory( '20040610T0900', 'Europe/Stockholm' ),
            [
                IcalInterface::FREQ     => IcalInterface::YEARLY,
                IcalInterface::COUNT    => 10,
                IcalInterface::BYMONTH  => [ 6, 7 ],
                $DATASET                => $dataSetNo,
                'MRANGE'                => [ 6, 7 ]
            ],
            [ 19970710, 19980610, 19980710, 19990610, 19990710, 20000610, 20000710, 20010610, 20010710 ],
            0,0,
        ];

        // rfc example 23 - with interval for-loop '19-23-...'
        $count    = 10;
        $mRange   = [];
        for( $ix1 = 1; $ix1 < 5; $ix1++ ) {
            $interval = 0;
            for( $ix2 = 1; $ix2 <= 10; $ix2++ ) {
                ++$interval;
                $mRange[] = array_rand( array_flip( range( 1, 12 )));
                sort( $mRange );
                $mRange   = array_unique( $mRange, SORT_NUMERIC );
                $time     = microtime( true );
//              $start    = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
                $start    = DateTimeFactory::factory( '19970610T0900', 'Europe/Stockholm' );
                $startYmd = $start->format( 'Ymd' );
                $end      = ( clone $start )->setDate(
                    ((int) $start->format( 'Y' ) + ( 10 + $interval )),
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
                    if( $currYear !== (int) $wDate->format( 'Y' )) {
                        $wDate   = $wDate->setDate(
                            ((int) $wDate->format( 'Y' ) + $interval ),
                            1,
                            (int) $wDate->format( 'd' )
                        );
                        $currYear = (int) $wDate->format( 'Y' );
                    }
                    if( $endYmd < $wDate->format( 'Ymd' )) {
                        break;
                    }
                    if( $startYmd < $wDate->format( 'Ymd' )) {
                        if( in_array((int) $wDate->format( 'm' ), $mRange ) ) {
                            $expects[] = $wDate->format( 'Ymd' );
                            ++$x;
                        }
                        if( 12 === (int) $wDate->format( 'm' )) {
                            $currYear = -1;
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
                    '19-23-' . $ix1 . '-' . $ix2 . '-' . $interval,
                    $start,
                    $end,
                    [
                        IcalInterface::FREQ     => IcalInterface::YEARLY,
                        IcalInterface::INTERVAL => $interval,
                        IcalInterface::COUNT    => $count,
                        IcalInterface::BYMONTH  => $mRange,
                        $DATASET            => $dataSetNo++,
                        'MRANGE'            => implode( ',', $mRange )
                    ],
                    $expects,
                    $execTime,
                ];
            } // end for... $x2
        } // end for... $x1

        return $dataArr;
    }

    /**
     * Testing recur2date Yearly , rfc example 23 - with interval for-loop '19-23-...'
     *
     * Test RecurFactory::recurYearly1
     *
     * @test
     * @dataProvider recurYearlyTest23Provider
     * @param string $case
     * @param DateTime $start
     * @param DateTime|array $end
     * @param array    $recur
     * @param array    $expects
     * @param float $prepTime
     * @throws Exception
     */
    public function recurYearlyTest23(
        string           $case,
        DateTime         $start,
        DateTime | array $end,
        array            $recur,
        array            $expects,
        float            $prepTime
    ) : void
    {
        $this->recurYearlyTest1X( $case, $start, $end, $recur, $expects, $prepTime );
    }

    /**
     * recurYearlyTest23e provider, rfc example 23 -Extended, both byMonth and byMonthDay
     *
     * @throws Exception
     */
    public function recurYearlyTest23eProvider() : array
    {
        $dataArr   = [];
        $dataSetNo = 0;
        $DATASET   = 'DATASET';

        // rfc example 23 -Extended, both byMonth and byMonthDay
        $start    = DateTimeFactory::factory( '20190101T0900', 'Europe/Stockholm' );
        $end      = ( clone $start )->modify('+10 years' );
        $count    = 20;
        $mRange   = [ 1 ]; // month
        $dRange   = [];    // days in month
        $baseDays = [ 4, 8, 12, 16, -16, -12, -8, -4 ];
        for( $ix1 = 1; $ix1 < 5; $ix1++ ) {
            $interval = 1;
            for( $ix2 = 1; $ix2 <= 2; $ix2++ ) {
                $mRange[] = array_rand( array_flip( range( 4, 12 )));
                sort( $mRange );
                $mRange   = array_values( array_unique( $mRange ));
                $dKey     = array_rand( $baseDays );
                $dRange[] = $baseDays[$dKey];
                sort( $dRange );
                $dRange   = array_values( array_unique( $dRange ));
                $time     = microtime( true );
                $startYmd = $start->format( 'Ymd' );
                $startYm  = $start->format( 'Ym' );
                $endYmd   = $end->format( 'Ymd' );
                $expects  = [];
                $x        = 1;
                $wDate    = clone $start;
                $currYear = $year = (int) $wDate->format( 'Y' );
                $mx       = 0;
                $month    = $mRange[$mx];
                $day      = (int) $wDate->format( 'd' );
                $wDate->setDate( $year, $month, $day );
                $currMonth = $month;
                while(( $x < $count ) && ( $endYmd > $wDate->format( 'Ymd' ))) {
//                    if( 4000 < ++$y ) break;
                    if( $currYear !== (int) $wDate->format( 'Y' )) {
                        $year    += $interval;
                        $currYear = $year;
                        $mx        = 0;
                        $currMonth = $month = $mRange[$mx];
                    } // end if
                    if( $currMonth !== $month ) {
                        ++$mx;
                        if( ! isset( $mRange[$mx] )) {
                            $currYear  = -1;
                            continue;
                        }
                        $currMonth = $month = $mRange[$mx];
                    } // end if
                    $wDate->setDate( $year, $month, $day );
                    if( $endYmd < $wDate->format( 'Ymd' )) {
                        break;
                    }
                    if( $startYm > $wDate->format( 'Ym' )) {
                        $currMonth    = -1;
                        continue;
                    }
                    if( in_array( $month, $mRange, true ) ) { // bort ??
                        $xDate = clone $wDate;
                        foreach( RecurFactory2::getMonthDaysFromByMonthDayList(
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
                            if( $startYmd >= $Ymd ) {
                                continue;
                            }
                            if( $endYmd < $Ymd ) {
                                break 2;
                            }
                            $expects[] = $Ymd;
                            ++$x;
                        } // end foreach
                    } // end if ... in mRange
                    $currMonth = -1;
                } // end while
                $execTime  = microtime( true ) - $time;
                $dataArr[] = [
                    '19-23e' . $ix1 . '-' . $ix2 . '-' . $interval,
                    $start,
                    $end,
                    [
                        IcalInterface::FREQ       => IcalInterface::YEARLY,
                        IcalInterface::INTERVAL   => $interval,
                        IcalInterface::COUNT      => $count,
                        IcalInterface::BYMONTH    => $mRange,
                        IcalInterface::BYMONTHDAY => $dRange,
                        $DATASET              => $dataSetNo++,
                        'MRANGE'              => implode( ',', $mRange )
                    ],
                    $expects,
                    $execTime,
                ];
            } // end for... $x2
        } // end for... $x1

        return $dataArr;
    }

    /**
     * Testing recur2date Yearly , rfc example 23 -Extended, both byMonth and byMonthDay
     *
     * @test
     * @dataProvider recurYearlyTest23eProvider
     * @param string $case
     * @param DateTime $start
     * @param DateTime|array $end
     * @param array    $recur
     * @param array    $expects
     * @param float $prepTime
     * @throws Exception
     */
    public function recurYearlyTest23e(
        string           $case,
        DateTime         $start,
        DateTime | array $end,
        array            $recur,
        array            $expects,
        float            $prepTime
    ) : void
    {
        $this->recurYearlyTest1X( $case, $start, $end, $recur, $expects, $prepTime );
    }

    /**
     * recurYearlyTest23l provider, rfc example 23 changed date and limited by INTERVAL 2 and byMonthDay [ -20, -10 ]
     *
     * @throws Exception
     */
    public function recurYearlyTest23lProvider() : array
    {
        $dataArr   = [];
        $dataSetNo = 0;
        $DATASET   = 'DATASET';

        // rfc example 23 changed date and limited by INTERVAL 2 and byMonthDay [ -20, -10 ]
        $start    = DateTimeFactory::factory( '20200801T0900', 'Europe/Stockholm' );
        $end      = ( clone $start )->modify('+12 years' );
        $dataArr[] = [
            '19-23l',
            $start,
            $end,
            [
                IcalInterface::FREQ       => IcalInterface::YEARLY,
                IcalInterface::INTERVAL   => 2,
                IcalInterface::COUNT      => 10,
                IcalInterface::BYMONTHDAY => [ -15, -2 ],
                $DATASET              => $dataSetNo++,
            ],
            [ 20200817, 20200830, 20220817, 20220830, 20240817, 20240830, 20260817, 20260830, 20280817 ],
            0.0,
        ];

        return $dataArr;
    }

    /**
     * Testing recur2date Yearly, rfc example 23 changed date and limited by INTERVAL 2 and byMonthDay [ -20, -10 ]
     *
     * @test
     * @dataProvider recurYearlyTest23lProvider
     * @param string $case
     * @param DateTime $start
     * @param DateTime|array $end
     * @param array    $recur
     * @param array    $expects
     * @param float $prepTime
     * @throws Exception
     */
    public function recurYearlyTest23l(
        string           $case,
        DateTime         $start,
        DateTime | array $end,
        array            $recur,
        array            $expects,
        float            $prepTime
    ) : void
    {
        $this->recurYearlyTest1X( $case, $start, $end, $recur, $expects, $prepTime );
    }

    /**
     * Testing recur2date Yearly
     *
     * @param string $case
     * @param DateTime $start
     * @param DateTime|array $end
     * @param array    $recur
     * @param array    $expects
     * @param float $prepTime
     * @throws Exception
     */
    public function recurYearlyTest1X(
        string           $case,
        DateTime         $start,
        DateTime | array $end,
        array            $recur,
        array            $expects,
        float            $prepTime
    ) : void
    {
        $saveStartDate = clone $start;

        if( ! isset( $recur[IcalInterface::INTERVAL] )) {
            $recur[IcalInterface::INTERVAL] = 1;
        }

        error_log('' ); // test ###
        error_log( __FUNCTION__ . ' start ' . $case ); // test ###

        $strCase   = str_pad( $case, 12 );

        if( '19-23l' === $case ) {
            $result = array_flip( $expects );
            echo $strCase . 'expects                  : ' .
                implode( ' - ', array_keys( $result ) ) . PHP_EOL;        // test ###
        }
        else {
            $result = $this->recur2dateTest( // return old
                $case,
                $start,
                $end,
                $recur,
                $expects,
                $prepTime
            );
        }
        $recurDisp = str_replace( [PHP_EOL, ' ' ], '', var_export( $recur, true ));
        if( ! RecurFactory2::isRecurYearly1( $recur )) {
            echo $strCase . ' NOT isRecurYearly1 ' . $recurDisp . PHP_EOL;
            $this->fail();
        }
        $time     = microtime( true );
        $resultX  = RecurFactory2::recurYearly1( $recur, $start, clone $start, $end );
        $execTime = microtime( true ) - $time;
        echo $strCase . 'year smpl1 time:' . number_format( $execTime, 6 ) . ' : ' .
            implode( ' - ', array_keys( $resultX ) ) . PHP_EOL;        // test ###
        echo $recurDisp . ' start ' . $start->format( 'Ymd' ) . ' end ' . $end->format( 'Ymd' ) . PHP_EOL; // test ###
        $endFormat = is_array( $end )
            ? implode( '-', $end )
            : $end->format( 'Ymd' );
        $this->assertEquals(
            array_keys( $result ),  // exp, old
            array_keys( $resultX ), // new, actual
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . ' old -> new  ',
                $saveStartDate->format( 'Ymd' ),
                $endFormat,
                $recurDisp
            )
        );
    }

    /**
     * recurYearlyTest2X provider
     *
     * @throws Exception
     */
    public function recurYearlyTest2XProvider() : array
    {
        $dataArr   = [];
        $dataSetNo = 0;
        $DATASET   = 'DATASET';

        // yearly in june, third TU/WE/TH in month, forever
        $start   = DateTimeFactory::factory( '20200101T090000', 'Europe/Stockholm');
        $wDate   = clone $start;
        $dataArr[] = [
            '2001',
            $start,
            $wDate->modify(  10 . ' year' ), // end
            [
                IcalInterface::FREQ      => IcalInterface::YEARLY,
                IcalInterface::BYMONTH   => 6,
                IcalInterface::BYDAY     => [
                    [ IcalInterface::DAY => IcalInterface::TU ],
                    [ IcalInterface::DAY => IcalInterface::WE ],
                    [ IcalInterface::DAY => IcalInterface::TH ],
                ],
                IcalInterface::BYSETPOS  => -3,
                $DATASET             => $dataSetNo++
            ],
            [ 20200624,20210624,20220628,20230627,20240625,20250624,20260624,20270624,20280627,20290626 ]
        ];

        // neotsn Thanksgiving event - 4th Thursday of every November - Yearly - same in recurMonthly2Test by MONTHLY
        $start   = DateTimeFactory::factory( '20201126T113000', 'America/Chicago');
        $wDate   = clone $start;
        $dataArr[] = [
            'neotsn',
            $start,
            $wDate->modify(  10 . ' year' ), // end
            [
                IcalInterface::FREQ      => IcalInterface::YEARLY,
                IcalInterface::BYMONTH   => 11,
                IcalInterface::BYDAY     => [
                    [ IcalInterface::DAY => IcalInterface::TH ],
                ],
                IcalInterface::BYSETPOS  => 4,
                $DATASET             => $dataSetNo++
            ],
            [ 20211125,20221124,20231123,20241128,20251127,20261126,20271125,20281123,20291122 ]
        ];

        return $dataArr;
    }

    /**
     * Testing recurMonthlyYearly3 - YEARLY + BYMONTH + BYDAY
     *
     * @test
     * @dataProvider recurYearlyTest2XProvider
     * @param string   $case
     * @param DateTime $start
     * @param DateTime|array $end
     * @param array    $recur
     * @param array    $expects
     * @throws Exception
     */
    public function recurYearly2XTest(
        string           $case,
        DateTime         $start,
        DateTime | array $end,
        array            $recur,
        array            $expects
    ) : void
    {
        $saveStartDate = clone $start;

        if( ! isset( $recur[IcalInterface::INTERVAL] )) {
            $recur[IcalInterface::INTERVAL] = 1;
        }
        $strCase   = str_pad( $case, 12 );
        $recurDisp = str_replace( [PHP_EOL, ' ' ], '', var_export( $recur, true ));
        if( ! RecurFactory2::isRecurYearly2( $recur )) {
            echo $strCase . ' NOT isRecurYearly2 ' . $recurDisp . PHP_EOL;
            $this->fail();
        } // end if
        $time     = microtime( true );
        $resultX  = RecurFactory2::recurMonthlyYearly3( $recur, $start, clone $start, $end );
        $execTime = microtime( true ) - $time;
        echo $strCase . 'year smpl2 time:' . number_format( $execTime, 6 ) . ' : ' .
            implode( ' - ', array_keys( $resultX )) . PHP_EOL; // test ###
        echo $recurDisp . PHP_EOL; // test ###
        $this->assertEquals(
            $expects,
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
