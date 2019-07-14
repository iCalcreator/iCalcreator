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
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Exception;

/**
 * class Exception1Test
 *
 * Testing exceptions in DateTimeFactory, DateTimeZoneFactory and DateIntervalFactory
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.14 - 2019-02-27
 */
class Exception1Test extends TestCase
{
    /**
     * DateTimeFactoryFactoryTest provider
     */
    public function DateTimeFactoryFactoryTestProvider() {

        $dataArr = [];

        $dataArr[] = [
            1,
            '@1',
            'invalid/timezone',
        ];

        $dataArr[] = [
            1,
            '@1111111111111111111111111111111111111111111111111111111111111111111111111',
            'invalid/timezone',
        ];

        $dataArr[] = [
            2,
            '@1',
            chr(0),
        ];

        $dataArr[] = [
            3,
            '@1',
            'invalid/timezone',
        ];

        $dataArr[] = [
            4,
            'invalid timezonestring',
            'invalid/timezone',
        ];

        $dataArr[] = [
            5,
            'now',
            'invalid/timezone',
        ];

        return $dataArr;
    }

    /**
     * Testing DateTimeFactory::factory
     *
     * @test
     * @dataProvider DateTimeFactoryFactoryTestProvider
     * @param int    $case
     * @param string  $dateTimeString
     * @param string  $timezoneString
     */
    public function DateTimeFactoryFactoryTest(
        $case,
        $dateTimeString,
        $timezoneString
    ) {
        $ok = false;
        try {
            $dateTime = DateTimeFactory::factory( $dateTimeString, $timezoneString );
        }
        catch ( Exception $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in case #' . $case );
    }

    /**
     * DateTimeFactoryGetYmdFromTimestampTest provider
     */
    public function DateTimeFactoryGetYmdFromTimestampTestProvider() {

        $dataArr = [];

        $dataArr[] = [
            1,
            '1111111111111111111111111111111111111111111111111111111111111111111111111',
            'invalid/timezone',
        ];

        $dataArr[] = [
            2,
            '1',
            'invalid/timezone',
        ];

        $dataArr[] = [
            3,
            DateTimeFactory::factory( 'now' )->getTimestamp(),
            'invalid/timezone',
        ];


        return $dataArr;
    }

    /**
     * Testing DateTimeFactory::getYmdFromTimestamp
     *
     * @test
     * @dataProvider DateTimeFactoryGetYmdFromTimestampTestProvider
     * @param int    $case
     * @param string  $dateTimeString
     * @param string  $timezoneString
     */
    public function getYmdFromTimestampTest(
        $case,
        $dateTimeString,
        $timezoneString
    ) {
        $ok = false;
        try {
            $dateTime = DateTimeFactory::getYmdFromTimestamp( $dateTimeString, $timezoneString );
        }
        catch ( Exception $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in case #' . $case );
    }

    /**
     * DateTimeFactoryassertArrayDateTest provider
     */
    public function DateTimeFactoryAssertArrayDateTestProvider() {

        $dataArr = [];

        $dataArr[] = [
            1,
            [ 'grodan Boll'],
            true
        ];

        $dataArr[] = [
            2,
            [ Util::$LCYEAR => -1 ],
            true
        ];

        $dataArr[] = [
            3,
            [ Util::$LCYEAR => -1 ],
            false
        ];

        $dataArr[] = [
            4,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12 ],
            true
        ];

        $dataArr[] = [
            5,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12 ],
            false
        ];

        $dataArr[] = [
            6,
            [ Util::$LCYEAR => -1, Util::$LCMONTH => 12, Util::$LCDAY => 1 ],
            true
        ];

        $dataArr[] = [
            7,
            [ Util::$LCYEAR => -1, Util::$LCMONTH => 12, Util::$LCDAY => 1 ],
            false
        ];

        $dataArr[] = [
            8,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 13, Util::$LCDAY => 1 ],
            true
        ];

