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
use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Util\Util;

class PropXTest extends DtBase
{
    /*
     *
     */
    protected static string $ERRFMT   = "Error %sin case #%s, %s <%s>->%s";

    /**
     * @var string[]
     */
    private static array $STCPAR   = [ 'X-PARAM' => 'Y-vALuE' ];

    /**
     * Testing component X-property
     *
     * @return mixed[]
     */
    public function misc3Provider() : array
    {

        $dataArr = [];

        $propName  = 'X-ABC-MMSUBJ';
        $value     = 'This is an X-property value';
        $params    = [] + self::$STCPAR;
        $dataArr[] = [
            1,
            $propName,
            [
                $propName => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VFREEBUSY,
//                    Vcalendar::VTIMEZONE
                ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            Property::formatParams( $params ) . ':' . $value
        ];

        $propName  = 'X-ALARM-CNT';
        $value     = '1000 : -PT1000M';
        $params    = [] + self::$STCPAR;
        $dataArr[] = [
            2,
            $propName,
            [
                $propName => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VFREEBUSY,
//                    Vcalendar::VTIMEZONE // as for now, can't sort Vtimezone...
                ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            Property::formatParams( $params ) . ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing Vcalendar and component X-property
     *
     * @test
     * @dataProvider misc3Provider
     * @param int     $case
     * @param string  $propName
     * @param mixed[] $propComps
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function miscTest3(
        int    $case,
        string $propName,
        array  $propComps,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        // set two Vcalendar X-properties
        $c = new Vcalendar();
        for( $x = 1; $x < 3; $x++ ) {
            $this->misc3factory(
                $c,
                'Vcalendar',
                $case . 31,
                $propName . $x,
                $value,
                $params,
                $expectedGet,
                $expectedString
            );
        } // end for

        // set single component X-property
        foreach( $propComps as $propName2 => $theComps ) {
            foreach( $theComps as $theComp ) {
                $newMethod = 'new' . $theComp;
                $comp      = $c->{$newMethod}();

                if( in_array( $theComp, [ IcalInterface::VEVENT, IcalInterface::VTODO ], true ) ) {
                    $a     = $comp->newValarm();
                    $this->misc3factory(
                        $a,
                        'Valarm',
                        $case . 32,
                        $propName2,
                        $value,
                        $params,
                        $expectedGet,
                        $expectedString
                    );
                }

                $this->misc3factory(
                    $comp,
                    $theComp,
                    $case . 33,
                    $propName2,
                    $value,
                    $params,
                    $expectedGet,
                    $expectedString
                );
            }
        }

        // set two component X-properties and two in Vevent/Vtodo Valarms
        foreach( $propComps as $propName3 => $theComps ) {
            foreach( $theComps as $theComp ) {
                $newMethod = 'new' . $theComp;
                $comp      = $c->{$newMethod}();
                if( in_array( $theComp, [ IcalInterface::VEVENT, IcalInterface::VTODO ], true ) ) {
                    $a     = $comp->newValarm();
                }
                for( $x = 1; $x < 3; $x++ ) {
                    if( in_array( $theComp, [ IcalInterface::VEVENT, IcalInterface::VTODO ], true ) ) {
                        $this->misc3factory(
                            $a,
                            'Valarm',
                            $case . 34,
                            $propName3 . $x,
                            $value,
                            $params,
                            $expectedGet,
                            $expectedString
                        );
                    }

                    $this->misc3factory(
                        $comp,
                        $theComp,
                        $case . 35,
                        $propName3 . $x,
                        $value,
                        $params,
                        $expectedGet,
                        $expectedString
                    );
                } // end for
            } // end foreach
        } // end foreach
        $c->sort();
        $this->parseCalendarTest( $case, $c, $expectedString );

    }

    /**
     * Testing component X-property factory
     *
     * @param IcalBase $comp,
     * @param string   $compName,
     * @param int      $Number,
     * @param string   $propName,
     * @param string   $value,
     * @param mixed[]  $params,
     * @param Pc       $expectedGet,
     * @param string   $expectedString
     */
    public function misc3factory(
        IcalBase $comp,
        string   $compName,
        int      $Number,
        string   $propName,
        string   $value,
        array    $params,
        Pc       $expectedGet,
        string   $expectedString
    ) : void
    {
        static $pcInput = false;
        if( $pcInput ) {
            $comp->setXprop( $propName, Pc::factory( $value, $params ));
        }
        else {
            $comp->setXprop( $propName, $value, $params );
        }
        $pcInput = ! $pcInput;

        $getValue = $comp->getXprop( $propName, null, true );
        $this->assertEquals(
            [ $propName, $expectedGet ],
            $getValue,
            sprintf( self::$ERRFMT, null, $Number . 1, __FUNCTION__, $compName, 'getXprop' )
        );

        $createString   = str_replace( Util::$CRLF . ' ', null, $comp->createXprop());
        $createString   = str_replace( '\,', ',', $createString );
        $this->assertNotFalse(
            strpos( $createString, $expectedString ),
            sprintf( self::$ERRFMT, null, $Number . 2, __FUNCTION__, 'Vcalendar', 'createXprop' )
        );

        $comp->deleteXprop( $propName );
        $this->assertFalse(
            $comp->getXprop( $propName ),
            sprintf(
                self::$ERRFMT, '(after delete) ', $Number . '3 ' . $propName, __FUNCTION__, 'Vcalendar', 'getXprop'
            )
        );

        $comp->setXprop( $propName, $value, $params );
    }
}
