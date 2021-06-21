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

use PHPUnit\Framework\TestCase;
use Kigkonsult\Icalcreator\Util\RecurFactory;
use DateTime;
use Exception;

/**
 * class RecurTest, testing selectComponents
 *
 * @since  2.27.20 - 2019-05-20
 */
abstract class RecurBaseTest extends TestCase
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
        RecurFactory::recur2date( $result2, $recur, $start, ( clone $start ), $end );
        $execTime2 = microtime( true ) - $time2;

        self::$totResultTime += $execTime1;
        self::$totResultTime += $execTime2;
        self::$totExpectTime += $prepTime;

        $strCase = str_pad( $case, 12 );
        echo PHP_EOL .  // test ###
            $strCase . 'resultOld  time:' . number_format( $execTime1, 6 ) . ' : ' . implode( ' - ', array_keys( $result1 )) . PHP_EOL; // test ###
        echo   // test ###
            $strCase . 'resultNew  time:' . number_format( $execTime2, 6 ) . ' : ' . implode( ' - ', array_keys( $result2 )) . PHP_EOL; // test ###
        echo
            $strCase . 'expects    time:' . number_format( $prepTime, 6 ) . ' : ' . implode( ' - ', $expects  ) . PHP_EOL; // test ###

        $recurDisp = str_replace( [PHP_EOL, ' ' ], '', var_export( $recur, true ));
        $result = array_keys( $result1 );
        $this->assertEquals(
            $expects,
            $result,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . ' test #1',
                $saveStartDate->format( 'Ymd' ),
                $end->format( 'Ymd' ),
                PHP_EOL . $recurDisp .
                PHP_EOL . 'got : ' . implode( ',', $result ) .
                PHP_EOL . 'exp : ' . implode( ',', $expects )
            )
        );
        $result = array_keys( $result2 );
        $this->assertEquals(
            $expects,
            $result,
            sprintf(
                self::$ERRFMT,
                __FUNCTION__,
                $case . ' test #2',
                $saveStartDate->format( 'Ymd' ),
                $end->format( 'Ymd' ),
                $recurDisp .
                PHP_EOL . 'exp : ' . implode( ',', $expects ) .
                PHP_EOL . 'got : ' . implode( ',', $result )
            )
        );
        return $result1;
    }
}