        $dataArr[] = [
            9,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 13, Util::$LCDAY => 1 ],
            false
        ];

        $dataArr[] = [
            10,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 33 ],
            true
        ];

        $dataArr[] = [
            11,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 33 ],
            false
        ];

        $dataArr[] = [
            12,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
              Util::$LCHOUR => 25, Util::$LCMIN => 1, Util::$LCSEC => 1 ],
            false
        ];

        $dataArr[] = [
            13,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
              Util::$LCHOUR => 1, Util::$LCMIN => 61, Util::$LCSEC => 1 ],
            false
        ];

        $dataArr[] = [
            14,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
              Util::$LCHOUR => 1, Util::$LCMIN => 1, Util::$LCSEC => 61 ],
            false
        ];

        $dataArr[] = [
            15,
            [ -1 ],
            true
        ];

        $dataArr[] = [
            16,
            [ -1 ],
            false
        ];

        $dataArr[] = [
            17,
            [ 1,  12 ],
            true
        ];

        $dataArr[] = [
            18,
            [ 1,  12 ],
            false
        ];

        $dataArr[] = [
            19,
            [ -1, 13, 1 ],
            true
        ];

        $dataArr[] = [
            20,
            [ -1, 13, 1 ],
            false
        ];

        $dataArr[] = [
            21,
            [ 1, 13, 1 ],
            true
        ];

        $dataArr[] = [
            22,
            [ 1, 13, 1 ],
            false
        ];

        $dataArr[] = [
            23,
            [ 1, 12, 33 ],
            true
        ];

        $dataArr[] = [
            24,
            [ 1, 12, 33 ],
            false
        ];

        $dataArr[] = [
            25,
            [ 1, 12, 1, 25, 1, 1 ],
            false
        ];

        $dataArr[] = [
            26,
            [ 1, 12, 1, 1, 61, 1 ],
            false
        ];

        $dataArr[] = [
            27,
            [ 1, 12, 1, 1, 1, 61 ],
            false
        ];

        return $dataArr;

    }

    /**
     * Testing DateTimeFactory::assertArrayDate
     *
     * @test
     * @dataProvider DateTimeFactoryAssertArrayDateTestProvider
     * @param int    $case
     * @param mixed  $date
     * @param bool   $isValueDate
     */
    public function assertArrayDateTest(
        $case,
        $date,
        $isValueDate
    ) {
        $ok = false;
        try {
            DateTimeFactory::assertArrayDate( $date, $isValueDate );
        }
        catch ( Exception $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in case #' . $case );
    }

    /**
     * DateTimeFactorySetDateTest provider
     */
    public function DateTimeFactorySetDateTestProvider() {

        $dataArr = [];

        $dataArr[] = [
            1,
            DateTimeFactory::factory( 'now' ),
            [ Vcalendar::TZID => 'invalid/timezone' ]
        ];

        $dataArr[] = [
            2,
            [ Util::$LCYEAR => -1 ],
            []
        ];

        $dataArr[] = [
            3,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12 ],
            []
        ];

        $dataArr[] = [
            4,
            [ Util::$LCYEAR => -1, Util::$LCMONTH => 12, Util::$LCDAY => 1 ],
            []
        ];

        $dataArr[] = [
            5,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 13, Util::$LCDAY => 1 ],
            []
        ];

        $dataArr[] = [
            6,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 33 ],
            []
        ];
        $dataArr[] = [
            7,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 3 ],
            [ Vcalendar::TZID => 'invalid/timezone' ]
        ];

        $dataArr[] = [
            8,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
              Util::$LCHOUR => 25, Util::$LCMIN => 1, Util::$LCSEC => 1 ],
            []
        ];

        $dataArr[] = [
            9,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
              Util::$LCHOUR => 1, Util::$LCMIN => 61, Util::$LCSEC => 1 ],
            []
        ];

        $dataArr[] = [
            10,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
              Util::$LCHOUR => 1, Util::$LCMIN => 1, Util::$LCSEC => 61 ],
            []
        ];

        $dataArr[] = [
            11,
            [ Util::$LCYEAR => 1, Util::$LCMONTH => 12, Util::$LCDAY => 1,
              Util::$LCHOUR => 1, Util::$LCMIN => 1, Util::$LCSEC => 1 ],
            [ Vcalendar::TZID => 'invalid/timezone']
        ];

        $dataArr[] = [
            12,
            [ -1 ],
            []
        ];

        $dataArr[] = [
            13,
            [ 1,  12 ],
            []
        ];

        $dataArr[] = [
            14,
            [ -1, 13, 1 ],
            []
        ];

        $dataArr[] = [
            15,
            [ 1, 13, 1 ],
            []
        ];

        $dataArr[] = [
            16,
            [ 1, 12, 33 ],
            []
        ];

        $dataArr[] = [
            16,
            [ 1, 12, 3 ],
            [ Vcalendar::TZID => 'invalid/timezone']
        ];

        $dataArr[] = [
            17,
            [ 1, 12, 1, 25, 1, 1 ],
            []
        ];

        $dataArr[] = [
            18,
            [ 1, 12, 1, 1, 61, 1 ],
            []
        ];

        $dataArr[] = [
            19,
            [ 1, 12, 1, 1, 1, 61 ],
            []
        ];

        $dataArr[] = [
            19,
            [ 1, 12, 1, 1, 1, 1 ],
            [ Vcalendar::TZID => 'invalid/timezone']
        ];

        $dataArr[] = [
            18,
            [ Util::$LCTIMESTAMP => 'Papegojan Ragatha' ],
            []
        ];

        $dataArr[] = [
            19,
            [
                Util::$LCTIMESTAMP => '1',
                Util::$LCtz        => 'invalid/timezone'
            ],
            []
        ];

        $dataArr[] = [
            20,
            [
                Util::$LCTIMESTAMP => '1',
                Util::$LCtz        => Vcalendar::UTC
            ],
            [ Vcalendar::TZID => 'invalid/timezone']
        ];

        $dataArr[] = [
            21,
            [ 'Kalle Stropp'],
            []
        ];

        return $dataArr;
    }

    /**
     * Testing DateTimeFactory::setDate
     *
     * @test
     * @dataProvider DateTimeFactorySetDateTestProvider
     * @param int    $case
     * @param mixed  $value
     * @param array  $params
     */
    public function DateTimeFactorySetDateTest(
        $case,
        $value,
        $params
    ) {
        $ok = false;
        try {
            $result = DateTimeFactory::setDate( $value, $params );
        }
        catch ( Exception $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in case #' . $case );
    }

}