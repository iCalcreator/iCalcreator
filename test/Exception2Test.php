<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
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
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\RexdateFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Exception;

/**
 * class Exception2Test
 *
 * Testing exception in RexdateFactory
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.14 - 2019-02-26
 */
class Exception2Test extends TestCase
{


    /**
     * RexdateFactoryPrepInputExdateTest provider
     */
    public function Provider1() {

        $dataArr = [];

        $dataArr[] = [
            1,
            [
                [
                    DateTimeFactory::factory( 'now' )
                ]
            ],
            [ Vcalendar::TZID => 'invalid/timezone' ]
        ];

        $dataArr[] = [
            12,
            [
                [
                    '011201250101'
                ]
            ],
            [ Vcalendar::TZID => 'invalid/timezone' ]
        ];

        $dataArr[] = [
            13,
            [
                '011201250101'
            ],
            []
        ];

        $dataArr[] = [
            30,
            [
                [
                    'Kalle Stropp'
                ]
            ],
            []
        ];

        return $dataArr;
    }

    /**
     * Testing RexdateFactory::prepInputExdate
     *
     * @test
     * @dataProvider Provider1
     * @param int    $case
     * @param mixed  $value
     * @param array  $params
     */
    public function RexdateFactoryPrepInputExdateTest( $case, $value, $params ) {
        $ok = false;
        try {
            $result = RexdateFactory::prepInputExdate( $value, $params );
        }
        catch ( Exception $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in case #' . $case );
    }


    /**
     * Testing RexdateFactory::prepInputRdate
     *
     * @test
     * @dataProvider Provider1
     * @param int    $case
     * @param mixed  $value
     * @param array  $params
     */
    public function RexdateFactoryprepInputRdateTest( $case, $value, $params ) {
        $ok = false;
        try {
            $result = RexdateFactory::prepInputRdate( $value, $params );
        }
        catch( Exception $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in case #' . $case );
    }


}
