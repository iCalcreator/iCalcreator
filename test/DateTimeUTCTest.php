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

use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class DateTest, testing DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.14 - 2019-01-24
 */
class DateTimeUTCTest extends DtBase
{

    private static $ERRFMT = "Error %sin case #%s, %s <%s>->%s";

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
     * The recur DATETIME test method , EXRULE + RRULE
     *
     * @param int    $case
     * @param array  $compsProps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function theRecurTestMethod(
        $case,
        array $compsProps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $calendar1 = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $calendar1->{$newMethod}();
            $comp->setDtstart( $value, $params );
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );

                $recurSet = [
                    Vcalendar::FREQ       => Vcalendar::YEARLY,
                    Vcalendar::UNTIL      => $value,
                    Vcalendar::INTERVAL   => 2,
                    Vcalendar::BYSECOND   => [1,2,3],
                    Vcalendar::BYMINUTE   => [12,23,45],
                    Vcalendar::BYHOUR     => [3,5,7] ,
                    Vcalendar::BYDAY      => [1, Vcalendar::MO],
                    Vcalendar::BYMONTHDAY => [-1],
                    Vcalendar::BYYEARDAY  => [100,200,300],
                    Vcalendar::BYWEEKNO   => [20,39,40],
                    Vcalendar::BYMONTH    => [1,2,3,4,5, 7,8,9,10,11],
                    Vcalendar::BYSETPOS   => [1,2,3,4,5],
                    Vcalendar::WKST       => Vcalendar::SU
                ];
                $comp->{$setMethod}( $recurSet );

