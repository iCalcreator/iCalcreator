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
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
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
    /**
     * @var string
     */
    private static string $ERRFMT = "Error %sin case #%s, %s <%s>->%s";

    /**
     * @var array|string[]
     */
    private static array $STCPAR = [ 'X-PARAM' => 'Y-vALuE' ];

    /**
     * valarmTest provider
     *
     * @return mixed[]
     */
    public function valarmTestProvider() : array
    {
        $dataArr = [];

        // UID, optional in Valarm, rfc7094
        $uid = bin2hex( random_bytes( 16 )); // i.e. 32
        $dataArr[] = [
            101,
            IcalInterface::UID,
            $uid,
            self::$STCPAR,
            [
                Util::$LCvalue  => $uid,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) . ':' . $uid
        ];

        // RELATED-TO
        $dataArr[] = [
            111,
            IcalInterface::RELATED_TO,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $params = [ IcalInterface::RELTYPE => IcalInterface::SNOOZE ] + self::$STCPAR;
        $dataArr[] = [
            112,
            IcalInterface::RELATED_TO,
            $uid,
            $params,
            [
                Util::$LCvalue  => $uid,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) . ':' . $uid
        ];

        // ACKNOWLEDGED
        $value     = DateTimeFactory::factory( null, IcalInterface::UTC );
        $dataArr[] = [
            121,
            IcalInterface::ACKNOWLEDGED,
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) .
                $this->getDateTimeAsCreateLongString( $value, IcalInterface::UTC )
        ];

        // ACTION
        $dataArr[] = [
            131,
            IcalInterface::ACTION,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $value = IcalInterface::AUDIO; // "DISPLAY" / "EMAIL
        $dataArr[] = [
            132,
            IcalInterface::ACTION,
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) . ':' . $value
        ];

        // TRIGGER
        $dataArr[] = [
            141,
            IcalInterface::TRIGGER,
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
            142,
            IcalInterface::TRIGGER,
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => DateIntervalFactory::factory( $value ),
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) . ':' . $value
        ];

        // DURATION
        $dataArr[] = [
            151,
            IcalInterface::DURATION,
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
            152,
            IcalInterface::DURATION,
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => DateIntervalFactory::factory( $value ),
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) . ':' . $value
        ];

        // REPEAT
        $dataArr[] = [
            161,
            IcalInterface::REPEAT,
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
            162,
            IcalInterface::REPEAT,
            $value,
            self::$STCPAR,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) . ':' . $value
        ];

        // ATTACH
        $getValue  = [
            Util::$LCvalue  => '',
            Util::$LCparams => []
        ];
        $dataArr[] = [
            171,
            IcalInterface::ATTACH,
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
            172,
            IcalInterface::ATTACH,
            $value,
            $params,
            $getValue,
            ParameterFactory::createParams( $params ) . ':' . $value
        ];

        // ATTACH
        $value  = 'ftp://example.com/pub/reports/r-960812.ps';
        $params = [ IcalInterface::FMTTYPE => 'application/postscript' ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            173,
            IcalInterface::ATTACH,
            $value,
            $params,
            $getValue,
            ParameterFactory::createParams( $params ) . ':' . $value
        ];

        // DESCRIPTION
        $dataArr[] = [
            181,
            IcalInterface::DESCRIPTION,
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
                IcalInterface::ALTREP   => 'http://example.org/altrep182.html',
                IcalInterface::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [
            182,
            IcalInterface::DESCRIPTION,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams(
                $params,
                [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ]
            ) . ':' . $value
        ];

        // PROXIMITY
        $dataArr[] = [
            191,
            IcalInterface::PROXIMITY,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $dataArr[] = [
            192,
            IcalInterface::PROXIMITY,
            IcalInterface::ARRIVE,
            self::$STCPAR,
            [
                Util::$LCvalue  => IcalInterface::ARRIVE,
                Util::$LCparams => self::$STCPAR
            ],
            ParameterFactory::createParams( self::$STCPAR ) . ':' . IcalInterface::ARRIVE
        ];

        // STYLED-DESCRIPTION
        $dataArr[] = [
            201,
            IcalInterface::STYLED_DESCRIPTION,
            null,
            self::$STCPAR,
            [
                Util::$LCvalue  => '',
                Util::$LCparams => []
            ],
            ':'
        ];

        $value  = 'http://example.org/desc001.html';
        $params = [
                IcalInterface::VALUE    => IcalInterface::URI,
                IcalInterface::ALTREP   => 'http://example.org/altrep202.html', // skipped
                IcalInterface::LANGUAGE => 'EN'                                 // skipped
            ] + self::$STCPAR;
        $params2 = [
                IcalInterface::VALUE   => IcalInterface::URI,
                IcalInterface::DERIVED => IcalBase::FALSE
            ] + self::$STCPAR;
        $dataArr[] = [
            202,
            IcalInterface::STYLED_DESCRIPTION,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params2
            ],
            ParameterFactory::createParams(
                $params2,
                [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ]
            ) . ':' . $value
        ];

        $value  = 'This is a longer styled description property with a number of meaningless words';
        $params = [
                IcalInterface::VALUE    => IcalInterface::TEXT,
                IcalInterface::ALTREP   => 'http://example.org/altrep203.html',
                IcalInterface::LANGUAGE => 'EN',
                IcalInterface::DERIVED  => IcalInterface::TRUE
            ] + self::$STCPAR;
        $dataArr[] = [
            203,
            IcalInterface::STYLED_DESCRIPTION,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams(
                $params,
                [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ]
            ) . ':' . $value
        ];

        // SUMMARY
        $dataArr[] = [
            211,
            IcalInterface::SUMMARY,
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
                IcalInterface::ALTREP   => 'http://example.org/altrep212.html',
                IcalInterface::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [
            212,
            IcalInterface::SUMMARY,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams(
                $params,
                [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ]
            ) . ':' . $value
        ];

        // ATTENDEE
        $dataArr[] = [
            221,
            IcalInterface::ATTENDEE,
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
            222,
            IcalInterface::ATTENDEE,
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
     * valarmTest provider TEST multi Valarms
     *
     * @return mixed[]
     */
    public function valarmTestProviderTest() : array
    {
        $dataArr = [];

        $value  = 'Meeting to provide....';
        $params = [];
        $dataArr[] = [
            9999,
            IcalInterface::DESCRIPTION,
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams(
                $params,
                [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ]
            ) . ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing Valarm
     *
     * @test
     * @dataProvider valarmTestProvider
     * @ // dataProvider valarmTestProviderTest
     * @param int|string $caseIn
     * @param string $propName
     * @param mixed  $value
     * @param mixed  $params
     * @param mixed[] $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function valarmTest(
        int|string $caseIn,
        string $propName,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        static $MULTIPROPS = [
            IcalInterface::ATTACH,
            IcalInterface::ATTENDEE,
            IcalInterface::DESCRIPTION,
            IcalInterface::RELATED_TO,
            IcalInterface::STYLED_DESCRIPTION
        ];

        $c  = new Vcalendar();
        for( $cix = 0; $cix < 2; $cix++ ) {
            if( 0 === $cix ) {
                $comp = $c->newVevent();
            }
            else {
                $comp = $c->newVtodo();
            }
            $case = $caseIn . '-' . ( 1 + $cix);
            $comp->setXprop( 'x-case', $case )
                ->setXprop( 'x-time', DateTimeFactory::factory()->format( DateTimeFactory::$YmdHis ));

            $a1 = $comp->newValarm()
                ->setXprop( 'x-case', $case )
                ->setXprop( 'x-time', DateTimeFactory::factory()->format( DateTimeFactory::$YmdHis ));

            if( IcalInterface::DESCRIPTION === $propName ) {
                $vLocation1 = $a1->newVlocation( 'Office1' )
                    ->setDescription( $value, $params )
                    ->setXprop( 'x-time', DateTimeFactory::factory()->format( DateTimeFactory::$YmdHis ))
                    ->setXprop( 'x-case', $case . ' location 1' ); // by-pass test
                $vLocation2 = $a1->newVlocation( 'Office1' )
                    ->setDescription( $value, $params )
                    ->setXprop( 'x-time', DateTimeFactory::factory()->format( DateTimeFactory::$YmdHis ))
                    ->setXprop( 'x-case', $case . ' location 2' ); // by-pass test
            }

            $getMethod    = StringFactory::getGetMethodName( $propName );
            $createMethod = StringFactory::getCreateMethodName( $propName );
            $deleteMethod = StringFactory::getDeleteMethodName( $propName );
            $setMethod    = StringFactory::getSetMethodName( $propName );

            $a1->{$setMethod}( $value, $params ); // set first

//          echo __FUNCTION__ . ' case #' . $case . PHP_EOL . $a1->createComponent() . PHP_EOL; // test ###

            $getValue = ( in_array( $propName, $MULTIPROPS, true ))
                ? $a1->{$getMethod}( null, true )
                : $a1->{$getMethod}( true );
            $this->assertEquals(
                $expectedGet,
                $getValue,
                sprintf( self::$ERRFMT, null, $case . '-1', __FUNCTION__, IcalInterface::VALARM, $getMethod )
            );
            $actualString = $a1->{$createMethod}();
            $actualString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], '', $actualString );
            $actualString = str_replace( '\,', ',', $actualString );
            $this->assertEquals(
                strtoupper( $propName ) . $expectedString,
                trim( $actualString ),
                sprintf( self::$ERRFMT, null, $case . '-2', __FUNCTION__, 'Valarm', $createMethod )
            );
            if( $propName !== IcalInterface::UID ) { // sort of mandatory
                $a1->{$deleteMethod}();
                $this->assertFalse(
                    $a1->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case . '-3', __FUNCTION__, 'Valarm', $getMethod )
                );
                $a1->{$setMethod}( $value, $params ); // set again
            }

//          echo __FUNCTION__ . ' case #' . $case . PHP_EOL . $c->createCalendar() . PHP_EOL; // test ###

            $this->parseCalendarTest( $case, $c, $expectedString );

            // clone $a1 into 2 (incl cloned subs)
            $subs = [];
            while( $sub = $a1->getComponent()) { // return clone
                $subs[] = $sub;
            }
            $x = 1;
            while( $a1->deleteComponent( $x )) {
                ++$x;
            }
            $a11 = clone $a1;
            $a11->setXprop( 'x-clone', $case . ' clone 1' );
            $a12 = clone $a1;
            $a12->setXprop( 'x-clone', $case . ' clone 2' );
            foreach( $subs as $six => $sub ) {
                $sub->setXprop( 'x-clone', $case . ' clone ' . $six . '-1' );
                $a11->addSubComponent( $sub ); // set clone
                $sub->setXprop( 'x-clone', $case . ' clone ' . $six . '-2' );
                $a12->addSubComponent( $sub );
            }

            $compArr = [ $a11, $a12 ];
            $x = 1;
            while( $comp->deleteComponent( $x )) {
                ++$x;
            }
            $this->assertSame(
                0, $comp->countComponents(), $case .  '-5 deleteComponent-error 2, has ' . $comp->countComponents()
            );
            // set the cloned components
            foreach( $compArr as $subComp ) {
                $comp->setComponent( $subComp );
            }
            // check number of components
            $this->assertSame(
                count( $compArr ), $comp->countComponents(), $case .  '-6 setComponent-error 3, has ' . $comp->countComponents()
            );

        }
    }
}
