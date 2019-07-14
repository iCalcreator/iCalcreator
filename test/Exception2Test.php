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
            2,
            [
                [
                    Util::$LCYEAR => -1
                ]
            ],
            [ Vcalendar::VALUE => Vcalendar::DATE ]
        ];

        $dataArr[] = [
            3,
            [
                [
                    Util::$LCYEAR => -1
                ]
            ],
            []
        ];

        $dataArr[] = [
            4,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 12
                ]
            ],
            [ Vcalendar::VALUE => Vcalendar::DATE ]
        ];

        $dataArr[] = [
            5,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 12
                ]
            ],
            []
        ];

        $dataArr[] = [
            6,
            [
                [
                    Util::$LCYEAR => -1, Util::$LCMONTH => 12, Util::$LCDAY => 1
                ]
            ],
            [ Vcalendar::VALUE => Vcalendar::DATE ]
        ];

        $dataArr[] = [
            7,
            [
                [
                    Util::$LCYEAR => -1, Util::$LCMONTH => 12, Util::$LCDAY => 1
                ]
            ],
            []
        ];

        $dataArr[] = [
            8,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 13, Util::$LCDAY => 1
                ]
            ],
            [ Vcalendar::VALUE => Vcalendar::DATE ]
        ];

        $dataArr[] = [
            9,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 13, Util::$LCDAY => 1
                ]
            ],
            []
        ];

        $dataArr[] = [
            10,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 33
                ]
            ],
            [ Vcalendar::VALUE => Vcalendar::DATE ]
        ];


        $dataArr[] = [
            11,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 33
                ]
            ],
            []
        ];

        $dataArr[] = [
            12,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 3
                ]
            ],
            [ Vcalendar::TZID => 'invalid/timezone' ]
        ];

        $dataArr[] = [
            13,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
                    Util::$LCHOUR => 25, Util::$LCMIN => 1, Util::$LCSEC => 1
                ]
            ],
            []
        ];

        $dataArr[] = [
            14,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
                    Util::$LCHOUR => 1, Util::$LCMIN => 61, Util::$LCSEC => 1
                ]
            ],
            []
        ];

        $dataArr[] = [
            15,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
                    Util::$LCHOUR => 1, Util::$LCMIN => 1, Util::$LCSEC => 61
                ]
            ],
            []
        ];

        $dataArr[] = [
            16,
            [
                [
                    Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
                    Util::$LCHOUR => 1, Util::$LCMIN => 1, Util::$LCSEC => 1
                ]
            ],
            [ Vcalendar::TZID => 'invalid/timezone']
        ];

        $dataArr[] = [
            17,
            [
                [
                    -1
                ]
            ],
            []
        ];

        $dataArr[] = [
            18,
            [
                [
                    1,  12
                ]
            ],
            []
        ];

        $dataArr[] = [
            19,
            [
                [
                    -1, 13, 1
                ]
            ],
            []
        ];

        $dataArr[] = [
            20,
            [
                [
                    1, 13, 1
                ]
            ],
            []
        ];

        $dataArr[] = [
            21,
            [
                [
                    1, 12, 33
                ]
            ],
            []
        ];

        $dataArr[] = [
            22,
            [
                [
                    1, 12, 3
                ]
            ],
            [ Vcalendar::TZID => 'invalid/timezone']
        ];

        $dataArr[] = [
            23,
            [
                [
                    1, 12, 1, 25, 1, 1
                ]
            ],
            []
        ];

        $dataArr[] = [
            24,
            [
                [
                    1, 12, 1, 1, 61, 1
                ]
            ],
            []
        ];

        $dataArr[] = [
            25,
            [
                [
                    1, 12, 1, 1, 1, 61
                ]
            ],
            []
        ];

        $dataArr[] = [
            26,
            [
                [
                    1, 12, 1, 1, 1, 1
                ]
            ],
            [ Vcalendar::TZID => 'invalid/timezone']
        ];

        $dataArr[] = [
            27,
            [
                [
                    Util::$LCTIMESTAMP => 'Papegojan Ragatha'
                ]
            ],
            []
        ];

        $dataArr[] = [
            28,
            [
                [
                    Util::$LCTIMESTAMP => '1',
                    Util::$LCtz        => 'invalid/timezone'
                ]
            ],
            []
        ];

        $dataArr[] = [
            29,
            [
                [
                    Util::$LCTIMESTAMP => '1',
                    Util::$LCtz        => Vcalendar::UTC
                ]
            ],
            [ Vcalendar::TZID => 'invalid/timezone']
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
    public function RexdateFactoryPrepInputExdateTest(
        $case,
        $value,
        $params
    ) {
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
    public function RexdateFactoryprepInputRdateTest(
        $case,
        $value,
        $params
    ) {
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