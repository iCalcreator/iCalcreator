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

use DateTime;
use Exception;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class DateTzTest, testing VALUE DATETIME for Standard/Daylight (always local time), also empty value, DTSTART
 *
 * @since  2.29.2 - 2019-06-28
 */
class DateTzTest extends DtBase
{
    private static $ERRFMT = "Error %sin case #%s, %s <%s>->%s";
    private static $STCPAR = [ 'X-PARAM' => 'Y-vALuE' ];

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
     * testDATEtz1 provider
     */
    public function DATEtz1Provider()
    {
        $dataArr = [];

        $value  = 'Europe/Stockholm';
        $params = self::$STCPAR;
        $dataArr[] = [
            101,
            Vcalendar::TZID,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            ':' .
            $value
        ];

        $value  = 'http://example.com/pub/calendars/jsmith/mytime.ics';
        $params = self::$STCPAR;
        $dataArr[] = [
            111,
            Vcalendar::TZURL,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            ':' .
            $value
        ];

        return $dataArr;
    }

    /**
     * Testing Vtimezone and TZID, TZURL
     *
     * @test
     * @dataProvider DATEtz1Provider
     * @param int    $case
     * @param string $propName
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testDATEtz1(
        $case,
        $propName,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $c = new Vcalendar();
        $v = $c->newVtimezone();

        $getMethod    = StringFactory::getGetMethodName( $propName );
        $createMethod = StringFactory::getCreateMethodName( $propName );
        $deleteMethod = StringFactory::getDeleteMethodName( $propName );
        $setMethod    = StringFactory::getSetMethodName( $propName );

        $v->{$setMethod}( $value, $params );
        $getValue = $v->{$getMethod}( true );
        $this->assertEquals(
            $expectedGet,
            $getValue,
            sprintf( self::$ERRFMT, null, $case . '-11', __FUNCTION__, 'Vtimezone', $getMethod )
        );
        $this->assertEquals(
            strtoupper( $propName ) . $expectedString,
            str_replace( "\r\n ", null, trim( $v->{$createMethod}() )),
            "create error in case #{$case}"
        );
        $v->{$deleteMethod}();
        $this->assertFalse(
            $v->{$getMethod}(),
            sprintf( self::$ERRFMT, '(after delete) ', $case . '-12', __FUNCTION__, 'Vtimezone', $getMethod )
        );
        $v->{$setMethod}( $value, $params );

        $this->parseCalendarTest( $case, $c, $expectedString );
    }

    /**
     * testDATEtz2 provider
     */
    public function DATEtz2Provider()
    {
        date_default_timezone_set( LTZ );

        $dataArr = [];

        $params = self::$STCPAR;
        $dataArr[] = [ // test set #200 empty
            200,
            Vcalendar::DTSTART,
            null,
            null,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $dateTime  = new DateTime( DATEYmd );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #211 DateTime
            211,
            Vcalendar::DTSTART,
            clone $dateTime,
            $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmd );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #212 DateTime
            212,
            Vcalendar::DTSTART,
            clone $dateTime,
            [ Vcalendar::VALUE => Vcalendar::DATE ] + $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmd );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #213 DateTime
            213,
            Vcalendar::DTSTART,
            clone $dateTime,
            [ Vcalendar::VALUE => Vcalendar::DATE_TIME ] + $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $dateTime  = new DateTime( DATEYmdTHis );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #221 DateTime
            221,
            Vcalendar::DTSTART,
            clone $dateTime,
            $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmdTHis );
        $dataArr[] = [ // test set #222 DateTime
            222,
            Vcalendar::DTSTART,
            clone $dateTime,
            self::$STCPAR + [ Vcalendar::VALUE => Vcalendar::DATE ],
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmdTHis );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #223 DateTime
            223,
            Vcalendar::DTSTART,
            clone $dateTime,
            [ Vcalendar::VALUE => Vcalendar::DATE_TIME ] + $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $dateTime  = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #231 DateTime
            231,
            Vcalendar::DTSTART,
            clone $dateTime,
            $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $dateTime  = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #232 DateTime
            232,
            Vcalendar::DTSTART,
            clone $dateTime,
            $params + [ Vcalendar::VALUE => Vcalendar::DATE ],
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $params = self::$STCPAR;
        $dataArr[] = [ // test set #233 DateTime
            233,
            Vcalendar::DTSTART,
            clone $dateTime,
            [ Vcalendar::VALUE => Vcalendar::DATE_TIME ] + $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $dateTime  = new DateTime( DATEYmdTHis . ' ' . Vcalendar::UTC );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #241 DateTime
            241,
            Vcalendar::DTSTART,
            clone $dateTime,
            $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . ' ' . Vcalendar::UTC );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #242 DateTime
            242,
            Vcalendar::DTSTART,
            clone $dateTime,
            [ Vcalendar::VALUE => Vcalendar::DATE ] + $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . ' ' . Vcalendar::UTC );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #243 DateTime
            243,
            Vcalendar::DTSTART,
            clone $dateTime,
            [ Vcalendar::VALUE => Vcalendar::DATE_TIME ] + $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $value     = '20170326020000';
        $dateTime  = new DateTime( '20170326020000' );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #261 string
            261,
            Vcalendar::DTSTART,
            $value,
            $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            ':20170326T020000'
        ];

        $localTz   = date_default_timezone_get();
        $dateTime  = new DateTime( '20170326020000 ' . $localTz  );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #262 DateTime
            262,
            Vcalendar::DTSTART,
            $dateTime,
            $params,
            [
                Util::$LCvalue  => clone $dateTime,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            ':20170326T020000'
        ];



        $value  = '+0300';
        $params = self::$STCPAR;
        $dataArr[] = [
            291,
            Vcalendar::TZOFFSETFROM,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            ':' .
            $value
        ];


        $value  = '-0700';
        $params = self::$STCPAR;
        $dataArr[] = [
            292,
            Vcalendar::TZOFFSETTO,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        $value  = 'CET';
        $params = self::$STCPAR;
        $dataArr[] = [
            293,
            Vcalendar::TZNAME,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATETIME for Standard/Daylight (always local time), also empty value, DTSTART
     *
     * @test
     * @dataProvider DATEtz2Provider
     * @param int    $case
     * @param string $propName
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testDATEtz2(
        $case,
        $propName,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $subCompProp = [
            Vcalendar::STANDARD,
            Vcalendar::DAYLIGHT
        ];
        if( $expectedGet[Util::$LCvalue] instanceof DateTime ) {
            $expectedGet[Util::$LCvalue] = $expectedGet[Util::$LCvalue]->format( DateTimeFactory::$YmdTHis );
        }
        $c = new Vcalendar();
        $v = $c->newVtimezone();
        foreach( $subCompProp as $theComp ) {
            $newMethod    = 'new' . $theComp;
            $comp         = $v->{$newMethod}();

            $getMethod    = StringFactory::getGetMethodName( $propName );
            $createMethod = StringFactory::getCreateMethodName( $propName );
            $deleteMethod = StringFactory::getDeleteMethodName( $propName );
            $setMethod    = StringFactory::getSetMethodName( $propName );

            $comp->{$setMethod}( $value, $params );
            if( Vcalendar::TZNAME == $propName ) {
                $getValue = $comp->{$getMethod}( null, true );
            }
            else {
                $getValue = $comp->{$getMethod}( true );
                unset( $getValue[Util::$LCparams][Util::$ISLOCALTIME] );
                if( $getValue[Util::$LCvalue] instanceof DateTime ) {
                    $getValue[Util::$LCvalue]    = $getValue[Util::$LCvalue]->format( DateTimeFactory::$YmdTHis );
                }
            }
            $this->assertEquals(
                $expectedGet,
                $getValue,
                sprintf( self::$ERRFMT, null, $case . '-21', __FUNCTION__, $theComp, $getMethod )
            );
            $this->assertEquals(
                strtoupper( $propName ) . $expectedString,
                trim( $comp->{$createMethod}() ),
                "create error in case #{$case}-22 {$theComp}::{$getMethod}"
            );
            $comp->{$deleteMethod}();
            $this->assertFalse(
                $comp->{$getMethod}(),
                sprintf( self::$ERRFMT, '(after delete) ', $case . '-23', __FUNCTION__, $theComp, $getMethod )
            );
            $comp->{$setMethod}( $value, $params );
        } // end foreach

        $this->parseCalendarTest( $case, $c, $expectedString );
    }
}
