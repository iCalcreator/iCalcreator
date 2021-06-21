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
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Exception;

/**
 * class Exception1Test
 *
 * Testing exceptions in DateTimeFactory, DateTimeZoneFactory and DateIntervalFactory
 *
 * @since  2.27.14 - 2019-02-27
 */
class Exception1Test extends TestCase
{
    /**
     * DateTimeFactoryFactoryTest provider
     */
    public function DateTimeFactoryFactoryTestProvider()
    {
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
    public function DateTimeFactoryFactoryTest( $case, $dateTimeString, $timezoneString )
    {
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
    public function DateTimeFactoryGetYmdFromTimestampTestProvider()
    {
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
    public function getYmdFromTimestampTest( $case, $dateTimeString, $timezoneString )
    {
        $ok = false;
        try {
            $dateTime = DateTimeFactory::factory( $dateTimeString, $timezoneString )
                                       ->format( DateTimeFactory::$Ymd );
        }
        catch ( Exception $e ) {
            $ok = true;
        }
        $this->assertTrue( $ok, 'error in case #' . $case );
    }

    /**
     * DateTimeFactorySetDateTest provider
     */
    public function DateTimeFactorySetDateTestProvider()
    {
        $dataArr = [];

        $dataArr[] = [
            1,
            DateTimeFactory::factory( 'now' ),
            [ Vcalendar::TZID => 'invalid/timezone' ]
        ];

        $dataArr[] = [
            19,
            '011201010101',
            [ Vcalendar::TZID => 'invalid/timezone']
        ];

        $dataArr[] = [
            18,
            [ 'timestamp' => 'Papegojan Ragatha' ],
            []
        ];

        $dataArr[] = [
            19,
            [
                'timestamp'         => '1',
                RecurFactory::$LCtz => 'invalid/timezone'
            ],
            []
        ];

        $dataArr[] = [
            20,
            [
                'timestamp'         => '1',
                RecurFactory::$LCtz => Vcalendar::UTC
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
    public function DateTimeFactorySetDateTest(  $case,  $value,  $params )
    {
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
