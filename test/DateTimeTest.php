<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2023 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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
use DateTimeInterface;
use Exception;
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;

/**
 * class DateTimeTest, testing DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
 *
 * @since  2.41.83 - 2023-09-02
 */
class DateTimeTest extends DtBase
{
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
     * dateTimeTest1 provider VALUE DATE-TIME with DateTimeInterface
     *
     * @return mixed[]
     * @throws Exception
     */
    public static function dateTimeTest1Provider() : array
    {
        $dataArr   = [];

        $dateTime  = new DateTime( DATEYmdTHis . ' ' . LTZ );
        $dataArr[] = [
            1008,
            $dateTime,
            [],
            Pc::factory(
                clone $dateTime,
                [ IcalInterface::TZID => LTZ ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, LTZ)
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            1012,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                clone $dateTime2,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            1013,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                clone $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            1014,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                clone $dateTime2,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC));
        $dataArr[] = [
            1015,
            $dateTime,
            [],
            Pc::factory(
                clone $dateTime,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
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
            Pc::factory(
                clone $dateTime2,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, TZ2 )
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
            Pc::factory(
                clone $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
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
            Pc::factory(
                clone $dateTime2,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . OFFSET );
        $tz        = DateTimeZoneFactory::getTimeZoneNameFromOffset( OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime, $tz );
        $dataArr[] = [
            1022,
            $dateTime,
            [],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . OFFSET );
        $dateTime2 = clone $dateTime;
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, TZ2 );
        $dataArr[] = [
            1026,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . OFFSET );
        $dateTime2 = clone $dateTime;
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            1027,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                clone $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
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
            Pc::factory(
                clone $dateTime,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        // 2.41.83, github 114,   DTSTART;TZID=US/Pacific:20170408T120000
        $dateTime = new DateTimeImmutable(
            '20170408T120000',
            DateTimeZoneFactory::factory( 'US/Pacific' )
        );
        $tz = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            24183,
            $dateTime,
            [ IcalInterface::TZID => $tz ],
            Pc::factory(
                clone $dateTime,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with DateTimeInterface, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider dateTimeTest1Provider
     *
     * @param int     $case
     * @param DateTimeInterface   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function dateTimeTest1(
        int    $case,
        DateTimeInterface $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        foreach( self::$propsCompsProps as $compsProps ) {
            $this->thePropTest( $case, $compsProps, $value, $params, clone $expectedGet, $expectedString );
        }

        $this->exdateRdateSpecTest( $case, self::$compsProps2, $value, $params, clone $expectedGet, $expectedString );
        if( empty( $value )) {
            return;
        }
        $value2       = [ $value, 'P1D' ];
        $params2      = [ IcalInterface::VALUE => IcalInterface::PERIOD ] + $params;
        $expectedGet2 =  Pc::factory(
            [ clone $expectedGet->getValue(), new DateInterval( 'P1D' ) ],
            $expectedGet->params + [ IcalInterface::VALUE => IcalInterface::PERIOD ]
        );
        $expectedString2 = ';' . IcalInterface::VALUE . '=' . IcalInterface::PERIOD . $expectedString . '/P1D';
        $this->exdateRdateSpecTest(
            $case . 'P',
            [
                IcalInterface::VEVENT => [ IcalInterface::RDATE ]
            ],
            $value2,
            $params2,
            $expectedGet2,
            $expectedString2
        );

        $value3          = [ $value, $value ];
        $expectedGet3    = Pc::factory(
            [ $expectedGet->getValue(), $expectedGet->getValue() ],
            $expectedGet->getParams()
        );
        $zExt            = ( str_ends_with( $expectedString, 'Z' )) ? 'Z' : '';
        $expectedString3 = $expectedString . ',' . $expectedGet->getValue()->format( DateTimeFactory::$YmdTHis ) . $zExt;
        $this->exdateRdateSpecTest(
            $case . 'M',
            [
                IcalInterface::VEVENT => [ IcalInterface::EXDATE, IcalInterface::RDATE ]
            ],
            $value3,
            $params,
            $expectedGet3,
            $expectedString3
        );
    }

    /**
     * Returns same as dateTimeTest1Provider
     *
     * @return mixed[]
     * @throws Exception
     */
    public static function dateTimeTest1bProvider() : array
    {
        $dataArr   = [];

        foreach( self::$DATECONSTANTFORMTS as $x => $format ) {
            $dateTime  = new DateTimeImmutable( DATEYmdTHis . ' ' . LTZ );
            $dateTime2 = DateTimeFactory::toDateTime( $dateTime );
            $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
            $dataArr[] = [
                $x + 29001,
                $dateTime->format( $format ),
                [ IcalInterface::TZID => TZ2 ],
                Pc::factory(
                    clone $dateTime2,
                    [ IcalInterface::TZID => TZ2 ]
                ),
                self::getDateTimeAsCreateLongString( $dateTime2, TZ2 ),
                $format
            ];
        }

        return $dataArr;
    }

    /**
     * Testing (string) VALUE DATE-TIME with PHP date format constants, DTSTART only
     *
     * @test
     * @dataProvider dateTimeTest1bProvider
     *
     * @param int     $case
     * @param string  $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @param string  $format
     * @throws Exception
     */
    public function dateTimeTest1b(
        int    $case,
        string $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString,
        string $format
    ) : void
    {
        $this->thePropTest(
            $case,
            [ IcalInterface::VEVENT => [ IcalInterface::DTSTART ]],
            $value,
            $params,
            $expectedGet,
            $expectedString
        );
    }

    /**
     * dateTimeTest7 provider, full datetime string
     *
     * @return mixed[]
     * @throws Exception
     */
    public static function dateTimeTest7Provider() : array
    {
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
            Pc::factory(
                clone $dateTime2,
                []
            ),
            ':' . $y . $m . $d . 'T' . $h . $i . '00'
        ];

        $dateTime  = DATEYmdTHis;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7005,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                clone $dateTime2,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            7006,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                clone $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7007,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                clone $dateTime2,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dataArr[] = [
            7008,
            $dateTime,
            [],
            Pc::factory(
                clone $dateTime,
                [ IcalInterface::TZID => LTZ, ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, LTZ )
        ];

        $dateTime  = DATEYmdTHis . ' ' . LTZ;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7012,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . ' ' . LTZ;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            7013,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . ' ' . LTZ;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7014,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            7015,
            $dateTime,
            [],
            Pc::factory(
                $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            70152,
            $dateTime,
            [],
            Pc::factory(
                $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7019,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            70192,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            7020,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            70202,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . ' UTC';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7021,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = DATEYmdTHis . 'Z';
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            70212,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7022,
            $dateTime,
            [],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7026,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            7027,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DATEYmdTHis . OFFSET;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7028,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with full string datetime, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider dateTimeTest7Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function dateTimeTest7(
        int    $case,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        foreach( self::$propsCompsProps as $compsProps ) {
            $this->thePropTest( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
            $this->propGetNoParamsTest( $case, $compsProps, $value, $params, $expectedGet );
        }

        $this->exdateRdateSpecTest( $case, self::$compsProps2, $value, $params, $expectedGet, $expectedString );
        if( empty( $value )) {
            return;
        }
        $value2          = [ $value, 'P1D' ];
        $params2         = [ IcalInterface::VALUE => IcalInterface::PERIOD ] + $params;
        $expectedGet2    = Pc::factory(
            [ $expectedGet->getValue(), new DateInterval( 'P1D' ) ],
            $expectedGet->params + [ IcalInterface::VALUE => IcalInterface::PERIOD ]
        );
        $expectedString3 = ';' . IcalInterface::VALUE . '=' . IcalInterface::PERIOD . $expectedString . '/P1D';
        $this->exdateRdateSpecTest(
            $case . 'P',
            [
                IcalInterface::VEVENT => [ IcalInterface::RDATE ]
            ],
            $value2,
            $params2,
            $expectedGet2,
            $expectedString3
        );

        $value3          = [ $value, $value ];
        $expectedGet3    = Pc::factory(
            [ $expectedGet->getValue(), $expectedGet->getValue() ],
            $expectedGet->params
        );
        $zExt = ( 'Z' === substr( $expectedString, -1 )) ? 'Z' : '';
        $expectedString3 = $expectedString . ',' . $expectedGet->getValue()->format( DateTimeFactory::$YmdTHis ) . $zExt;
        $this->exdateRdateSpecTest(
            $case . 'M',
            [
                IcalInterface::VEVENT => [ IcalInterface::EXDATE, IcalInterface::RDATE ]
            ],
            $value3,
            $params,
            $expectedGet3,
            $expectedString3
        );
    }

    /**
     * dateTimeTest7 provider VALUE DATE-TIME with DateTimeInterface
     *
     * @return mixed[]
     * @throws Exception
     */
    public static function dateTimeTest7msProvider() : array
    {
        $dataArr = [];

        // testing one MS timezone, from all but last three below
        [ $msTz, $phpTz ] = self::getRandomMsAndPhpTz();
        if( 'Etc/UTC' === $phpTz ) {
            $phpTz = 'UTC';
        }
        $dateTime  = DATEYmdTHis . ' ' . $msTz;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( $phpTz ));
        $dataArr[] = [
            7112,
            $dateTime,
            [], // [ IcalInterface::TZID => $phpTz ],
            Pc::factory(
                $dateTime2,
                [ IcalInterface::TZID => $phpTz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $phpTz )
        ];

        $tz1       = 'UTC';
        $tz2       = 'Etc/UTC';
        $dateTime  = DATEYmdTHis . ' ' . $tz2;
        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( $tz1 ));
        $dataArr[] = [
            7113,
            $dateTime,
            [], // [ IcalInterface::TZID => $phpTz ],
            Pc::factory(
                $dateTime2,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime2, $tz1 )
        ];

        // testing spec 'UTC' MS timezones
        foreach( [ 'UTC', 'UTC-02', 'UTC-11', 'UTC+12' ] as $mx => $msTz ) {
            $phpTz     = \IntlTimeZone::getIDForWindowsID( $msTz );
            if( 'Etc/UTC' === $phpTz ) {
                $phpTz = 'UTC';
            }
            $dateTime  = DATEYmdTHis . ' ' . $msTz;
            $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( $phpTz ));
            $caseNo    = ( $mx + 7114 );
            $expString = self::getDateTimeAsCreateLongString( $dateTime2, $phpTz );
            $params    = ( 'UTC' === $phpTz ) ? [] : [ IcalInterface::TZID => $phpTz ];
            $dataArr[] = [
                $caseNo,
                $dateTime,
                [],
                Pc::factory(
                    $dateTime2,
                    $params
                ),
                $expString
            ];
        } // end foreach
        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with full string MS datetime, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * Same as dateTimeTest7 BUT MS timezones
     *
     * @test
     * @dataProvider dateTimeTest7msProvider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function dateTimeTest7ms(
        int    $case,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        $this->dateTimeTest7( $case, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * dateTimeTest8 provider
     *
     * @return mixed[]
     * @throws Exception
     */
    public static function dateTimeTest8Provider() : array
    {
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
            Pc::factory(
                new DateTime( $dateTime . IcalInterface::UTC ),
                []
            ),
            ':' . $y . $m . $d . 'T' . $h . $i . '00'
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8005,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            8006,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                $dateTime,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $tz       = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8007,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dataArr[] = [
            8008,
            DATEYmd . ' ' . LTZ,
            [],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => LTZ, ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, LTZ )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8012,
            DATEYmd . ' ' . LTZ,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            8013,
            DATEYmd . ' ' . LTZ,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                $dateTime,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz       = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8014,
            DATEYmd . ' ' . LTZ,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            8015,
            DATEYmd . ' ' . IcalInterface::UTC,
            [],
            Pc::factory(
                $dateTime,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            80152,
            DATEYmd . IcalInterface::Z,
            [],
            Pc::factory(
                $dateTime,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8019,
            DATEYmd . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            80192,
            DATEYmd . IcalInterface::Z,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            8020,
            DATEYmd . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                $dateTime,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            80202,
            DATEYmd . IcalInterface::Z,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                $dateTime,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8021,
            DATEYmd . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            80212,
            DATEYmd . IcalInterface::Z,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8022,
            DATEYmd . OFFSET,
            [],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8026,
            DATEYmd . OFFSET,
            [ IcalInterface::TZID => TZ2 ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => TZ2 ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( IcalInterface::UTC ));
        $dataArr[] = [
            8027,
            DATEYmd . OFFSET,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            Pc::factory(
                $dateTime,
                []
            ),
            self::getDateTimeAsCreateLongString( $dateTime, IcalInterface::UTC )
        ];

        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8028,
            DATEYmd . OFFSET,
            [ IcalInterface::TZID => OFFSET ],
            Pc::factory(
                $dateTime,
                [ IcalInterface::TZID => $tz ]
            ),
            self::getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        // testing MS timezone
       [ $msTz, $phpTz ] = self::getRandomMsAndPhpTz();
        $dateTime  = new DateTime( DATEYmd, DateTimeZoneFactory::factory( $phpTz ));
        $dataArr[] = [
            8108,
            DATEYmd . ' ' . $msTz,
            [],
            Pc::factory(
                $dateTime,
                (( IcalInterface::UTC !== $phpTz ) ? [ IcalInterface::TZID => $phpTz, ] : [] )
            ),
            self::getDateTimeAsCreateLongString( $dateTime, $phpTz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with short string datetime, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider dateTimeTest8Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function dateTimeTest8(
        int    $case,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        foreach( self::$propsCompsProps as $compsProps ) {
            $this->thePropTest( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
            $this->propGetNoParamsTest( $case, $compsProps, $value, $params, $expectedGet );
        }

        $this->exdateRdateSpecTest( $case, self::$compsProps2, $value, $params, $expectedGet, $expectedString );
        if( empty( $value )) {
            return;
        }
        $value2          = [ $value, 'P1D' ];
        $params2         = [ IcalInterface::VALUE => IcalInterface::PERIOD ] + $params;
        $expectedGet2    = Pc::factory(
            [ $expectedGet->getValue(), new DateInterval( 'P1D' ) ],
            $expectedGet->params + [ IcalInterface::VALUE => IcalInterface::PERIOD ]
        );
        $expectedString2 = ';' . IcalInterface::VALUE . '=' . IcalInterface::PERIOD . $expectedString . '/P1D';
        $this->exdateRdateSpecTest(
            $case . 'P',
            [
                IcalInterface::VEVENT => [ IcalInterface::RDATE ]
            ],
            $value2,
            $params2,
            $expectedGet2,
            $expectedString2
        );

        $value3          = [ $value, $value ];
        $expectedGet3    = Pc::factory(
            [ $expectedGet->getValue(), $expectedGet->getValue() ],
            $expectedGet->params
        );
        $zExt = ( str_ends_with( $expectedString, 'Z' )) ? 'Z' : '';
        $expectedString3 = $expectedString . ',' . $expectedGet->getValue()->format( DateTimeFactory::$YmdTHis ) . $zExt;
        $this->exdateRdateSpecTest(
            $case . 'M',
            [
                IcalInterface::VEVENT => [ IcalInterface::EXDATE, IcalInterface::RDATE ]
            ],
            $value3,
            $params,
            $expectedGet3,
            $expectedString3
        );
    }
}