                $getValue = $comp->{$getMethod}( null, true );
                $this->assertEquals(
                    $expectedGet[Util::$LCvalue],
                    $getValue[Util::$LCvalue][Vcalendar::UNTIL],
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    substr( $expectedString, 1 ),
                    trim( StringFactory::between( 'UNTIL=', ';INTERVAL', $comp->{$createMethod}())),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                );
                $comp->{$setMethod}( $recurSet );
            }
        }
        $calendar1Str = $calendar1->createCalendar();
        $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], null, $calendar1Str );
        $createString = str_replace( '\,', ',', $createString );
        if( ':' == $expectedString{0} ) { // opt excl lead ':'
            $expectedString = substr( $expectedString, 1 );
        }
        $this->assertNotFalse(
            strpos( $createString, $expectedString ),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'Vcalendar', 'createComponent' )
        );

        $this->parseCalendarTest( $case, $calendar1, $expectedString );
    }

    /**
     * The FREEBUSY DATETIME/DATETIME test method
     *
     * @param int    $case
     * @param array  $compsProps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function theFreebusyTestMethodDate(
        $case,
        array $compsProps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $calendar1 = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $calendar1->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                $comp->{$setMethod}( Vcalendar::BUSY, [$value, $value] );

                $getValue = $comp->{$getMethod}( null, true );
                $this->assertEquals(
                    $expectedGet[Util::$LCvalue],
                    $getValue[Util::$LCvalue][0][0],
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    substr( $expectedString, 1 ),
                    trim( StringFactory::between( Vcalendar::BUSY . ':', '/', $comp->{$createMethod}())),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                );
                $comp->{$setMethod}( Vcalendar::BUSY, [$value, $value] );
            }
        }
        $calendar1Str = $calendar1->createCalendar();
        $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], null, $calendar1Str );
        $createString = str_replace( '\,', ',', $createString );
        $this->assertNotFalse(
            strpos( $createString, $expectedString ),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'Vcalendar', 'createComponent' )
        );

        $this->parseCalendarTest( $case, $calendar1, $expectedString );
    }

    /**
     * The FREEBUSY DATETIME/DATEINTERVAL test method
     *
     * @param int    $case
     * @param array  $compsProps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function theFreebusyTestMethodDateInterval(
        $case,
        array $compsProps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $calendar1 = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $calendar1->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                $comp->{$setMethod}( Vcalendar::BUSY, [$value, 'P1D'] );

                $getValue = $comp->{$getMethod}( null, true );
                $this->assertEquals(
                    $expectedGet[Util::$LCvalue],
                    $getValue[Util::$LCvalue][0][0],
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    substr( $expectedString, 1 ),
                    trim( StringFactory::between( Vcalendar::BUSY . ':', '/', $comp->{$createMethod}())),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                );
                $comp->{$setMethod}( Vcalendar::BUSY, [$value, $value] );
            }
        }
        $calendar1Str = $calendar1->createCalendar();
        $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], null, $calendar1Str );
        $createString = str_replace( '\,', ',', $createString );
        $this->assertNotFalse(
            strpos( $createString, $expectedString ),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'Vcalendar', 'createComponent' )
        );

        $this->parseCalendarTest( $case, $calendar1, $expectedString );
    }

    /**
     * The TRIGGER DATETIME test method
     *
     * @param int    $case
     * @param array  $compsProps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function theTriggerTestMethod(
        $case,
        array $compsProps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $calendar1 = new Vcalendar();
        $e         = $calendar1->newVevent();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $e->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                $comp->{$setMethod}( $value, [Vcalendar::VALUE => Vcalendar::DATE_TIME] );

                $getValue = $comp->{$getMethod}( true );
                $this->assertEquals(
                    $expectedGet[Util::$LCvalue],
                    $getValue[Util::$LCvalue],
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    strtoupper( $propName ) . ';VALUE=DATE-TIME' . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                );
                $comp->{$setMethod}( $value, [Vcalendar::VALUE => Vcalendar::DATE_TIME] );
            }
        }
        $calendar1Str = $calendar1->createCalendar();
        $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], null, $calendar1Str );
        $createString = str_replace( '\,', ',', $createString );
        $this->assertNotFalse(
            strpos( $createString, $expectedString ),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'Vcalendar', 'createComponent' )
        );

        $this->parseCalendarTest( $case, $calendar1, $expectedString );
    }

    /**
     * The TRIGGER DATETIME args test method
     *
     * @param int    $case
     * @param array  $compsProps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function theTriggerTestMethod2(
        $case,
        array $compsProps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $keys = null;
        if( empty( $keys )) {
            $keys = [
                Util::$LCYEAR, Util::$LCMONTH, Util::$LCDAY,
                Util::$LCHOUR, Util::$LCMIN,   Util::$LCSEC
            ];
        }
        $calendar1 = new Vcalendar();
        $e         = $calendar1->newVevent();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $e->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                foreach( $keys as $key ) {
                    ${$key} = ( isset( $value[$key] )) ? $value[$key] : null;
                }
                $comp->{$setMethod}( ${Util::$LCYEAR}, ${Util::$LCMONTH}, ${Util::$LCDAY},
                                     ${Util::$LCHOUR}, ${Util::$LCMIN},   ${Util::$LCSEC},
                                     [Vcalendar::VALUE => Vcalendar::DATE_TIME] );

                $getValue = $comp->{$getMethod}( true );
                $this->assertEquals(
                    $expectedGet[Util::$LCvalue],
                    $getValue[Util::$LCvalue],
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    strtoupper( $propName ) . ';VALUE=DATE-TIME' . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                );
                $comp->{$setMethod}( ${Util::$LCYEAR}, ${Util::$LCMONTH}, ${Util::$LCDAY},
                                     ${Util::$LCHOUR}, ${Util::$LCMIN},   ${Util::$LCSEC},
                                     [Vcalendar::VALUE => Vcalendar::DATE_TIME] );
            }
        }
        $calendar1Str = $calendar1->createCalendar();
        $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], null, $calendar1Str );
        $createString = str_replace( '\,', ',', $createString );
        $this->assertNotFalse(
            strpos( $createString, $expectedString ),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'Vcalendar', 'createComponent' )
        );

        $this->parseCalendarTest( $case, $calendar1, $expectedString );
    }

    /**
     * testDateTime11 provider
     */
    public function DateTime11Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            11008,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            11012,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            11013,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            11014,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $dataArr[] = [
            11015,
            $dateTime2,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $dataArr[] = [
            11019,
            $dateTime2,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $dataArr[] = [
            11020,
            $dateTime2,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $dataArr[] = [
            11021,
            $dateTime2,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis . OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            11022,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis . OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            11026,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis . OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            11027,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis . OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            11028,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];


        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with DateTime, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime11Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime11(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT    => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VTODO     => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED, Vcalendar::COMPLETED ],
            Vcalendar::VJOURNAL  => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VFREEBUSY => [ Vcalendar::DTSTAMP, Vcalendar::DTSTART ],
            Vcalendar::VTIMEZONE => [ Vcalendar::LAST_MODIFIED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with DateTime, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime11Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testRecurDateTime11(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT   => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VTODO    => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VJOURNAL => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with DateTime, FREEBUSY
     *
     * @test
     * @dataProvider DateTime11Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testFreebusyDateTime11(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        $this->theFreebusyTestMethodDate(
            $case, $compsProps, $value, $params, $expectedGet, $expectedString
        );
        $this->theFreebusyTestMethodDateInterval(
            $case, $compsProps, $value, $params, $expectedGet, $expectedString
        );
    }

    /**
     * Testing VALUE DATE-TIME with DateTime, TRIGGER
     *
     * @test
     * @dataProvider DateTime11Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testTriggerDateTime11(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VALARM => [ Vcalendar::TRIGGER ],
        ];
        $this->theTriggerTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * testDateTime12 provider
     */
    public function DateTime12Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12008,
            array_merge( $timestampArr, [ Util::$LCtz => LTZ ] ),
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12012,
            array_merge( $timestampArr, [ Util::$LCtz => LTZ ] ),
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12013,
            array_merge( $timestampArr, [ Util::$LCtz => LTZ ] ),
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12014,
            array_merge( $timestampArr, [ Util::$LCtz => LTZ ] ),
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12015,
            $timestampArr,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12019,
            $timestampArr,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12020,
            $timestampArr,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12021,
            $timestampArr,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12022,
            array_merge( $timestampArr, [ Util::$LCtz => OFFSET ] ),
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis,  Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12026,
            array_merge( $timestampArr, [ Util::$LCtz => OFFSET ] ),
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12027,
            array_merge( $timestampArr, [ Util::$LCtz => OFFSET ] ),
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $timestampArr = [
            Util::$LCTIMESTAMP => $dateTime->getTimestamp()
        ];
        $dataArr[] = [
            12028,
            array_merge( $timestampArr, [ Util::$LCtz => OFFSET ] ),
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with timestamp, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime12Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime12(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT    => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VTODO     => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED, Vcalendar::COMPLETED ],
            Vcalendar::VJOURNAL  => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VFREEBUSY => [ Vcalendar::DTSTAMP, Vcalendar::DTSTART ],
            Vcalendar::VTIMEZONE => [ Vcalendar::LAST_MODIFIED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with timestamp, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime12Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testRecurDateTime12(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT   => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VTODO    => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VJOURNAL => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with timestamp, FREEBUSY
     *
     * @test
     * @dataProvider DateTime12Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testFreebusyDateTime12(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        $this->theFreebusyTestMethodDate(
            $case, $compsProps, $value, $params, $expectedGet, $expectedString
        );
        $this->theFreebusyTestMethodDateInterval(
            $case, $compsProps, $value, $params, $expectedGet, $expectedString
        );
    }

    /**
     * Testing VALUE DATE-TIME with timestamp, TRIGGER
     *
     * @test
     * @dataProvider DateTime12Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testTriggerDateTime12(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VALARM => [ Vcalendar::TRIGGER ],
        ];
        $this->theTriggerTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * testDateTime13 provider
     */
    public function DateTime13Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DateTimeFactory::factory( DATEYmd,  LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, TZ2 );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd,  Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dataArr[] = [
            13006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => Vcalendar::UTC
        ];
        $dataArr[] = [
            13015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => Vcalendar::UTC
        ];
        $dataArr[] = [
            13019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => Vcalendar::UTC
        ];
        $dataArr[] = [
            13020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => Vcalendar::UTC
        ];
        $dataArr[] = [
            13021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];


        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            13028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with short assoc array, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime13Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime13(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT    => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VTODO     => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED, Vcalendar::COMPLETED ],
            Vcalendar::VJOURNAL  => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VFREEBUSY => [ Vcalendar::DTSTAMP, Vcalendar::DTSTART ],
            Vcalendar::VTIMEZONE => [ Vcalendar::LAST_MODIFIED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with short assoc array, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime13Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testRecurDateTime13(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT   => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VTODO    => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VJOURNAL => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with short assoc array, FREEBUSY
     *
     * @test
     * @dataProvider DateTime13Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testFreebusyDateTime13(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        if( in_array( $case, [ 13001, 13005, 13007 ] )) { // n.a. covers by 13006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theFreebusyTestMethodDate(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
            $this->theFreebusyTestMethodDateInterval(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
        }
    }

    /**
     * Testing VALUE DATE-TIME with short assoc array, TRIGGER
     *
     * @test
     * @dataProvider DateTime13Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testTriggerDateTime13(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VALARM => [ Vcalendar::TRIGGER ],
        ];
        if( in_array( $case, [ 13001, 13005, 13007 ] )) { // n.a. covers by 13006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theTriggerTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }
    }

    /**
     * testDateTime14 provider
     */
    public function DateTime14Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            14001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, TZ2 );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            14005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
        ];
        $dateTime2 = clone $dateTime;
        $dataArr[] = [
            14006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            14007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => LTZ,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            14008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => LTZ,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            14012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => LTZ,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            14013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => LTZ,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            14014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => Vcalendar::UTC
        ];
        $dataArr[] = [
            14015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => Vcalendar::UTC
        ];
        $dataArr[] = [
            14019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => Vcalendar::UTC
        ];
        $dataArr[] = [
            14020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => Vcalendar::UTC
        ];
        $dataArr[] = [
            14021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];


        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz   => OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $y  = $dateTime2->format( 'Y' );
        $m  = $dateTime2->format( 'm' );
        $d  = $dateTime2->format( 'd' );
        $h  = $dateTime2->format( 'H' );
        $i  = $dateTime2->format( 'i' );
        $dataArr[] = [
            14022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz   => OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            14026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            14027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz   => OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            14028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with full assoc array, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime14Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime14(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT    => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VTODO     => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED, Vcalendar::COMPLETED ],
            Vcalendar::VJOURNAL  => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VFREEBUSY => [ Vcalendar::DTSTAMP, Vcalendar::DTSTART ],
            Vcalendar::VTIMEZONE => [ Vcalendar::LAST_MODIFIED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with full assoc array, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime14Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testRecurDateTime14(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT   => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VTODO    => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VJOURNAL => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with full assoc array, FREEBUSY
     *
     * @test
     * @dataProvider DateTime14Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testFreebusyDateTime14(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        if( in_array( $case, [ 14001, 14005, 14007 ] )) { // n.a. covers by 14006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theFreebusyTestMethodDate(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
            $this->theFreebusyTestMethodDateInterval(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
        }
    }

    /**
     * Testing VALUE DATE-TIME with full assoc array, TRIGGER
     *
     * @test
     * @dataProvider DateTime14Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testTriggerDateTime14(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VALARM => [ Vcalendar::TRIGGER ],
        ];
        if( in_array( $case, [ 14001, 14005, 14007 ] )) { // n.a. covers by 14006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theTriggerTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }
    }

    /**
     * testDateTime15 provider
     */
    public function DateTime15Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, TZ2 );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            LTZ
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            LTZ
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            LTZ
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            LTZ
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            Vcalendar::UTC
        ];
        $dataArr[] = [
            15015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            Vcalendar::UTC
        ];
        $y  = $dateTime->format( 'Y' );
        $m  = $dateTime->format( 'm' );
        $d  = $dateTime->format( 'd' );
        $h  = $dateTime->format( 'H' );
        $i  = $dateTime->format( 'i' );
        $dataArr[] = [
            15019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            Vcalendar::UTC
        ];
        $dataArr[] = [
            15020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            Vcalendar::UTC
        ];
        $dataArr[] = [
            15021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            OFFSET
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            15028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with short array, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime15Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime15(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT    => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VTODO     => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED, Vcalendar::COMPLETED ],
            Vcalendar::VJOURNAL  => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VFREEBUSY => [ Vcalendar::DTSTAMP, Vcalendar::DTSTART ],
            Vcalendar::VTIMEZONE => [ Vcalendar::LAST_MODIFIED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with short array, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime15Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testRecurDateTime15(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT   => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VTODO    => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VJOURNAL => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with short array, FREEBUSY
     *
     * @test
     * @dataProvider DateTime15Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testFreebusyDateTime15(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        if( in_array( $case, [ 15001, 15005, 15007 ] )) { // n.a. covers by 15006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theFreebusyTestMethodDate(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
            $this->theFreebusyTestMethodDateInterval(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
        }
    }

    /**
     * Testing VALUE DATE-TIME with short array, TRIGGER
     *
     * @test
     * @dataProvider DateTime15Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testTriggerDateTime15(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VALARM => [ Vcalendar::TRIGGER ],
        ];
        if( in_array( $case, [ 15001, 15005, 15007 ] )) { // n.a. covers by 15006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theTriggerTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }
    }

    /**
     * testDateTime16 provider
     */
    public function DateTime16Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, TZ2 );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
        ];
        $dataArr[] = [
            16006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            LTZ,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            LTZ,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            LTZ,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            LTZ,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            Vcalendar::UTC,
        ];
        $dataArr[] = [
            16015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            Vcalendar::UTC,
        ];
        $dataArr[] = [
            16019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            Vcalendar::UTC,
        ];
        $dataArr[] = [
            16020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            Vcalendar::UTC,
        ];
        $dataArr[] = [
            16021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];


        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            OFFSET,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            OFFSET,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            OFFSET,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' ),
            OFFSET,
        ];
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            16028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with full array, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime16Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime16(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT    => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VTODO     => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED, Vcalendar::COMPLETED ],
            Vcalendar::VJOURNAL  => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VFREEBUSY => [ Vcalendar::DTSTAMP, Vcalendar::DTSTART ],
            Vcalendar::VTIMEZONE => [ Vcalendar::LAST_MODIFIED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with full array, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime16Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testRecurDateTime16(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT   => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VTODO    => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VJOURNAL => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with full array, FREEBUSY
     *
     * @test
     * @dataProvider DateTime16Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testFreebusyDateTime16(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        if( in_array( $case, [ 16001, 16005, 16007 ] )) { // n.a. covers by 16006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theFreebusyTestMethodDate(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
            $this->theFreebusyTestMethodDateInterval(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
        }
    }

    /**
     * Testing VALUE DATE-TIME with full array, TRIGGER
     *
     * @test
     * @dataProvider DateTime16Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testTriggerDateTime16(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VALARM => [ Vcalendar::TRIGGER ],
        ];
        if( in_array( $case, [ 16001, 16005, 16007 ] )) { // n.a. covers by 16006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theTriggerTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }
    }

    /**
     * testDateTime17 provider
     */
    public function DateTime17Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DATEYmdTHis;
        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17001,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, TZ2 );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17005,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, Vcalendar::UTC );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17006,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17007,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17008,
            $dateTime . ' ' . LTZ,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17012,
            $dateTime . ' ' . LTZ,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17013,
            $dateTime . ' ' . LTZ,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17014,
            $dateTime . ' ' . LTZ,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            17015,
            $dateTime . ' ' . Vcalendar::UTC,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            17019,
            $dateTime . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            17020,
            $dateTime . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            17021,
            $dateTime . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17022,
            $dateTime . OFFSET,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17026,
            $dateTime . OFFSET,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17027,
            $dateTime . OFFSET,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            17028,
            $dateTime . OFFSET,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with full string datetime, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime17Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime17(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT    => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VTODO     => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED, Vcalendar::COMPLETED ],
            Vcalendar::VJOURNAL  => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VFREEBUSY => [ Vcalendar::DTSTAMP, Vcalendar::DTSTART ],
            Vcalendar::VTIMEZONE => [ Vcalendar::LAST_MODIFIED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with full string datetime, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime17Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testRecurDateTime17(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT   => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VTODO    => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VJOURNAL => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with full string datetime, FREEBUSY
     *
     * @test
     * @dataProvider DateTime17Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testFreebusyDateTime17(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        if( in_array( $case, [ 17001, 17005, 17007 ] )) { // n.a. covers by 17006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theFreebusyTestMethodDate(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
            $this->theFreebusyTestMethodDateInterval(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
        }
    }

    /**
     * Testing VALUE DATE-TIME with full string datetime, TRIGGER
     *
     * @test
     * @dataProvider DateTime17Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testTriggerDateTime17(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VALARM => [ Vcalendar::TRIGGER ],
        ];
        if( in_array( $case, [ 17001, 17005, 17007 ] )) { // n.a. covers by 17006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theTriggerTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }
    }

    /**
     * testDateTime18 provider
     */
    public function DateTime18Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime  = DATEYmd;

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18001,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, TZ2 );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18005,
            $dateTime,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            18006,
            $dateTime,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18007,
            $dateTime,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18008,
            DATEYmd . ' ' . LTZ,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18012,
            DATEYmd . ' ' . LTZ,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18013,
            DATEYmd . ' ' . LTZ,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18014,
            DATEYmd . ' ' . LTZ,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            18015,
            DATEYmd . ' ' . Vcalendar::UTC,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            18019,
            DATEYmd . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            18020,
            DATEYmd . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            18021,
            DATEYmd . ' ' . Vcalendar::UTC,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( DATEYmd, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18022,
            DATEYmd . OFFSET,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmd, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18026,
            DATEYmd . OFFSET,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmd, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18027,
            DATEYmd . OFFSET,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmd, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, Vcalendar::UTC );
        $dataArr[] = [
            18028,
            DATEYmd . OFFSET,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with short string datetime, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime18Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime18(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT    => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VTODO     => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED, Vcalendar::COMPLETED ],
            Vcalendar::VJOURNAL  => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VFREEBUSY => [ Vcalendar::DTSTAMP, Vcalendar::DTSTART ],
            Vcalendar::VTIMEZONE => [ Vcalendar::LAST_MODIFIED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with short string datetime, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime18Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testRecurDateTime18(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT   => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VTODO    => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
            Vcalendar::VJOURNAL => [ Vcalendar::EXRULE, Vcalendar::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with short string datetime, FREEBUSY
     *
     * @test
     * @dataProvider DateTime18Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testFreebusyDateTime18(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        if( in_array( $case, [ 18001, 18005, 18007 ] )) { // n.a. covers by 18006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theFreebusyTestMethodDate(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
            $this->theFreebusyTestMethodDateInterval(
                $case, $compsProps, $value, $params, $expectedGet, $expectedString
            );
        }
    }

    /**
     * Testing VALUE DATE-TIME with short string datetime, TRIGGER
     *
     * @test
     * @dataProvider DateTime18Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testTriggerDateTime18(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VALARM => [ Vcalendar::TRIGGER ],
        ];
        if( in_array( $case, [ 18001, 18005, 18007 ] )) { // n.a. covers by 18006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theTriggerTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }
    }

    /**
     * testDateTime19 provider
     */
    public function DateTime19Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, TZ2 );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dataArr[] = [
            19006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];


        $dateTime = DateTimeFactory::factory( DATEYmd, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, TZ2 );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => TZ2
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC  );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => Vcalendar::UTC
        ];
        $dataArr[] = [
            19013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz   => Vcalendar::UTC
        ];
        $dataArr[] = [
            19015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $dataArr[] = [
            19019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime2 = clone $dateTime;
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime2 ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, Vcalendar::UTC );
        $dataArr[] = [
            19021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];


        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmd, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            19028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with short assoc array as args, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime19Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime19(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT    => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VTODO     => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED, Vcalendar::COMPLETED ],
            Vcalendar::VJOURNAL  => [ Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED ],
            Vcalendar::VFREEBUSY => [ Vcalendar::DTSTAMP, Vcalendar::DTSTART ],
            Vcalendar::VTIMEZONE => [ Vcalendar::LAST_MODIFIED ],
        ];
        $this->theTestMethod2( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with short assoc array as args, TRIGGER
     *
     * @test
     * @dataProvider DateTime19Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testTriggerDateTime19(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VALARM => [ Vcalendar::TRIGGER ],
        ];
        if( ! in_array( $case, [ 19006, 19015, 19019, 19020, 190021 ] )) { // n.a.
            $this->assertTrue( true );
        }
        else {
            $this->theTriggerTestMethod2( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }
    }

    /**
     * testDateTime20 provider
     */
    public function DateTime20Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20001,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, TZ2 );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20005,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
        ];
        $dataArr[] = [
            20006,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20007,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20008,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20012,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20013,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, LTZ );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => LTZ
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20014,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => Vcalendar::UTC
        ];
        $dataArr[] = [
            20015,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $dataArr[] = [
            20019,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $dataArr[] = [
            20020,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, Vcalendar::UTC );
        $dataArr[] = [
            20021,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];


        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20022,
            $arrayDate,
            [],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20026,
            $arrayDate,
            [ Vcalendar::TZID => TZ2 ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20027,
            $arrayDate,
            [ Vcalendar::TZID => Vcalendar::UTC ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis, OFFSET );
        $arrayDate = [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
            Util::$LCtz    => OFFSET
        ];
        $dateTime = DateTimeFactory::setDateTimeTimeZone( $dateTime, Vcalendar::UTC );
        $dataArr[] = [
            20028,
            $arrayDate,
            [ Vcalendar::TZID => OFFSET ],
            [
                Util::$LCvalue  => $this->getDateTimeAsArray( $dateTime ),
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime, Vcalendar::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with full assoc array as args, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime20Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateTime20(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VEVENT    => [
                Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED
            ],
            Vcalendar::VTODO     => [
                Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED, Vcalendar::COMPLETED
            ],
            Vcalendar::VJOURNAL  => [
                Vcalendar::DTSTAMP, Vcalendar::LAST_MODIFIED, Vcalendar::CREATED
            ],
            Vcalendar::VFREEBUSY => [
                Vcalendar::DTSTAMP, Vcalendar::DTSTART
            ],
            Vcalendar::VTIMEZONE => [ Vcalendar::LAST_MODIFIED ],
        ];
        $this->theTestMethod2( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with full assoc array as args, TRIGGER
     *
     * @test
     * @dataProvider DateTime20Provider
     * @param int    $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testTriggerDateTime20(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VALARM => [ Vcalendar::TRIGGER ],
        ];
        if( ! in_array( $case, [ 20006, 20015, 20019, 20020, 20021 ] )) { // n.a.
            $this->assertTrue( true );
        }
        else {
            $this->theTriggerTestMethod2( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }
    }

}
