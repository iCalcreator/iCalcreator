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

use Exception;
use DateTime;
use DateTimeImmutable;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\RecurFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class DateTest, testing DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
 *
 * @since  2.41.4 - 2022-01-18
 */
class DateTimeUTCTest extends DtBase
{

    private static string $ERRFMT = "Error %sin case #%s, %s <%s>->%s";

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
     * The recur DATETIME test method , EXRULE + RRULE
     *
     * @param int     $case
     * @param mixed[] $compsProps
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    public function theRecurTestMethod(
        int    $case,
        array  $compsProps,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        $calendar1 = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            if( IcalInterface::AVAILABLE ===  $theComp ) {
                $comp = $calendar1->newVavailability()->{$newMethod}();
            }
            else {
                $comp = $calendar1->{$newMethod}();
            }
            $comp->setDtstart( $value, $params );

            foreach( $props as $x2 => $propName ) {
                $getMethod    = StringFactory::getGetMethodName( $propName );
                $createMethod = StringFactory::getCreateMethodName( $propName );
                $deleteMethod = StringFactory::getDeleteMethodName( $propName );
                $setMethod    = StringFactory::getSetMethodName( $propName );

                $recurSet = [
                    IcalInterface::FREQ       => IcalInterface::YEARLY,
                    IcalInterface::UNTIL      => (( $value instanceof DateTime ) ? clone $value : $value ),
                    IcalInterface::INTERVAL   => 2,
                    IcalInterface::BYSECOND   => [ 1, 2, 3 ],
                    IcalInterface::BYMINUTE   => [ 12, 23, 45 ],
                    IcalInterface::BYHOUR     => [ 3, 5, 7 ] ,
                    IcalInterface::BYDAY      => [ IcalInterface::DAY => IcalInterface::MO ],
                    IcalInterface::BYMONTHDAY => [ -1 ],
                    IcalInterface::BYYEARDAY  => [ 100, 200, 300 ],
                    IcalInterface::BYWEEKNO   => [ 20, 39, 40 ],
                    IcalInterface::BYMONTH    => [ 1, 2, 3, 4, 5, 7, 8, 9, 10, 11 ],
                    IcalInterface::BYSETPOS   => [ 1, 2, 3, 4, 5 ],
                    IcalInterface::WKST       => IcalInterface::SU
                ];
                $comp->{$setMethod}( $recurSet );

                $getValue = $comp->{$getMethod}( true );

                $this->assertEquals(
                    $expectedGet[Util::$LCvalue],
                    $getValue[Util::$LCvalue][IcalInterface::UNTIL],
                    sprintf( self::$ERRFMT, null, $case . "-r{$x2}-1", __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    substr( $expectedString, 1 ),
                    trim( StringFactory::between( 'UNTIL=', ';INTERVAL', $comp->{$createMethod}())),
                    sprintf( self::$ERRFMT, null, $case . "-r{$x2}-2", __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case . "-r{$x2}-3", __FUNCTION__, $theComp, $getMethod )
                );
                $comp->{$setMethod}( $recurSet );
            } // edn foreach
        } // end foreach
        $calendar1Str = $calendar1->createCalendar();
        $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], null, $calendar1Str );
        $createString = str_replace( '\,', ',', $createString );
        if( str_starts_with( $expectedString, ':' ) ) { // opt excl lead ':'
            $expectedString = substr( $expectedString, 1 );
        }
        $this->assertNotFalse(
            strpos( $createString, $expectedString ),
            sprintf( self::$ERRFMT, null, $case . '-r-5', __FUNCTION__, 'Vcalendar', 'createComponent' )
        );

        $this->parseCalendarTest( $case, $calendar1, $expectedString );
    }

    /**
     * The FREEBUSY DATETIME/DATETIME test method
     *
     * @param int     $case
     * @param mixed[] $compsProps
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function theFreebusyTestMethodDate(
        int    $case,
        array  $compsProps,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        $calendar1 = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $calendar1->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = StringFactory::getGetMethodName( $propName );
                $createMethod = StringFactory::getCreateMethodName( $propName );
                $deleteMethod = StringFactory::getDeleteMethodName( $propName );
                $setMethod    = StringFactory::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                $comp->{$setMethod}( IcalInterface::BUSY, [$value, $value] );

                $getValue = $comp->{$getMethod}( null, true );
                $this->assertEquals(
                    $expectedGet[Util::$LCvalue],
                    $getValue[Util::$LCvalue][0][0],
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    substr( $expectedString, 1 ),
                    trim( StringFactory::between( IcalInterface::BUSY . ':', '/', $comp->{$createMethod}())),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                );
                $comp->{$setMethod}( IcalInterface::BUSY, [$value, $value] );
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
     * @param int     $case
     * @param mixed[] $compsProps
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function theFreebusyTestMethodDateInterval(
        int    $case,
        array  $compsProps,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        $calendar1 = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $calendar1->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = StringFactory::getGetMethodName( $propName );
                $createMethod = StringFactory::getCreateMethodName( $propName );
                $deleteMethod = StringFactory::getDeleteMethodName( $propName );
                $setMethod    = StringFactory::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                $comp->{$setMethod}( IcalInterface::BUSY, [ $value, 'P1D' ] );

                $getValue = $comp->{$getMethod}( null, true );
                $this->assertEquals(
                    $expectedGet[Util::$LCvalue],
                    $getValue[Util::$LCvalue][0][0],
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    substr( $expectedString, 1 ),
                    trim( StringFactory::between( IcalInterface::BUSY . ':', '/', $comp->{$createMethod}())),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                );
                $comp->{$setMethod}( IcalInterface::BUSY, [$value, $value] );
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
     * @param int     $case
     * @param mixed[] $compsProps
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function theTriggerTestMethod(
        int    $case,
        array  $compsProps,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        $calendar1 = new Vcalendar();
        $e         = $calendar1->newVevent();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $e->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = StringFactory::getGetMethodName( $propName );
                $createMethod = StringFactory::getCreateMethodName( $propName );
                $deleteMethod = StringFactory::getDeleteMethodName( $propName );
                $setMethod    = StringFactory::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                $comp->{$setMethod}( $value, [ IcalInterface::VALUE => IcalInterface::DATE_TIME] );

                $getValue = $comp->{$getMethod}( true );
                $this->assertEquals(
                    $expectedGet[Util::$LCvalue],
                    $getValue[Util::$LCvalue],
                    sprintf( self::$ERRFMT, null, $case . '-1', __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    strtoupper( $propName ) . ';VALUE=DATE-TIME' . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    sprintf( self::$ERRFMT, null, $case . '-2', __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case . '-3', __FUNCTION__, $theComp, $getMethod )
                );
                $comp->{$setMethod}( $value, [ IcalInterface::VALUE => IcalInterface::DATE_TIME] );
            }
        } // end foreach
        $calendar1Str = $calendar1->createCalendar();
        $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], null, $calendar1Str );
        $createString = str_replace( '\,', ',', $createString );
        $this->assertNotFalse(
            strpos( $createString, $expectedString ),
            sprintf( self::$ERRFMT, null, $case . '-5', __FUNCTION__, 'Vcalendar', 'createComponent' )
        );

        $this->parseCalendarTest( $case, $calendar1, $expectedString );
    }

    /**
     * The TRIGGER DATETIME args test method
     *
     * @param int     $case
     * @param mixed[] $compsProps
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function theTriggerTestMethod2(
        int    $case,
        array  $compsProps,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $keys = [];
        if( empty( $keys )) {
            $keys = [
                RecurFactory::$LCYEAR, RecurFactory::$LCMONTH, RecurFactory::$LCDAY,
                RecurFactory::$LCHOUR, RecurFactory::$LCMIN,   RecurFactory::$LCSEC
            ];
        }
        $calendar1 = new Vcalendar();
        $e         = $calendar1->newVevent();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $e->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = StringFactory::getGetMethodName( $propName );
                $createMethod = StringFactory::getCreateMethodName( $propName );
                $deleteMethod = StringFactory::getDeleteMethodName( $propName );
                $setMethod    = StringFactory::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                foreach( $keys as $key ) {
                    ${$key} = $value[$key] ?? null;
                }
                $comp->{$setMethod}( ${RecurFactory::$LCYEAR}, ${RecurFactory::$LCMONTH}, ${RecurFactory::$LCDAY},
                                     ${RecurFactory::$LCHOUR}, ${RecurFactory::$LCMIN}, ${RecurFactory::$LCSEC},
                                     [ IcalInterface::VALUE => IcalInterface::DATE_TIME] );

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
                $comp->{$setMethod}( ${RecurFactory::$LCYEAR}, ${RecurFactory::$LCMONTH}, ${RecurFactory::$LCDAY},
                                     ${RecurFactory::$LCHOUR}, ${RecurFactory::$LCMIN}, ${RecurFactory::$LCSEC},
                                     [ IcalInterface::VALUE => IcalInterface::DATE_TIME] );
            } // end foreach
        } // end foreach
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
     *
     * @return mixed[]
     * @throws Exception
     */
    public function DateTime11Provider() : array
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DateTimeFactory::factory( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            11008,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = clone $dateTime;
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            11012,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            11013,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis . ' ' . LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            11014,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . ' ' . IcalInterface::UTC );
        $dateTime2 = clone $dateTime;
        $dataArr[] = [
            11015,
            $dateTime2,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmdTHis, IcalInterface::UTC );
        $dataArr[] = [
            11019,
            $dateTime2,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmdTHis, IcalInterface::UTC );
        $dataArr[] = [
            11020,
            $dateTime2,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmdTHis, IcalInterface::UTC );
        $dataArr[] = [
            11021,
            $dateTime2,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = new DateTimeImmutable( DATEYmdTHis . OFFSET );
        $dateTime2 = clone $dateTime;
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            11022,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis . OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            11026,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis . OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            11027,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime  = DateTimeFactory::factory( DATEYmdTHis . OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( clone $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            11028,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with DateTime, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime11Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testDateTime11( int $case, mixed $value, mixed $params, array $expectedGet, string $expectedString ) : void
    {
        static $compsProps = [
            IcalInterface::VEVENT        => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::VTODO         => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED, IcalInterface::COMPLETED ],
            IcalInterface::VJOURNAL      => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::VFREEBUSY     => [ IcalInterface::DTSTAMP, IcalInterface::DTSTART ],
            IcalInterface::VTIMEZONE     => [ IcalInterface::LAST_MODIFIED ],
            IcalInterface::PARTICIPANT   => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::AVAILABLE     => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::VAVAILABILITY => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with DateTime, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime11Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testRecurDateTime11( int $case, mixed $value, mixed $params, array $expectedGet, string $expectedString ) : void
    {
        static $compsProps = [
            IcalInterface::VEVENT    => [ IcalInterface::EXRULE, IcalInterface::RRULE ],
            IcalInterface::VTODO     => [ IcalInterface::EXRULE, IcalInterface::RRULE ],
            IcalInterface::VJOURNAL  => [ IcalInterface::EXRULE, IcalInterface::RRULE ],
            IcalInterface::AVAILABLE => [ IcalInterface::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with DateTime, FREEBUSY
     *
     * @test
     * @dataProvider DateTime11Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testFreebusyDateTime11(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $compsProps = [
            IcalInterface::VFREEBUSY => [ IcalInterface::FREEBUSY ],
        ];
        $this->theFreebusyTestMethodDate(
            $case, $compsProps, clone $value, $params, $expectedGet, $expectedString
        );
        $this->theFreebusyTestMethodDateInterval(
            $case, $compsProps, clone $value, $params, $expectedGet, $expectedString
        );
    }

    /**
     * Testing VALUE DATE-TIME with DateTime, TRIGGER
     *
     * @test
     * @dataProvider DateTime11Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testTriggerDateTime11(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $compsProps = [
            IcalInterface::VALARM => [ IcalInterface::TRIGGER ],
        ];
        $this->theTriggerTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }


    /**
     * testDateTime17 provider
     *
     * @return mixed[]
     * @throws Exception
     */
    public function DateTime17Provider() : array
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime = DATEYmdTHis;
        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            17001,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, TZ2 );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17005,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17006,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17007,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17008,
            $dateTime . ' ' . LTZ,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17012,
            $dateTime . ' ' . LTZ,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17013,
            $dateTime . ' ' . LTZ,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17014,
            $dateTime . ' ' . LTZ,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            17015,
            $dateTime . ' ' . IcalInterface::UTC,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            17019,
            $dateTime . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            17020,
            $dateTime . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            17021,
            $dateTime . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17022,
            $dateTime . OFFSET,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17026,
            $dateTime . OFFSET,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17027,
            $dateTime . OFFSET,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            17028,
            $dateTime . OFFSET,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with full string datetime, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime17Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testDateTime17(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $compsProps = [
            IcalInterface::VEVENT        => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::VTODO         => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED, IcalInterface::COMPLETED ],
            IcalInterface::VJOURNAL      => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::VFREEBUSY     => [ IcalInterface::DTSTAMP, IcalInterface::DTSTART ],
            IcalInterface::VTIMEZONE     => [ IcalInterface::LAST_MODIFIED ],
            IcalInterface::PARTICIPANT   => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::AVAILABLE     => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::VAVAILABILITY => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with full string datetime, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime17Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testRecurDateTime17(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $compsProps = [
            IcalInterface::VEVENT    => [ IcalInterface::EXRULE, IcalInterface::RRULE ],
            IcalInterface::VTODO     => [ IcalInterface::EXRULE, IcalInterface::RRULE ],
            IcalInterface::VJOURNAL  => [ IcalInterface::EXRULE, IcalInterface::RRULE ],
            IcalInterface::AVAILABLE => [ IcalInterface::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with full string datetime, FREEBUSY
     *
     * @test
     * @dataProvider DateTime17Provider
     * @param int $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testFreebusyDateTime17(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $compsProps = [
            IcalInterface::VFREEBUSY => [ IcalInterface::FREEBUSY ],
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
     * @param int $case
     * @param mixed  $value
     * @param mixed  $params
     * @param array $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testTriggerDateTime17(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $compsProps = [
            IcalInterface::VALARM => [ IcalInterface::TRIGGER ],
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
     * @throws Exception
     */
    public function DateTime18Provider() : array
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $dateTime  = DATEYmd;

        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            18001,
            $dateTime,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, TZ2 );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            18005,
            $dateTime,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            18006,
            $dateTime,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            18007,
            $dateTime,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            18008,
            DATEYmd . ' ' . LTZ,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            18012,
            DATEYmd . ' ' . LTZ,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            18013,
            DATEYmd . ' ' . LTZ,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, LTZ );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            18014,
            DATEYmd . ' ' . LTZ,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            18015,
            DATEYmd . ' ' . IcalInterface::UTC,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            18019,
            DATEYmd . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            18020,
            DATEYmd . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( $dateTime, IcalInterface::UTC );
        $dataArr[] = [
            18021,
            DATEYmd . ' ' . IcalInterface::UTC,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];


        $dateTime2 = DateTimeFactory::factory( DATEYmd, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            18022,
            DATEYmd . OFFSET,
            [],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmd, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            18026,
            DATEYmd . OFFSET,
            [ IcalInterface::TZID => TZ2 ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmd, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            18027,
            DATEYmd . OFFSET,
            [ IcalInterface::TZID => IcalInterface::UTC ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        $dateTime2 = DateTimeFactory::factory( DATEYmd, OFFSET );
        $dateTime2 = DateTimeFactory::setDateTimeTimeZone( $dateTime2, IcalInterface::UTC );
        $dataArr[] = [
            18028,
            DATEYmd . OFFSET,
            [ IcalInterface::TZID => OFFSET ],
            [
                Util::$LCvalue  => $dateTime2,
                Util::$LCparams => []
            ],
            $this->getDateTimeAsCreateLongString( $dateTime2, IcalInterface::UTC )
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATE-TIME with short string datetime, DTSTAMP, LAST_MODIFIED, CREATED, COMPLETED, DTSTART (VFREEBUSY)
     *
     * @test
     * @dataProvider DateTime18Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testDateTime18(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $compsProps = [
            IcalInterface::VEVENT        => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::VTODO         => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED, IcalInterface::COMPLETED ],
            IcalInterface::VJOURNAL      => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::VFREEBUSY     => [ IcalInterface::DTSTAMP, IcalInterface::DTSTART ],
            IcalInterface::VTIMEZONE     => [ IcalInterface::LAST_MODIFIED ],
            IcalInterface::PARTICIPANT   => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::AVAILABLE     => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
            IcalInterface::VAVAILABILITY => [ IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED, IcalInterface::CREATED ],
        ];
        $this->theTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with short string datetime, (EXRULE+)RRULE
     *
     * @test
     * @dataProvider DateTime18Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testRecurDateTime18(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $compsProps = [
            IcalInterface::VEVENT    => [ IcalInterface::EXRULE, IcalInterface::RRULE ],
            IcalInterface::VTODO     => [ IcalInterface::EXRULE, IcalInterface::RRULE ],
            IcalInterface::VJOURNAL  => [ IcalInterface::EXRULE, IcalInterface::RRULE ],
            IcalInterface::AVAILABLE => [ IcalInterface::RRULE ],
        ];
        $this->theRecurTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
    }

    /**
     * Testing VALUE DATE-TIME with short string datetime, FREEBUSY
     *
     * @test
     * @dataProvider DateTime18Provider
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testFreebusyDateTime18(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $compsProps = [
            IcalInterface::VFREEBUSY => [ IcalInterface::FREEBUSY ],
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
     * @param int     $case
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function testTriggerDateTime18(
        int    $case,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $compsProps = [
            IcalInterface::VALARM => [ IcalInterface::TRIGGER ],
        ];
        if( in_array( $case, [ 18001, 18005, 18007 ] )) { // n.a. covers by 18006 (UTC)
            $this->assertTrue( true );
        }
        else {
            $this->theTriggerTestMethod( $case, $compsProps, $value, $params, $expectedGet, $expectedString );
        }
    }
}
