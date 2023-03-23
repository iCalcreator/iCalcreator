<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2023 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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

use ArgumentCountError;
use PHPUnit\Framework\TestCase;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Exception;

/**
 * class Exception6Test
 *
 * Testing 'ALLOWEMPTY = false' exceptions
 *
 * @since  2.27.14 - 2019-02-27
 */
class Exception6Test extends TestCase
{
    private static string $ERRFMT = "%s error in case #%s, <%s>->%s";

    /**
     * AllowEmptyTest1 provider
     *
     * @return mixed[]
     */
    public function AllowEmptyTest1Provider() : array
    {
        $dataArr = [];

        $dataArr[] = [
            11,
            [
                IcalInterface::VEVENT =>
                    [
                        IcalInterface::ATTACH, IcalInterface::ATTENDEE, IcalInterface::CATEGORIES,
                        IcalInterface::KLASS, IcalInterface::COMMENT, IcalInterface::CONTACT,
                        IcalInterface::DESCRIPTION, IcalInterface::DTEND, IcalInterface::DTSTART,
                        IcalInterface::DURATION, IcalInterface::EXDATE, IcalInterface::EXRULE,
                        IcalInterface::GEO, IcalInterface::LOCATION, IcalInterface::ORGANIZER,
                        IcalInterface::PRIORITY, IcalInterface::RECURRENCE_ID, IcalInterface::RELATED_TO,
                        IcalInterface::REQUEST_STATUS, IcalInterface::RESOURCES, IcalInterface::RRULE, IcalInterface::RDATE,
                        IcalInterface::STATUS, IcalInterface::SUMMARY, IcalInterface::TRANSP, IcalInterface::URL,
                    ],
            ]
        ];

        $dataArr[] = [
            12,
            [
                IcalInterface::VTODO => [
                    IcalInterface::ATTACH, IcalInterface::ATTENDEE, IcalInterface::CATEGORIES,
                    IcalInterface::KLASS, IcalInterface::COMMENT, IcalInterface::COMPLETED, IcalInterface::CONTACT,
                    IcalInterface::DESCRIPTION, IcalInterface::DTSTART, IcalInterface::DUE,
                    IcalInterface::DURATION, IcalInterface::EXDATE, IcalInterface::EXRULE,
                    IcalInterface::GEO, IcalInterface::LOCATION, IcalInterface::ORGANIZER,
                    IcalInterface::PRIORITY, IcalInterface::RECURRENCE_ID, IcalInterface::RELATED_TO,
                    IcalInterface::REQUEST_STATUS, IcalInterface::RESOURCES, IcalInterface::RRULE, IcalInterface::RDATE,
                    IcalInterface::STATUS, IcalInterface::SUMMARY, IcalInterface::URL,
                ],
            ],
        ];

        $dataArr[] = [
            13,
            [
                IcalInterface::VJOURNAL => [
                    IcalInterface::ATTACH, IcalInterface::ATTENDEE, IcalInterface::CATEGORIES,
                    IcalInterface::KLASS, IcalInterface::COMMENT, IcalInterface::CONTACT,
                    IcalInterface::DESCRIPTION, IcalInterface::DTSTART,
                    IcalInterface::EXDATE, IcalInterface::EXRULE,
                    IcalInterface::ORGANIZER,
                    IcalInterface::RECURRENCE_ID, IcalInterface::RELATED_TO,
                    IcalInterface::REQUEST_STATUS, IcalInterface::RRULE, IcalInterface::RDATE,
                    IcalInterface::STATUS, IcalInterface::SUMMARY, IcalInterface::URL,
                ],
            ],
        ];

        $dataArr[] = [
            14,
            [
                IcalInterface::VFREEBUSY => [
                    IcalInterface::ATTENDEE, IcalInterface::COMMENT, IcalInterface::CONTACT,
                    IcalInterface::DTEND, IcalInterface::DTSTART, IcalInterface::DURATION,
                    IcalInterface::FREEBUSY, IcalInterface::REQUEST_STATUS, IcalInterface::URL,
                ],
            ]
        ];

        $dataArr[] = [
            15,
            [
                IcalInterface::VTIMEZONE => [
                    IcalInterface::TZID, IcalInterface::TZURL,
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
     * @param int $case
     * @param mixed[] $compProps
     * @throws Exception
     */
    public function AllowEmptyTest1( int $case, array $compProps ) : void
    {
        $calendar = new Vcalendar( [ IcalInterface::ALLOWEMPTY => false ] );
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
    public function AllowEmptyTest2() : void
    {
        $comps = [
            IcalInterface::VEVENT,
            IcalInterface::VTODO,
            IcalInterface::VJOURNAL,
            IcalInterface::VFREEBUSY,
            IcalInterface::VTIMEZONE
        ];
        $calendar = new Vcalendar( [ IcalInterface::ALLOWEMPTY => false ] );
        foreach( $comps as $x => $theComp ) {
            $newMethod = 'new' . $theComp;
            $ok = false;
            try {
                $calendar->{$newMethod}()->setXprop();
            }
            catch( ArgumentCountError $e ) {
                $ok = true;
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
    public function AllowEmptyTest3() : void
    {
        $compProps = [
            IcalInterface::VEVENT => [
                IcalInterface::ACTION, IcalInterface::DESCRIPTION, IcalInterface::TRIGGER, IcalInterface::SUMMARY,
                IcalInterface::ATTENDEE,
                IcalInterface::DURATION, IcalInterface::REPEAT,
                IcalInterface::ATTACH,
            ],
            IcalInterface::VTODO => [
                IcalInterface::ACTION, IcalInterface::DESCRIPTION, IcalInterface::TRIGGER, IcalInterface::SUMMARY,
                IcalInterface::ATTENDEE,
                IcalInterface::DURATION, IcalInterface::REPEAT,
                IcalInterface::ATTACH,
            ],
        ];
        $calendar = new Vcalendar( [ IcalInterface::ALLOWEMPTY => false ] );
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
    public function AllowEmptyTest4() : void
    {
        $compProps = [
            IcalInterface::VEVENT => [
                IcalInterface::VALARM
            ],
            IcalInterface::VTIMEZONE => [
                IcalInterface::STANDARD,
                IcalInterface::DAYLIGHT
            ],
        ];
        $calendar = new Vcalendar( [ IcalInterface::ALLOWEMPTY => false ] );
        foreach( $compProps as $theComp => $compNames ) {
            $newMethod1 = 'new' . $theComp;
            foreach( $compNames as $x => $subComp ) {
                $newMethod2 = 'new' . $subComp;
                $ok = false;
                try {
                    $calendar->{$newMethod1}()->{$newMethod2}()->setXprop();
                }
                catch( ArgumentCountError $e ) {
                    $ok = true;
                }
                catch( Exception $e ) {
                    $ok = true;
                }
                $this->assertTrue( $ok, sprintf( self::$ERRFMT, __FUNCTION__, $x, $theComp, 'xProp' ) );
            } // end foreach
        } // end foreach
    }
}
