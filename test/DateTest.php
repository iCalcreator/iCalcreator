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

use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class DateTest, testing VALUE DATE, also empty value, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
 *
 * @since  2.27.14 - 2019-01-24
 */
class DateTest extends DtBase
{
    /**
     * set and restore local timezone from const
     */
    public static $oldTimeZone = null;

    public static function setUpBeforeClass()
    {
        self::$oldTimeZone = date_default_timezone_get();
        date_default_timezone_set( LTZ );
    }

    public static function tearDownAfterClass()
    {
        date_default_timezone_set( self::$oldTimeZone );
    }

    /**
     * testDATE provider
     *
     * @throws Exception
     */
    public function DATEProvider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dataArr[] = [ // test set #100 empty
            100,
            null,
            null,
            [
                Util::$LCvalue => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmd );
        $dataArr[] = [ // test set #101 DateTime
            101,
            $dateTime,
            [ Vcalendar::VALUE => Vcalendar::DATE ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::VALUE => Vcalendar::DATE ]
            ],
            $this->getDateTimeAsCreateShortString( $dateTime )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis );
        $dataArr[] = [ // test set #102 DateTime
            102,
            $dateTime,
            [ Vcalendar::VALUE => Vcalendar::DATE ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::VALUE => Vcalendar::DATE ]
            ],
            $this->getDateTimeAsCreateShortString( $dateTime )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis . ' ' . LTZ );
        $dataArr[] = [ // test set #103 DateTime
            103,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ)
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd );
        $dataArr[] = [ // test set #114 string
            114,
            DATEYmd,
            [ Vcalendar::VALUE => Vcalendar::DATE ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::VALUE => Vcalendar::DATE ]
            ],
            $this->getDateTimeAsCreateShortString( $dateTime )
        ];


        $dateTime = DateTimeFactory::factory( DATEYmdTHis );
        $dataArr[] = [ // test set #115 string
            115,
            DATEYmdTHis,
            [ Vcalendar::VALUE => Vcalendar::DATE ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::VALUE => Vcalendar::DATE ]
            ],
            $this->getDateTimeAsCreateShortString( $dateTime )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $dataArr[] = [ // test set #116 string
            116,
            DATEYmdTHis,
            [],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $dataArr[] = [ // test set #117 string
            117,
            DATEYmdTHis . 'Z',
            [ Vcalendar::VALUE => Vcalendar::DATE ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams =>
                    [ Vcalendar::VALUE => Vcalendar::DATE ]
            ],
            $this->getDateTimeAsCreateShortString( $dateTime )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $dataArr[] = [ // test set #118 string
            118,
            DATEYmdTHis . 'Z',
            [],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( '20160305' );
        for( $x = 1; $x < 10; $x++ ) {
            $dataArr[] = [ // test set #101 DateTime
                200 + $x,
                $dateTime->format( 'Ymd' ),
                [ Vcalendar::VALUE => Vcalendar::DATE ],
                [
                    Util::$LCvalue  => clone $dateTime,
                    Util::$LCparams => [ Vcalendar::VALUE => Vcalendar::DATE ]
                ],
                $this->getDateTimeAsCreateShortString( $dateTime )
            ];
            $dateTime->modify( '-1 day ' );
        } // end for

        return $dataArr;
    }

    /**
     * Testing VALUE DATE, also empty value, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DATEProvider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testDATE(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    )
    {
        static $compsProps = [
            Vcalendar::VEVENT => [
                Vcalendar::DTSTART,
                Vcalendar::DTEND,
                Vcalendar::RECURRENCE_ID,
                Vcalendar::EXDATE,
                Vcalendar::RDATE
            ],
            Vcalendar::VTODO    => [ Vcalendar::DTSTART, Vcalendar::DUE, Vcalendar::RECURRENCE_ID ],
            Vcalendar::VJOURNAL => [ Vcalendar::DTSTART, Vcalendar::RECURRENCE_ID ],
        ];
        static $compsProps2 = [
            Vcalendar::VEVENT => [
                Vcalendar::EXDATE,
                Vcalendar::RDATE
            ]
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        $this->theTestMethod1b( $case, $compsProps2, $value, $params, $expectedGet, $expectedString );
    }
}
