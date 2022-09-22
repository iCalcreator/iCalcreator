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

use DateTime;
use Exception;
use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;

/**
 * class DateTzTest, testing VALUE DATETIME for Standard/Daylight (always local time), also empty value, DTSTART
 *
 * @since  2.29.2 - 2019-06-28
 */
class DateTzTest extends DtBase
{
    /**
     * @var array|string[]
     */
    private
    static array  $STCPAR = [ 'X-PARAM' => 'Y-vALuE' ];

    /**
     * testDATEtz1 provider
     *
     * @return mixed[]
     */
    public function dateTzTest1Provider() : array
    {
        $dataArr = [];

        $value  = 'Europe/Stockholm';
        $params = self::$STCPAR;
        $dataArr[] = [
            101,
            IcalInterface::TZID,
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            Property::formatParams( $params ) .
            ':' .
            $value
        ];

        $value  = 'http://example.com/pub/calendars/jsmith/mytime.ics';
        $params = self::$STCPAR;
        $dataArr[] = [
            201,
            IcalInterface::TZURL,
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            Property::formatParams( $params ) .
            ':' .
            $value
        ];

        return $dataArr;
    }

    /**
     * Testing Vtimezone and TZID, TZURL
     *
     * @test
     * @dataProvider dateTzTest1Provider
     * @param int     $case
     * @param string  $propName
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    public function dateTzTest1(
        int    $case,
        string $propName,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        $c = new Vcalendar();
        $v = $c->newVtimezone();

        [ $createMethod, $deleteMethod, $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
        $this->assertFalse(
            $v->{$isMethod}(),
            sprintf( self::$ERRFMT, null, $case . '-11', __FUNCTION__, 'Vtimezone', $isMethod )
        );

        $v->{$setMethod}( $value, $params );
        $this->assertTrue(
            $v->{$isMethod}(),
            sprintf( self::$ERRFMT, null, $case . '-12', __FUNCTION__, 'Vtimezone', $isMethod )
        );

        $getValue = $v->{$getMethod}( true );
        $this->assertEquals(
            $expectedGet,
            $getValue,
            sprintf( self::$ERRFMT, null, $case . '-13', __FUNCTION__, 'Vtimezone', $getMethod )
        );
        $this->assertEquals(
            strtoupper( $propName ) . $expectedString,
            str_replace( "\r\n ", null, trim( $v->{$createMethod}())),
            "create error in case #{$case}"
        );
        $v->{$deleteMethod}();
        $this->assertFalse(
            $v->{$getMethod}(),
            sprintf( self::$ERRFMT, '(after delete) ', $case . '-14', __FUNCTION__, 'Vtimezone', $getMethod )
        );
        $v->{$setMethod}( $value, $params );

        $this->parseCalendarTest( $case, $c, $expectedString );
    }

    /**
     * dateTzTest2 provider, VALUE DATETIME
     *
     * @return mixed[]
     * @throws Exception
     */
    public function dateTzTest2Provider() : array
    {
        $dataArr = [];

        $params = self::$STCPAR;
        $dataArr[] = [ // test set #200 empty
            200,
            IcalInterface::DTSTART,
            null,
            null,
            Pc::factory(
                '',
                []
            ),
            ':'
        ];

        $dateTime  = new DateTime( DATEYmd );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #211 DateTime
            211,
            IcalInterface::DTSTART,
            clone $dateTime,
            $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmd );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #212 DateTime
            212,
            IcalInterface::DTSTART,
            clone $dateTime,
            [ IcalInterface::VALUE => IcalInterface::DATE ] + $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmd );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #213 DateTime
            213,
            IcalInterface::DTSTART,
            clone $dateTime,
            [ IcalInterface::VALUE => IcalInterface::DATE_TIME ] + $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $dateTime  = new DateTime( DATEYmdTHis );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #221 DateTime
            221,
            IcalInterface::DTSTART,
            clone $dateTime,
            $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmdTHis );
        $dataArr[] = [ // test set #222 DateTime
            222,
            IcalInterface::DTSTART,
            clone $dateTime,
            self::$STCPAR + [ IcalInterface::VALUE => IcalInterface::DATE ],
            Pc::factory(
                $dateTime,
                self::$STCPAR
            ),
            Property::formatParams( self::$STCPAR ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmdTHis );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #223 DateTime
            223,
            IcalInterface::DTSTART,
            clone $dateTime,
            [ IcalInterface::VALUE => IcalInterface::DATE_TIME ] + $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $dateTime  = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #231 DateTime
            231,
            IcalInterface::DTSTART,
            clone $dateTime,
            $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $dateTime  = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #232 DateTime
            232,
            IcalInterface::DTSTART,
            clone $dateTime,
            $params + [ IcalInterface::VALUE => IcalInterface::DATE ],
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmdTHis, DateTimeZoneFactory::factory( LTZ ));
        $params = self::$STCPAR;
        $dataArr[] = [ // test set #233 DateTime
            233,
            IcalInterface::DTSTART,
            clone $dateTime,
            [ IcalInterface::VALUE => IcalInterface::DATE_TIME ] + $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $dateTime  = new DateTime( DATEYmdTHis . ' ' . IcalInterface::UTC );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #241 DateTime
            241,
            IcalInterface::DTSTART,
            clone $dateTime,
            $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . ' ' . IcalInterface::UTC );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #242 DateTime
            242,
            IcalInterface::DTSTART,
            clone $dateTime,
            [ IcalInterface::VALUE => IcalInterface::DATE ] + $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];

        $dateTime  = new DateTime( DATEYmdTHis . ' ' . IcalInterface::UTC );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #243 DateTime
            243,
            IcalInterface::DTSTART,
            clone $dateTime,
            [ IcalInterface::VALUE => IcalInterface::DATE_TIME ] + $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            $this->getDateTimeAsCreateLongString( $dateTime )
        ];


        $value     = '20170326020000';
        $dateTime  = new DateTime( '20170326020000' );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #261 string
            261,
            IcalInterface::DTSTART,
            $value,
            $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            ':20170326T020000'
        ];

        $localTz   = date_default_timezone_get();
        $dateTime  = new DateTime( '20170326020000 ' . $localTz  );
        $params    = self::$STCPAR;
        $dataArr[] = [ // test set #262 DateTime
            262,
            IcalInterface::DTSTART,
            $dateTime,
            $params,
            Pc::factory(
                clone $dateTime,
                $params
            ),
            Property::formatParams( $params ) .
            ':20170326T020000'
        ];



        $value  = '+0300';
        $params = self::$STCPAR;
        $dataArr[] = [
            291,
            IcalInterface::TZOFFSETFROM,
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            Property::formatParams( $params ) .
            ':' .
            $value
        ];


        $value  = '-0700';
        $params = self::$STCPAR;
        $dataArr[] = [
            292,
            IcalInterface::TZOFFSETTO,
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            Property::formatParams( $params ) .
            ':' . $value
        ];

        $value  = 'CET';
        $params = self::$STCPAR;
        $dataArr[] = [
            293,
            IcalInterface::TZNAME,
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            Property::formatParams( $params ) .
            ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing VALUE DATETIME for Standard/Daylight (always local time), also empty value, DTSTART
     *
     * @test
     * @dataProvider dateTzTest2Provider
     * @param int     $case
     * @param string  $propName
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function dateTzTest2(
        int    $case,
        string $propName,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        static $subCompProp = [
            IcalInterface::STANDARD,
            IcalInterface::DAYLIGHT
        ];
        if( $expectedGet->value instanceof DateTime ) {
            $expectedGet->value = $expectedGet->value->format( DateTimeFactory::$YmdTHis );
        }
        $c = new Vcalendar();
        $v = $c->newVtimezone();
        foreach( $subCompProp as $theComp ) {
            $newMethod    = 'new' . $theComp;
            $comp         = $v->{$newMethod}();

            [ $createMethod, $deleteMethod, $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
            $this->assertFalse(
                $comp->{$isMethod}(),
                sprintf( self::$ERRFMT, null, $case . '-21', __FUNCTION__, $theComp, $isMethod )
            );

            $comp->{$setMethod}( $value, $params );
            $this->assertSame(
                ! empty( $value ),
                $comp->{$isMethod}(),
                sprintf( self::$ERRFMT, null, $case . '-22', __FUNCTION__, $theComp, $isMethod )
                    . ", exp " . empty( $value ) ? Vcalendar::FALSE : Vcalendar::TRUE
            );

            if( IcalInterface::TZNAME === $propName ) {
                $getValue = $comp->{$getMethod}( null, true );
            }
            else {
                $getValue = $comp->{$getMethod}( true );
                unset( $getValue->  params[IcalInterface::ISLOCALTIME] );
                if( $getValue->value instanceof DateTime ) {
                    $getValue->value = $getValue->value->format( DateTimeFactory::$YmdTHis );
                }
            }
            $this->assertEquals(
                $expectedGet,
                $getValue,
                sprintf( self::$ERRFMT, null, $case . '-23', __FUNCTION__, $theComp, $getMethod )
            );
            $this->assertEquals(
                strtoupper( $propName ) . $expectedString,
                trim( $comp->{$createMethod}()),
                sprintf( self::$ERRFMT, null, $case . '-24', __FUNCTION__, $theComp, $getMethod )
            );
            $comp->{$deleteMethod}();
            $this->assertFalse(
                $comp->{$getMethod}(),
                sprintf( self::$ERRFMT, '(after delete) ', $case . '-25', __FUNCTION__, $theComp, $getMethod )
            );
            $comp->{$setMethod}( $value, $params );
        } // end foreach

        $this->parseCalendarTest( $case, $c, $expectedString );
    }
}
