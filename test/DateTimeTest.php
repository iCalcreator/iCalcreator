<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
    public static ?string $oldTimeZone = null;

    /**
     * @return void
     */
    public static function setUpBeforeClass() : void
    {
        self::$oldTimeZone = date_default_timezone_get();
        date_default_timezone_set( LTZ );
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass() : void
    {
        date_default_timezone_set( self::$oldTimeZone );
    }

    /**
     * @var mixed[][]
     */
    private static array $propsCompsProps = [
        IcalInterface::DTSTART => [
            IcalInterface::VEVENT        => [ IcalInterface::DTSTART ],
            IcalInterface::VTODO         => [ IcalInterface::DTSTART ],
            IcalInterface::VJOURNAL      => [ IcalInterface::DTSTART ],
            IcalInterface::AVAILABLE     => [ IcalInterface::DTSTART ],
            IcalInterface::VAVAILABILITY => [ IcalInterface::DTSTART ]
        ],
        IcalInterface::DTEND => [
            IcalInterface::VEVENT        => [ IcalInterface::DTEND ],
            IcalInterface::AVAILABLE     => [ IcalInterface::DTEND ],
            IcalInterface::VAVAILABILITY => [ IcalInterface::DTEND ]
        ],
        IcalInterface::DUE => [
            IcalInterface::VTODO     => [ IcalInterface::DUE ],
        ],
        IcalInterface::RECURRENCE_ID => [
            IcalInterface::VEVENT    => [ IcalInterface::RECURRENCE_ID ],
            IcalInterface::VTODO     => [ IcalInterface::RECURRENCE_ID ],
        ],
        IcalInterface::EXDATE => [
            IcalInterface::VEVENT    => [ IcalInterface::EXDATE ],
            IcalInterface::AVAILABLE => [ IcalInterface::EXDATE ],
        ],
        IcalInterface::RDATE => [
            IcalInterface::VEVENT    => [ IcalInterface::RDATE ],
            IcalInterface::AVAILABLE => [ IcalInterface::RDATE ],
        ],
    ];

    private static array $compsProps2 = [
        IcalInterface::VEVENT => [
            IcalInterface::EXDATE,
            IcalInterface::RDATE
        ],
        IcalInterface::AVAILABLE => [
            IcalInterface::EXDATE,
            IcalInterface::RDATE
        ]
    ];

    /**
     * testDateTime1 provider
     *
     * @return mixed[]
     * @throws Exception
     */
    public function DateTime1Provider() : array
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
                Util::$LCparams => [ IcalInterface::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ)
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            1012,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            1013,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            1014,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC));
        $dataArr[] = [
            1015,
            $dateTime,
            [],
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime  = new DateTimeImmutable(
            DATEYmdTHis,
            DateTimeZoneFactory::factory( IcalInterface::UTC)
        );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            1019,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime = new DateTime(
            DATEYmdTHis,
            DateTimeZoneFactory::factory( IcalInterface::UTC)
        );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            1020,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = new DateTimeImmutable(
            DATEYmdTHis,
            DateTimeZoneFactory::factory( IcalInterface::UTC)
        );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            1021,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
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
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . OFFSET );
        $dateTime2 = clone $dateTime;
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, TZ2 );
        $dataArr[] = [
            1026,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . OFFSET );
        $dateTime2 = clone $dateTime;
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            1027,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime = new DateTimeImmutable(
            DATEYmdTHis,
            DateTimeZoneFactory::factory( OFFSET )
        );
        $tz = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            1028,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
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
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testDateTime1(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        $expectedGet2 = empty( $value )
            ? []
            : [
                Util::$LCvalue  => [ clone $expectedGet[Util::$LCvalue], new DateInterval( 'P1D' ) ],
                Util::$LCparams => $expectedGet[Util::$LCparams] + [ IcalInterface::VALUE => IcalInterface::PERIOD ]
            ];

        foreach( self::$propsCompsProps as $compsProps ) {
            $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }

        $this->theTestMethod1b( $case, self::$compsProps2, $value, $params, $expectedGet, $expectedString );
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, 'P1D' ];
        $params         = [ IcalInterface::VALUE => IcalInterface::PERIOD ] + $params;
        $expectedString = ';' . IcalInterface::VALUE . '=' . IcalInterface::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case . 'P',
            [
                IcalInterface::VEVENT => [ IcalInterface::RDATE ]
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
     * @return mixed[]
     * @throws Exception
     */
    public function DateTime7Provider() : array
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DATEYmdTHis;
        $y  = substr( $dateTime, 0, 4 );
        $m  = substr( $dateTime, 4, 2 );
        $d  = substr( $dateTime, 6, 2);
        $h  = substr( $dateTime, 9, 2 );
        $i  = substr( $dateTime, 11, 2);
        $dateTime2 = new DateTime( DATEYmdTHis . ' ' . IcalInterface::UTC );
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
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            7006,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7007,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => clone $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dataArr[] = [
            7008,
            $dateTime,
            [],
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => LTZ, ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ )
        ];

        $dateTime  = DATEYmdTHis . ' ' . LTZ;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7012,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . ' ' . LTZ;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            7013,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . ' ' . LTZ;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7014,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            7015,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            70152,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7019,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            70192,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            7020,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            70202,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7021,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            70212,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
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
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7026,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            7027,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7028,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
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
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testDateTime7(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        foreach( self::$propsCompsProps as $comps => $compsProps ) {
            $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }

        $this->theTestMethod1b( $case, self::$compsProps2, $value, $params, $expectedGet, $expectedString );
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, 'P1D' ];
        $params         = [ IcalInterface::VALUE => IcalInterface::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], new DateInterval( 'P1D' ) ],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ IcalInterface::VALUE => IcalInterface::PERIOD ]
        ];
        $expectedString = ';' . IcalInterface::VALUE . '=' . IcalInterface::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case . 'P',
            [
                IcalInterface::VEVENT => [ IcalInterface::RDATE ]
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
     * @return mixed[]
     * @throws Exception
     */
    public function DateTime8Provider() : array
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
                Util::$LCvalue  => new DateTime( $dateTime . IcalInterface::UTC ),
                Util::$LCparams => []
            ],
            ':' . $y . $m . $d . 'T' . $h . $i . '00'
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8005,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            8006,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $tz       = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8007,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
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
                Util::$LCparams => [ IcalInterface::TZID => LTZ, ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8012,
            DATEYmd . ' ' . LTZ,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            8013,
            DATEYmd . ' ' . LTZ,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz       = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8014,
            DATEYmd . ' ' . LTZ,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            8015,
            DATEYmd . ' ' . IcalInterface::UTC,
            [],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            80152,
            DATEYmd . IcalInterface::Z,
            [],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8019,
            DATEYmd . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            80192,
            DATEYmd . IcalInterface::Z,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            8020,
            DATEYmd . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            80202,
            DATEYmd . IcalInterface::Z,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8021,
            DATEYmd . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            80212,
            DATEYmd . IcalInterface::Z,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
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
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8026,
            DATEYmd . OFFSET,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            8027,
            DATEYmd . OFFSET,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8028,
            DATEYmd . OFFSET,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime,
                Util::$LCparams => [ IcalInterface::TZID => $tz ]
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
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testDateTime8(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        foreach( self::$propsCompsProps as $comps => $compsProps ) {
            $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }

        $this->theTestMethod1b( $case, self::$compsProps2, $value, $params, $expectedGet, $expectedString );
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, 'P1D' ];
        $params         = [ IcalInterface::VALUE => IcalInterface::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], new DateInterval( 'P1D' ) ],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ IcalInterface::VALUE => IcalInterface::PERIOD ]
        ];
        $expectedString = ';' . IcalInterface::VALUE . '=' . IcalInterface::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case . 'P',
            [
                IcalInterface::VEVENT => [ IcalInterface::RDATE ]
            ],
            $value,
            $params,
            $expectedGet,
            $expectedString
        );
    }
}
