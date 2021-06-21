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

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Exception;
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class DateTimeTest, testing DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
 *
 * @since  2.29.16 - 2020-01-24
 */
class DateTimeTest extends DtBase
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

    private static $propsCompsProps = [
        Vcalendar::DTSTART => [
            Vcalendar::VEVENT   => [ Vcalendar::DTSTART ],
            Vcalendar::VTODO    => [ Vcalendar::DTSTART ],
            Vcalendar::VJOURNAL => [ Vcalendar::DTSTART ]
        ],
        Vcalendar::DTEND => [
            Vcalendar::VEVENT => [ Vcalendar::DTEND ]
        ],
        Vcalendar::DUE => [
            Vcalendar::VTODO => [ Vcalendar::DUE ],
        ],
        Vcalendar::RECURRENCE_ID => [
            Vcalendar::VEVENT => [ Vcalendar::RECURRENCE_ID ],
            Vcalendar::VTODO  => [ Vcalendar::RECURRENCE_ID ],
        ],
        Vcalendar::EXDATE => [
            Vcalendar::VEVENT => [ Vcalendar::EXDATE ],
        ],
        Vcalendar::RDATE => [
            Vcalendar::VEVENT => [ Vcalendar::RDATE ],
        ],
    ];

    private static $compsProps2 = [
        Vcalendar::VEVENT => [
            Vcalendar::EXDATE,
            Vcalendar::RDATE
        ]
    ];

    /**
     * testDateTime1 provider
     * @throws Exception
     */
    public function DateTime1Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr   = [];

        $dateTime  = new DateTime( DATEYmdTHis . ' ' . LTZ );
        $dataArr[] = [
            1008,
            $dateTime,
            [],
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ)
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            1012,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            1013,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            1014,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC));
        $dataArr[] = [
            1015,
            $dateTime,
            [],
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime  = new DateTimeImmutable(
            DATEYmdTHis,
            DateTimeZoneFactory::factory( Vcalendar::UTC)
        );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            1019,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime = new DateTime(
            DATEYmdTHis,
            DateTimeZoneFactory::factory( Vcalendar::UTC)
        );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            1020,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = new DateTimeImmutable(
            DATEYmdTHis,
            DateTimeZoneFactory::factory( Vcalendar::UTC)
        );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            1021,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . OFFSET );
        $tz        = DateTimeZoneFactory::getTimeZoneNameFromOffset( OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime, $tz );
        $dataArr[] = [
            1022,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . OFFSET );
        $dateTime2 = clone $dateTime;
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, TZ2 );
        $dataArr[] = [
            1026,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . OFFSET );
        $dateTime2 = clone $dateTime;
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            1027,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = new DateTimeImmutable(
            DATEYmdTHis,
            DateTimeZoneFactory::factory( OFFSET )
        );
        $tz = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            1028,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with DateTimeInterface, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DateTime1Provider
     *
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testDateTime1(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    )
    {
        $expectedGet2 = empty( $value )
            ? []
            : [
                Util::$LCvalue  => [ clone $expectedGet[Util::$LCvalue], new DateInterval( 'P1D' ) ],
                Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
            ];

        foreach( self::$propsCompsProps as $compsProps ) {
            $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }

        $this->theTestMethod1b( $case, self::$compsProps2, $value, $params, $expectedGet, $expectedString );
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, 'P1D' ];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case . 'P',
            [
                Vcalendar::VEVENT => [ Vcalendar::RDATE ]
            ],
            $value,
            $params,
            $expectedGet2,
            $expectedString
        );
    }

    /**
     * testDateTime7 provider
     *
     * @throws Exception
     */
    public function DateTime7Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DATEYmdTHis;
        $y  = substr( $dateTime, 0, 4 );
        $m  = substr( $dateTime, 4, 2 );
        $d  = substr( $dateTime, 6, 2);
        $h  = substr( $dateTime, 9, 2 );
        $i  = substr( $dateTime, 11, 2);
        $dateTime2 = new DateTime( DATEYmdTHis . ' ' . Vcalendar::UTC );
        $dataArr[] = [
            7001,
            $dateTime,
            [],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => []
            ],
            ':' . $y . $m . $d . 'T' . $h . $i . '00'
        ];

        $dateTime  = DATEYmdTHis;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7005,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            7006,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DATEYmdTHis;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7007,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = DATEYmdTHis . ' ' . LTZ;
        $dateTime  = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dataArr[] = [
            7008,
            $dateTime,
            [],
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => LTZ, ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ )
        ];

        $dateTime  = DATEYmdTHis . ' ' . LTZ;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7012,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . ' ' . LTZ;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            7013,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DATEYmdTHis . ' ' . LTZ;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7014,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            7015,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            70152,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7019,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            70192,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            7020,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            70202,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7021,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            70212,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7022,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7026,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            7027,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7028,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with full string datetime, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DateTime7Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testDateTime7(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    )
    {
        foreach( self::$propsCompsProps as $comps => $compsProps ) {
            $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }

        $this->theTestMethod1b( $case, self::$compsProps2, $value, $params, $expectedGet, $expectedString );
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, 'P1D' ];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], new DateInterval( 'P1D' ) ],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
        ];
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case . 'P',
            [
                Vcalendar::VEVENT => [ Vcalendar::RDATE ]
            ],
            $value,
            $params,
            $expectedGet,
            $expectedString
        );
    }

    /**
     * testDateTime8 provider
     *
     * @throws Exception
     */
    public function DateTime8Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DATEYmd;
        $y  = substr( $dateTime, 0, 4 );
        $m  = substr( $dateTime, 4, 2 );
        $d  = substr( $dateTime, 6, 2);
        $h  = '00';
        $i  = '00';
        $dataArr[] = [
            8001,
            $dateTime,
            [],
            [
                Util::$LCvalue  => new DateTime( $dateTime . Vcalendar::UTC ),
                Util::$LCparams => []
            ],
            ':' . $y . $m . $d . 'T' . $h . $i . '00'
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8005,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            8006,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $tz       = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8007,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dataArr[] = [
            8008,
            DATEYmd . ' ' . LTZ,
            [],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => LTZ, ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8012,
            DATEYmd . ' ' . LTZ,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            8013,
            DATEYmd . ' ' . LTZ,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz       = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8014,
            DATEYmd . ' ' . LTZ,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            8015,
            DATEYmd . ' ' . Vcalendar::UTC,
            [],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            80152,
            DATEYmd . Vcalendar::Z,
            [],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8019,
            DATEYmd . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            80192,
            DATEYmd . Vcalendar::Z,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            8020,
            DATEYmd . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            80202,
            DATEYmd . Vcalendar::Z,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8021,
            DATEYmd . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            80212,
            DATEYmd . Vcalendar::Z,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8022,
            DATEYmd . OFFSET,
            [],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8026,
            DATEYmd . OFFSET,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            8027,
            DATEYmd . OFFSET,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8028,
            DATEYmd . OFFSET,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with short string datetime, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DateTime8Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testDateTime8(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    )
    {
        foreach( self::$propsCompsProps as $comps => $compsProps ) {
            $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }

        $this->theTestMethod1b( $case, self::$compsProps2, $value, $params, $expectedGet, $expectedString );
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, 'P1D' ];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], new DateInterval( 'P1D' ) ],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
        ];
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case . 'P',
            [
                Vcalendar::VEVENT => [ Vcalendar::RDATE ]
            ],
            $value,
            $params,
            $expectedGet,
            $expectedString
        );
    }
}
