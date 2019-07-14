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

use DateTime;
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class DateTimeTest, testing DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.14 - 2019-01-24
 */
class DateTimeTest extends DtBase
{
    /**
     * set and restore local timezone from const
     */
    public static $oldTimeZone = null;
    public static function setUpBeforeClass() {
        self::$oldTimeZone = date_default_timezone_get();
        date_default_timezone_set( LTZ );
    }
    public static function tearDownAfterClass() {
        date_default_timezone_set( self::$oldTimeZone );
    }

    /**
     * testDateTime1 provider
     */
    public function DateTime1Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = new DateTime( DATEYmdTHis . ' ' . LTZ );
        $dataArr[] = [
            1008,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ)
        ];

        $dateTime = new DateTime( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            1012,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            1013,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            1014,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue => $this->getDateTimeAsArray( $dateTime2 ),
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
                Util::$LCvalue => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC));
        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            1019,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC));
        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            1020,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC));
        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            1021,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis . OFFSET );
        DateTimeFactory::setDateTimeTimeZone( $dateTime, $dateTime->getTimezone()->getName());
        $tz = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            1022,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis . OFFSET );
        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            1026,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmdTHis . OFFSET );
        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            1027,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            1028,
            $dateTime2,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with DateTime, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DateTime1Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime1(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
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
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, [ Util::$LCDAY => 1 ]];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], [ Util::$LCDAY => 1 ]],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
        ];
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case,
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
     * testDateTime2 provider
     */
    public function DateTime2Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC));
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( LTZ ));
        $dataArr[] = [
            2008,
            array_merge( $timestampArr, [ Util::$LCtz => LTZ ] ),
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, LTZ )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            2012,
            array_merge( $timestampArr, [ Util::$LCtz => LTZ ] ),
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            2013,
            array_merge( $timestampArr, [ Util::$LCtz => LTZ ] ),
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            2014,
            array_merge( $timestampArr, [ Util::$LCtz => LTZ ] ),
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC));
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            2015,
            $timestampArr,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            2019,
            $timestampArr,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            2020,
            $timestampArr,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            2021,
            $timestampArr,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $tz = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            2022,
            array_merge( $timestampArr, [ Util::$LCtz => OFFSET ] ),
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            2026,
            array_merge( $timestampArr, [ Util::$LCtz => OFFSET ] ),
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            2027,
            array_merge( $timestampArr, [ Util::$LCtz => OFFSET ] ),
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            2028,
            array_merge( $timestampArr, [ Util::$LCtz => OFFSET ] ),
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with timestamp, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DateTime2Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime2(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
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
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, [ Util::$LCDAY => 1 ]];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], [ Util::$LCDAY => 1 ]],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
        ];
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case,
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
     * testDateTime3 provider
     */
    public function DateTime3Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dataArr[] = [
            3001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime2 = new DateTime( DATEYmd, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            3005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            3006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            3007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $arrayDate[Util::$LCtz] = LTZ;
        $dataArr[] = [
            3008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, LTZ )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            3012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            3013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            3014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $y  = $dateTime->format( 'Y' );
        $m  = $dateTime->format( 'm' );
        $d  = $dateTime->format( 'd' );
        $arrayDate = [
            Util::$LCYEAR => $y, Util::$LCMONTH => $m, Util::$LCDAY => $d,
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dataArr[] = [
            3015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            3019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            3020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            3021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $y  = $dateTime->format( 'Y' );
        $m  = $dateTime->format( 'm' );
        $d  = $dateTime->format( 'd' );
        $tz = $dateTime2->getTimezone()->getName();
        $arrayDate = [
            Util::$LCYEAR => $y, Util::$LCMONTH => $m, Util::$LCDAY => $d,
            Util::$LCtz   => OFFSET
        ];
        $dataArr[] = [
            3022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            3026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            3027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            3028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with short assoc array, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DateTime3Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime3(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
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
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, [ Util::$LCDAY => 1 ]];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], [ Util::$LCDAY => 1 ]],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
        ];
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case,
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
     * testDateTime4 provider
     */
    public function DateTime4Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $y  = $dateTime->format( 'Y' );
        $m  = $dateTime->format( 'm' );
        $d  = $dateTime->format( 'd' );
        $h  = $dateTime->format( 'H' );
        $i  = $dateTime->format( 'i' );
        $arrayDate = [
            Util::$LCYEAR => $y, Util::$LCMONTH => $m, Util::$LCDAY => $d,
            Util::$LCHOUR => $h, Util::$LCMIN   => $i, Util::$LCSEC => '00',
        ];
        $dataArr[] = [
            4001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            4005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            4006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            4007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $arrayDate[Util::$LCtz] = LTZ;
        $dataArr[] = [
            4008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, LTZ )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            4012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            4013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            4014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $y  = $dateTime->format( 'Y' );
        $m  = $dateTime->format( 'm' );
        $d  = $dateTime->format( 'd' );
        $h  = $dateTime->format( 'H' );
        $i  = $dateTime->format( 'i' );
        $arrayDate = [
            Util::$LCYEAR => $y, Util::$LCMONTH => $m, Util::$LCDAY => $d,
            Util::$LCHOUR => $h, Util::$LCMIN   => $i, Util::$LCSEC => '00',
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dataArr[] = [
            4015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            4019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            4020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            4021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $y  = $dateTime->format( 'Y' );
        $m  = $dateTime->format( 'm' );
        $d  = $dateTime->format( 'd' );
        $h  = $dateTime->format( 'H' );
        $i  = $dateTime->format( 'i' );
        $tz = $dateTime->getTimezone()->getName();
        $arrayDate = [
            Util::$LCYEAR => $y, Util::$LCMONTH => $m, Util::$LCDAY => $d,
            Util::$LCHOUR => $h, Util::$LCMIN   => $i, Util::$LCSEC => '00',
            Util::$LCtz   => $tz
        ];
        $dataArr[] = [
            4022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            4026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            4027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            4028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with full assoc array, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DateTime4Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime4(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
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
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, [ Util::$LCDAY => 1 ]];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], [ Util::$LCDAY => 1 ]],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
        ];
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case,
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
     * testDateTime5 provider
     */
    public function DateTime5Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $y  = $dateTime->format( 'Y' );
        $m  = $dateTime->format( 'm' );
        $d  = $dateTime->format( 'd' );
        $arrayDate = [
            $y, $m, $d,
        ];
        $dataArr[] = [
            5001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime2 = new DateTime( DATEYmd, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            5005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            5006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            5007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $arrayDate[] = LTZ;
        $dataArr[] = [
            5008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, LTZ )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            5012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            5013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            5014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $y  = $dateTime->format( 'Y' );
        $m  = $dateTime->format( 'm' );
        $d  = $dateTime->format( 'd' );
        $arrayDate = [
            $y, $m, $d, Vcalendar::UTC
        ];
        $dataArr[] = [
            5015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            5019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            5020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            5021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $y  = $dateTime->format( 'Y' );
        $m  = $dateTime->format( 'm' );
        $d  = $dateTime->format( 'd' );
        $tz = $dateTime2->getTimezone()->getName();
        $arrayDate = [
            $y, $m, $d, OFFSET
        ];
        $dataArr[] = [
            5022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            5026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            5027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            5028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with short array, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DateTime5Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime5(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
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
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, [ Util::$LCDAY => 1 ]];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], [ Util::$LCDAY => 1 ]],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
        ];
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case,
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
     * testDateTime6 provider
     */
    public function DateTime6Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $y  = $dateTime->format( 'Y' );
        $m  = $dateTime->format( 'm' );
        $d  = $dateTime->format( 'd' );
        $h  = $dateTime->format( 'H' );
        $i  = $dateTime->format( 'i' );
        $arrayDate = [
            $y, $m, $d, $h, $i, '00',
        ];
        $dataArr[] = [
            6001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            6005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            6006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            6007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $arrayDate[] = LTZ;
        $dataArr[] = [
            6008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, LTZ )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            6012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            6013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            6014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            $y, $m, $d, $h, $i, '00', Vcalendar::UTC
        ];
        $dataArr[] = [
            6015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            6019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            6020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            6021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $arrayDate = $this->getDateTimeAsArray( $dateTime );
        $arrayDate[Util::$LCtz] = $tz;
        $dataArr[] = [
            6022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            6026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            6027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            6028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with full array, DTSTART, DTEND, DUE, RECURRENCE_ID, (single) EXDATE + RDATE
     *
     * @test
     * @dataProvider DateTime6Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime6(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
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
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, [ Util::$LCDAY => 1 ]];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], [ Util::$LCDAY => 1 ]],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
        ];
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case,
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
     * testDateTime7 provider
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
        $dataArr[] = [
            7001,
            $dateTime,
            [],
            [
                Util::$LCvalue =>
                    [
                        Util::$LCYEAR => $y, Util::$LCMONTH => $m, Util::$LCDAY => $d,
                        Util::$LCHOUR => $h, Util::$LCMIN   => $i, Util::$LCSEC => '00',
                    ],
                Util::$LCparams => []
            ],
            ':' . $y . $m . $d . 'T' . $h . $i . '00'
        ];

        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7005,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            7006,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7007,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $dataArr[] = [
            7008,
            DATEYmdTHis . ' ' . LTZ,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => LTZ, ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7012,
            DATEYmdTHis . ' ' . LTZ,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            7013,
            DATEYmdTHis . ' ' . LTZ,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7014,
            DATEYmdTHis . ' ' . LTZ,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            7015,
            DATEYmdTHis . ' UTC',
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7019,
            DATEYmdTHis . ' UTC',
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            7020,
            DATEYmdTHis . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7021,
            DATEYmdTHis . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            7022,
            DATEYmdTHis . OFFSET,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            7026,
            DATEYmdTHis . OFFSET,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            7027,
            DATEYmdTHis . OFFSET,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            7028,
            DATEYmdTHis . OFFSET,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
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
     */
    public function testDateTime7(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
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
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, [ Util::$LCDAY => 1 ]];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], [ Util::$LCDAY => 1 ]],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
        ];
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case,
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
                Util::$LCvalue =>
                    [
                        Util::$LCYEAR => $y, Util::$LCMONTH => $m, Util::$LCDAY => $d,
                        Util::$LCHOUR => $h, Util::$LCMIN   => $i, Util::$LCSEC => '00',
                    ],
                Util::$LCparams => []
            ],
            ':' . $y . $m . $d . 'T' . $h . $i . '00'
        ];

        $dateTime2 = new DateTime( DATEYmd, DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8005,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            8006,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            8007,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $dataArr[] = [
            8008,
            DATEYmd . ' ' . LTZ,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => LTZ, ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8012,
            DATEYmd . ' ' . LTZ,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            8013,
            DATEYmd . ' ' . LTZ,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            8014,
            DATEYmd . ' ' . LTZ,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            8015,
            DATEYmd . ' ' . Vcalendar::UTC,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8019,
            DATEYmd . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            8020,
            DATEYmd . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            8021,
            DATEYmd . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            8022,
            DATEYmd . OFFSET,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            8026,
            DATEYmd . OFFSET,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, TZ2 )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            8027,
            DATEYmd . OFFSET,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime2->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime2->getTimezone()->getName();
        $dataArr[] = [
            8028,
            DATEYmd . OFFSET,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, $tz )
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
     */
    public function testDateTime8(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
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
        if( empty( $value )) {
            return;
        }
        $value          = [ $value, [ Util::$LCDAY => 1 ]];
        $params         = [ Vcalendar::VALUE => Vcalendar::PERIOD ] + $params;
        $expectedGet    = [
            Util::$LCvalue  => [ $expectedGet[Util::$LCvalue], [ Util::$LCDAY => 1 ]],
            Util::$LCparams => $expectedGet[Util::$LCparams] + [ Vcalendar::VALUE => Vcalendar::PERIOD ]
        ];
        $expectedString = ';' . Vcalendar::VALUE . '=' . Vcalendar::PERIOD . $expectedString . '/P1D';
        $this->theTestMethod1b(
            $case,
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
     * testDateTime9 provider
     */
    public function DateTime9Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dataArr[] = [
            9001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( TZ2 ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dataArr[] = [
            9005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dataArr[] = [
            9006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $tz = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            9007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => LTZ
        ];
        $dataArr[] = [
            9008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            9012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ  ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            9013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( LTZ ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            9014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dataArr[] = [
            9015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            9019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dataArr[] = [
            9020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            9021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            9022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            9026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            9027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmd, DateTimeZoneFactory::factory( OFFSET ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            9028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with (short...) args, DTSTART, DTEND, DUE, RECURRENCE_ID
     *
     * @test
     * @dataProvider DateTime9Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime9(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT   => [ Vcalendar::DTSTART, Vcalendar::DTEND, Vcalendar::RECURRENCE_ID ],
            Vcalendar::VTODO    => [ Vcalendar::DTSTART, Vcalendar::DUE, Vcalendar::RECURRENCE_ID ],
            Vcalendar::VJOURNAL => [ Vcalendar::DTSTART, Vcalendar::RECURRENCE_ID ],
        ];
        $this->theTestMethod2( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * testDateTime10 provider
     */
    public function DateTime10Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];


        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
        ];
        $dataArr[] = [
            10001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( TZ2 ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
        ];
        $dataArr[] = [
            10005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
        ];
        $dataArr[] = [
            10006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
        ];
        $tz = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            10007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz    => LTZ
        ];
        $dataArr[] = [
            10008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => LTZ ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, LTZ )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz    => LTZ
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            10012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ  ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz    => LTZ
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            10013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz    => LTZ
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            10014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dataArr[] = [
            10015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            10019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dataArr[] = [
            10020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( OFFSET ));
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            10021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];


        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz    => OFFSET
        ];
        $tz = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            10022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz    => OFFSET
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( TZ2 ));
        $dataArr[] = [
            10026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => TZ2 ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, TZ2 )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz    => OFFSET
        ];
        $dateTime->setTimezone( DateTimeZoneFactory::factory( Vcalendar::UTC ));
        $dataArr[] = [
            10027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( OFFSET ));
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => '00',
            Util::$LCtz    => OFFSET
        ];
        $tz        = $dateTime->getTimezone()->getName();
        $dataArr[] = [
            10028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => [ Vcalendar::TZID => $tz ]
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, $tz )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with all args, DTSTART, DTEND, DUE, RECURRENCE_ID
     *
     * @test
     * @dataProvider DateTime10Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime10(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT   => [ Vcalendar::DTSTART, Vcalendar::DTEND, Vcalendar::RECURRENCE_ID ],
            Vcalendar::VTODO    => [ Vcalendar::DTSTART, Vcalendar::DUE, Vcalendar::RECURRENCE_ID ],
            Vcalendar::VJOURNAL => [ Vcalendar::DTSTART, Vcalendar::RECURRENCE_ID ],
        ];
        $this->theTestMethod2( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

}
