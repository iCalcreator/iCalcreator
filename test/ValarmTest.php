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
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class ValarmTest, testing Integers in
 *    PERCENT-COMPLETE    VTODO
 *    PRIORITY            VEVENT and VTODO
 *    SEQUENCE            VEVENT, VTODO, or VJOURNAL
 *    REPEAT              (VEVENT) VALARM
 *
 * @since  2.27.14 - 2019-01-24
 */
class ValarmTest extends DtBase
{
    private static $ERRFMT = "Error %sin case #%s, %s <%s>->%s";
    private static $STCPAR = [ 'X-PARAM' => 'Y-vALuE' ];

    /**
     * valarmTest provider
     */
    public function valarmTestProvider()
    {
        $dataArr = [];

        // ACTION
        $dataArr[] = [
            11,
            Vcalendar::ACTION,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $value = Vcalendar::AUDIO; // "DISPLAY" / "EMAIL
        $dataArr[] = [
            12,
            Vcalendar::ACTION,
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) .
            ':' . $value
        ];

        // TRIGGER
        $dataArr[] = [
            21,
            Vcalendar::TRIGGER,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $value = 'P1D';
        $dataArr[] = [
            22,
            Vcalendar::TRIGGER,
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => DateIntervalFactory::factory( $value ),
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) .
            ':' . $value
        ];

        // DURATION
        $dataArr[] = [
            31,
            Vcalendar::DURATION,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $value = 'P1D';
        $dataArr[] = [
            32,
            Vcalendar::DURATION,
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => DateIntervalFactory::factory( $value ),
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) .
            ':' . $value
        ];

        // REPEAT
        $dataArr[] = [
            41,
            Vcalendar::REPEAT,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $value = 3;
        $dataArr[] = [
            42,
            Vcalendar::REPEAT,
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) .
            ':' . $value
        ];

        // ATTACH
        $getValue  = [
            Util::$LCvalue  => '',
            Util::$LCparams => []
        ];
        $dataArr[] = [
            51,
            Vcalendar::ATTACH,
            null,
            self::$STCPAR,
            $getValue,
            ':'
        ];

        $value  = 'CID:jsmith.part3.960817T083000.xyzMail@example.com';
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            52,
            Vcalendar::ATTACH,
            $value,
            $params,
            $getValue,
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // ATTACH
        $value  = 'ftp://example.com/pub/reports/r-960812.ps';
        $params = [ Vcalendar::FMTTYPE => 'application/postscript' ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            53,
            Vcalendar::ATTACH,
            $value,
            $params,
            $getValue,
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // DESCRIPTION
        $dataArr[] = [
            61,
            Vcalendar::DESCRIPTION,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $value  = 'Meeting to provide technical review for \'Phoenix\' design.\nHappy Face Conference Room. Phoenix design team MUST attend this meeting.\nRSVP to team leader.';
        $params = [
                Vcalendar::ALTREP   => 'This is an alternative representation',
                Vcalendar::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [
            62,
            Vcalendar::DESCRIPTION,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        // SUMMARY
        $dataArr[] = [
            71,
            Vcalendar::SUMMARY,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $value  = 'Department Party';
        $params = [
                Vcalendar::ALTREP   => 'This is an alternative representation',
                Vcalendar::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [
            72,
            Vcalendar::SUMMARY,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        // ATTENDEE
        $dataArr[] = [
            71,
            Vcalendar::ATTENDEE,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $value  = 'MAILTO:ildoit@example.com';
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $expectedString = trim( CalAddressFactory::outputFormatAttendee( [ $getValue ], true ));
        $expectedString = str_replace( Util::$CRLF . ' ' , null, $expectedString);
        $expectedString = str_replace( '\,', ',', $expectedString );
        $expectedString = substr( $expectedString, 8 );
        $dataArr[] = [
            72,
            Vcalendar::ATTENDEE,
            $value,
            $params,
            $getValue,
            $expectedString
        ];

        /*
         *  x-prop ??
         */

        return $dataArr;
    }

    /**
     * Testing Valarm
     *
     * @test
     * @dataProvider valarmTestProvider
     * @param int    $case
     * @param string $propName
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function valarmTest(
        $case,
        $propName,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $c  = new Vcalendar();
        foreach( [ $c->newVevent(), $c->newVtodo() ] as $comp  ) {
            $a1 = $comp->newValarm();

            $getMethod    = StringFactory::getGetMethodName( $propName );
            $createMethod = StringFactory::getCreateMethodName( $propName );
            $deleteMethod = StringFactory::getDeleteMethodName( $propName );
            $setMethod    = StringFactory::getSetMethodName( $propName );

            $a1->{$setMethod}( $value, $params );
            if( in_array( $propName, [ Vcalendar::ATTACH, Vcalendar::DESCRIPTION, Vcalendar::ATTENDEE ] ) ) {
                $getValue = $a1->{$getMethod}( null, true );
            }
            else {
                $getValue = $a1->{$getMethod}( true );
            }
            $this->assertEquals(
                $expectedGet,
                $getValue,
                sprintf( self::$ERRFMT, null, $case . '-1', __FUNCTION__, 'Valarm', $getMethod )
            );
            $actualString = $a1->{$createMethod}();
            $actualString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], null, $actualString );
            $actualString = str_replace( '\,', ',', $actualString );
            $this->assertEquals(
                strtoupper( $propName ) . $expectedString,
                trim( $actualString ),
                sprintf( self::$ERRFMT, null, $case . '-2', __FUNCTION__, 'Valarm', $createMethod )
            );
            $a1->{$deleteMethod}();
            $this->assertFalse(
                $a1->{$getMethod}(),
                sprintf( self::$ERRFMT, '(after delete) ', $case . '-3', __FUNCTION__, 'Valarm', $getMethod )
            );
            $a1->{$setMethod}( $value, $params );

            $this->parseCalendarTest( $case, $c, $expectedString );

            $compArr = [
                clone $a1,
                clone $a1
            ];

            $x = 1;
            while( $comp->deleteComponent( $x ) ) {
                $x += 1;
            }
            $this->assertTrue(
                ( 0 == $comp->countComponents() ),
                $case .  '-5 deleteComponent-error 2, has ' . $comp->countComponents()
            );
            // check components are set
            foreach( $compArr as $subComp ) {
                $comp->setComponent( $subComp );
            }
            // check number of components
            $this->assertTrue(
                ( count( $compArr ) == $comp->countComponents() ),
                $case .  '-6 setComponent-error 3, has ' . $comp->countComponents()
            );
        }
    }
}
