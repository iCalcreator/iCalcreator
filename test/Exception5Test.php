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

use PHPUnit\Framework\TestCase;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Exception;

/**
 * class Exception5Test
 *
 * Testing ALLOWEMPTY = false exceptions
 *
 * @since  2.27.14 - 2019-02-27
 */
class Exception5Test extends TestCase
{
    private static $ERRFMT = "%s error in case #%s, <%s>->%s";

    /**
     * AllowEmptyTest1 provider
     */
    public function AllowEmptyTest1Provider()
    {
        $dataArr = [];

        $dataArr[] = [
            11,
            [
                Vcalendar::VEVENT =>
                    [
                        Vcalendar::ATTACH, Vcalendar::ATTENDEE, Vcalendar::CATEGORIES,
                        Vcalendar::KLASS, Vcalendar::COMMENT, Vcalendar::CONTACT,
                        Vcalendar::DESCRIPTION, Vcalendar::DTEND, Vcalendar::DTSTART,
                        Vcalendar::DURATION, Vcalendar::EXDATE, Vcalendar::EXRULE,
                        Vcalendar::GEO, Vcalendar::LOCATION, Vcalendar::ORGANIZER,
                        Vcalendar::PRIORITY, Vcalendar::RECURRENCE_ID, Vcalendar::RELATED_TO,
                        Vcalendar::REQUEST_STATUS, Vcalendar::RESOURCES, Vcalendar::RRULE, Vcalendar::RDATE,
                        Vcalendar::STATUS, Vcalendar::SUMMARY, Vcalendar::TRANSP, Vcalendar::URL,
                    ],
            ]
        ];

        $dataArr[] = [
            12,
            [
                Vcalendar::VTODO => [
                    Vcalendar::ATTACH, Vcalendar::ATTENDEE, Vcalendar::CATEGORIES,
                    Vcalendar::KLASS, Vcalendar::COMMENT, Vcalendar::COMPLETED, Vcalendar::CONTACT,
                    Vcalendar::DESCRIPTION, Vcalendar::DTSTART, Vcalendar::DUE,
                    Vcalendar::DURATION, Vcalendar::EXDATE, Vcalendar::EXRULE,
                    Vcalendar::GEO, Vcalendar::LOCATION, Vcalendar::ORGANIZER,
                    Vcalendar::PRIORITY, Vcalendar::RECURRENCE_ID, Vcalendar::RELATED_TO,
                    Vcalendar::REQUEST_STATUS, Vcalendar::RESOURCES, Vcalendar::RRULE, Vcalendar::RDATE,
                    Vcalendar::STATUS, Vcalendar::SUMMARY, Vcalendar::URL,
                ],
            ],
        ];

        $dataArr[] = [
            13,
            [
                Vcalendar::VJOURNAL => [
                    Vcalendar::ATTACH, Vcalendar::ATTENDEE, Vcalendar::CATEGORIES,
                    Vcalendar::KLASS, Vcalendar::COMMENT, Vcalendar::CONTACT,
                    Vcalendar::DESCRIPTION, Vcalendar::DTSTART,
                    Vcalendar::EXDATE, Vcalendar::EXRULE,
                    Vcalendar::ORGANIZER,
                    Vcalendar::RECURRENCE_ID, Vcalendar::RELATED_TO,
                    Vcalendar::REQUEST_STATUS, Vcalendar::RRULE, Vcalendar::RDATE,
                    Vcalendar::STATUS, Vcalendar::SUMMARY, Vcalendar::URL,
                ],
            ],
        ];

        $dataArr[] = [
            14,
            [
                Vcalendar::VFREEBUSY => [
                    Vcalendar::ATTENDEE, Vcalendar::COMMENT, Vcalendar::CONTACT,
                    Vcalendar::DTEND, Vcalendar::DTSTART, Vcalendar::DURATION,
                    Vcalendar::FREEBUSY, Vcalendar::REQUEST_STATUS, Vcalendar::URL,
                ],
            ]
        ];

        $dataArr[] = [
            15,
            [
                Vcalendar::VTIMEZONE => [
                    Vcalendar::TZID, Vcalendar::TZURL,
                ],
            ]
        ];

        return $dataArr;
    }

