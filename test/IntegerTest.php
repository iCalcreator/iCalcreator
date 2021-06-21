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

use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Exception;

/**
 * class IntegerTest, testing Integers in
 *    PERCENT-COMPLETE    VTODO
 *    PRIORITY            VEVENT and VTODO
 *    SEQUENCE            VEVENT, VTODO, or VJOURNAL
 *    REPEAT              (VEVENT) VALARM
 *
 * @since  2.27.14 - 2019-01-24
 */
class IntegerTest extends DtBase
{
    private static $ERRFMT = "Error %sin case #%s, %s <%s>->%s";
    private static $STCPAR = [ 'X-PARAM' => 'Y-vALuE' ];

    /**
     * integerTest provider
     */
    public function integerTestProvider()
    {
        $dataArr = [];

        $dataArr[] = [
            1,
            [
                Vcalendar::PERCENT_COMPLETE => [ Vcalendar::VTODO ],
                Vcalendar::PRIORITY         => [ Vcalendar::VEVENT, Vcalendar::VTODO ],
                Vcalendar::REPEAT           => [ Vcalendar::VALARM ],
            ],
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $value = null;
        $dataArr[] = [
            5,
            [
                Vcalendar::SEQUENCE => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ],
            ],
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => 0,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) .
            ':0'
        ];

        $value = 9;
        $dataArr[] = [
            9,
            [
                Vcalendar::PERCENT_COMPLETE => [ Vcalendar::VTODO ],
                Vcalendar::PRIORITY         => [ Vcalendar::VEVENT, Vcalendar::VTODO ],
                Vcalendar::SEQUENCE         => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ],
                Vcalendar::REPEAT           => [ Vcalendar::VALARM ],
            ],
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) .
            ':' . $value
        ];

        $value = 19;
        $dataArr[] = [
            19,
            [
                Vcalendar::SEQUENCE         => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ],
            ],
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) .
            ':' . $value
        ];

        $value = 109;
        $dataArr[] = [
            109,
            [
                Vcalendar::PERCENT_COMPLETE => [ Vcalendar::VTODO ],
            ],
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) .
            ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing integers
     *
     * @test
     * @dataProvider integerTestProvider
     * @param int    $case
     * @param array  $propComps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function integerTest(
        $case,
        $propComps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $c = new Vcalendar();
        foreach( $propComps as $propName => $theComps ) {
            $getMethod    = StringFactory::getGetMethodName( $propName );
            $createMethod = StringFactory::getCreateMethodName( $propName );
            $deleteMethod = StringFactory::getDeleteMethodName( $propName );
            $setMethod    = StringFactory::getSetMethodName( $propName );
            foreach( $theComps as $theComp ) {
                $newMethod = 'new' . $theComp;
                if( Vcalendar::VALARM == $theComp ) {
                    $comp      = $c->newVevent()->{$newMethod}();
                }
                else {
                    $comp      = $c->{$newMethod}();
                }
                try {
                    $comp->{$setMethod}( $value, $params );
                }
                catch( Exception $e ) {
                    $ok = false;
                    if(( Vcalendar::SEQUENCE == $propName ) && ( $value > 9 )) {
                        $ok = true;
                    }
                    elseif(( Vcalendar::PERCENT_COMPLETE == $propName ) && ( $value > 100 )) {
                        $ok = true;
                    }
                    $this->assertTrue( $ok );
                    return;
                }
                $getValue = $comp->{$getMethod}( true );
                if(( empty( $getValue[Util::$LCvalue] ) && Vcalendar::SEQUENCE == $propName )) {
                    $expectedGet[Util::$LCvalue]  = 0;
                    $expectedGet[Util::$LCparams] = self::$STCPAR;
                }
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                );
                $comp->{$setMethod}( $value, $params );
            } // end foreach
        } // end foreach

        $this->parseCalendarTest( $case, $c, $expectedString );
    }
}