    /**
     * Test Vevent, Vtodo, Vjournal, Vfreebusy, Vtimezone
     *
     * @test
     * @dataProvider AllowEmptyTest1Provider
     * @param int    $case
     * @param array  $compProps
     */
    public function AllowEmptyTest1( $case, $compProps )
    {
        $calendar = new Vcalendar( [ Vcalendar::ALLOWEMPTY => false ] );
        foreach( $compProps as $theComp => $propNames ) {
            $newMethod = 'new' . $theComp;
            $comp = $calendar->{$newMethod}();
            foreach( $propNames as $propName ) {
                $setMethod = StringFactory::getSetMethodName( $propName );
                $ok = false;
                try {
                    $comp->{$setMethod}();
                }
                catch( Exception $e ) {
                    $ok = true;
                }
                $this->assertTrue( $ok, sprintf( self::$ERRFMT, __FUNCTION__ , $case, $theComp, $propName ));
            } // end foreach
        } // end foreach
    }

    /**
     * Test Vevent, Vtodo, Vjournal, Vfreebusy, Vtimezone X-prop
     *
     * @test
     */
    public function AllowEmptyTest2()
    {
        $comps = [
            Vcalendar::VEVENT,
            Vcalendar::VTODO,
            Vcalendar::VJOURNAL,
            Vcalendar::VFREEBUSY,
            Vcalendar::VTIMEZONE
        ];
        $calendar = new Vcalendar( [ Vcalendar::ALLOWEMPTY => false ] );
        foreach( $comps as $x => $theComp ) {
            $newMethod = 'new' . $theComp;
            $ok = false;
            try {
                $calendar->{$newMethod}()->setXprop();
            }
            catch( Exception $e ) {
                $ok = true;
            }
            $this->assertTrue( $ok, sprintf( self::$ERRFMT, __FUNCTION__, $x, $theComp, 'xProp' ) );
        } // end foreach
    }

    /**
     * Test Valarm X-prop
     *
     * @test
     */
    public function AllowEmptyTest3()
    {
        $compProps = [
            Vcalendar::VEVENT => [
                Vcalendar::ACTION, Vcalendar::DESCRIPTION, Vcalendar::TRIGGER, Vcalendar::SUMMARY,
                Vcalendar::ATTENDEE,
                Vcalendar::DURATION, Vcalendar::REPEAT,
                Vcalendar::ATTACH,
            ],
            Vcalendar::VTODO => [
                Vcalendar::ACTION, Vcalendar::DESCRIPTION, Vcalendar::TRIGGER, Vcalendar::SUMMARY,
                Vcalendar::ATTENDEE,
                Vcalendar::DURATION, Vcalendar::REPEAT,
                Vcalendar::ATTACH,
            ],
        ];
        $calendar = new Vcalendar( [ Vcalendar::ALLOWEMPTY => false ] );
        foreach( $compProps as $theComp => $propNames) {
            $newMethod = 'new' . $theComp;
            $comp      = $calendar->{$newMethod}()->newValarm();
            foreach( $propNames as $x => $propName ) {
                $setMethod = StringFactory::getSetMethodName( $propName );
                $ok        = false;
                try {
                    $comp->{$setMethod}();
                }
                catch( Exception $e ) {
                    $ok = true;
                }
                $this->assertTrue( $ok, sprintf( self::$ERRFMT, __FUNCTION__, $x, $theComp, $propName ) );
            } // end foreach
        } // end foreach
    }

    /**
     * Test Valarm/Standard/Daylight X-prop
     *
     * @test
     */
    public function AllowEmptyTest4()
    {
        $compProps = [
            Vcalendar::VEVENT => [
                Vcalendar::VALARM
            ],
            Vcalendar::VTIMEZONE => [
                Vcalendar::STANDARD,
                Vcalendar::DAYLIGHT
            ],
        ];
        $calendar = new Vcalendar( [ Vcalendar::ALLOWEMPTY => false ] );
        foreach( $compProps as $theComp => $compNames ) {
            $newMethod1 = 'new' . $theComp;
            foreach( $compNames as $x => $subComp ) {
                $newMethod2 = 'new' . $subComp;
                $ok = false;
                try {
                    $calendar->{$newMethod1}()->{$newMethod2}()->setXprop();
                }
                catch( Exception $e ) {
                    $ok = true;
                }
                $this->assertTrue( $ok, sprintf( self::$ERRFMT, __FUNCTION__, $x, $theComp, 'xProp' ) );
            } // end foreach
        } // end foreach
    }
}
