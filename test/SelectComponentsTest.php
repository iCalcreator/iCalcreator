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
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator;

use DateTime;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * class SelectComponentsTest
 *
 * Testing exceptions in DateIntervalFactory
 *
 * @since  2.27.18 - 2019-04-09
 */
class SelectComponentsTest extends TestCase
{
    /**
     * Vevent calendar sub-provider
     *
     * demo calendar
     * @throws Exception
     */
    public static function veventCalendarSubProvider() : Vcalendar
    {
        // create a new calendar
        $vcalendar = Vcalendar::factory( [ IcalInterface::UNIQUE_ID => "kigkonsult.se", ] )
            // with calendaring info
            ->setMethod( IcalInterface::PUBLISH )
            ->setXprop(
                IcalInterface::X_WR_CALNAME,
                "Calendar Sample"
            )
            ->setXprop(
                IcalInterface::X_WR_CALDESC,
                "The calendar Description"
            )
            ->setXprop(
                IcalInterface::X_WR_RELCALID,
                "3E26604A-50F4-4449-8B3E-E4F4932D05B5"
            )
            ->setXprop(
                IcalInterface::X_WR_TIMEZONE,
                "Europe/Stockholm"
            );

        // create a new event with dtstart, dtend and summary
        $event1 = $vcalendar->newVevent(
            new DateTime( '20190421T090000', new DateTimeZone( 'Europe/Stockholm' )),
            new DateTime( '20190421T100000', new DateTimeZone( 'Europe/Stockholm' )),
            null.
            'Scheduled meeting with six occurrences'
        )
            ->setTransp( IcalInterface::OPAQUE )
            ->setClass( IcalInterface::P_BLIC )
            ->setSequence( 1 )
            ->setDescription(
                'Agenda for the the meeting...',
                [ IcalInterface::ALTREP => 'CID:<FFFF__=0ABBE548DFE235B58f9e8a93d@coffeebean.com>' ]
            )
            ->setComment( 'It\'s going to be fun..' )
            ->setLocation( 'Kafé Ekorren Stockholm' )
            ->setGeo( '59.32206', '18.12485' )
            // with recurrence rule
            ->setRrule(
                [
                    IcalInterface::FREQ => IcalInterface::WEEKLY,
                    IcalInterface::COUNT => 5,
                ]
            )
            // and a another using a recurrence date
            ->setRdate(
                [
                    new DateTime(
                        '20190609T090000',
                        new DateTimeZone( 'Europe/Stockholm' )
                    ),
                    new DateTime(
                        '20190609T110000',
                        new DateTimeZone( 'Europe/Stockholm' )
                    ),
                ],
                [ IcalInterface::VALUE => IcalInterface::PERIOD ]
            )
            // and revoke a recurrence date
            ->setExdate(
                new DateTime(
                    '2019-05-12 09:00:00',
                    new DateTimeZone( 'Europe/Stockholm' )
                )
            )
            // organizer, chair  and some participants
            ->setContact(
                'Head Office, coffeebean Corp, Acme city',
                [ IcalInterface::ALTREP => "http://coffeebean.com/contacts.vcf" ]
            )
            ->setContact(
                '+12 34 56 78 90, coffeebean Corp, Acme city',
                [ IcalInterface::ALTREP => "http://coffeebean.com/contacts.vcf" ]
            )
            ->setContact(
                'Head.Office@coffeebean.com, coffeebean Corp, Acme city',
                [ IcalInterface::ALTREP => "http://coffeebean.com/contacts.vcf" ]
            )
            ->setOrganizer(
                'secretary@coffeebean.com',
                [ IcalInterface::SENT_BY => 'sent_by.Secretary.staff@CoffeeBean.com' ]
            )
            ->setAttendee(
                'president@coffeebean.com',
                [
                    IcalInterface::ROLE => IcalInterface::CHAIR,
                    IcalInterface::PARTSTAT => IcalInterface::ACCEPTED,
                    IcalInterface::RSVP => IcalInterface::FALSE,
                    IcalInterface::CN => 'President CoffeeBean',
                    IcalInterface::SENT_BY => 'president.sent_by.secretary@coffeebean.com'
                ]
            )
            ->setAttendee(
                'participant1.staff@coffeebean.com',
                [
                    IcalInterface::ROLE => IcalInterface::REQ_PARTICIPANT,
                    IcalInterface::PARTSTAT => IcalInterface::NEEDS_ACTION,
                    IcalInterface::RSVP => IcalInterface::TRUE,
                    IcalInterface::CN => 'Participant1 CoffeeBean',
                    IcalInterface::MEMBER => [
                        'member.3@coffeebean.com',
                        'member.4@coffeebean.com'
                    ],
                ]
            )
            ->setAttendee(
                'participant2@coffeebean.com',
                [
                    IcalInterface::ROLE => IcalInterface::REQ_PARTICIPANT,
                    IcalInterface::PARTSTAT => IcalInterface::NEEDS_ACTION,
                    IcalInterface::RSVP => IcalInterface::TRUE,
                    IcalInterface::CN => 'Participant2 CoffeeBean',
                    IcalInterface::DELEGATED_FROM => 'delegated_from.2@coffeebean.com',
                ]
            );

        // add an alarm for the event
        // fire off the alarm one day before
        $alarm = $event1->newValarm(IcalInterface::DISPLAY, '-P1D' )
            ->setDescription( $event1->getDescription());

        // alter day and time for one event in recurrence set
        // the altered day and time with duration
        $event2 = $vcalendar->newVevent(
            new DateTime( '20190504T100000', new DateTimeZone( 'Europe/Stockholm' )),
            null,
            'PT2H'
        )
            ->setTransp( IcalInterface::OPAQUE )
            ->setClass( IcalInterface::P_BLIC )
            // reference to one event in recurrence set
            ->setUid( $event1->getUid() )
            ->setSequence( 2 )
            // pointer to event in the recurrence set
            ->setRecurrenceid( '20190505T090000 Europe/Stockholm' )
            // reason text
            ->setDescription(
                'Altered day and time for event 2019-05-05',
                [ IcalInterface::ALTREP => 'CID:<FFFF__=0ABBE548DFE235B58f9e8a93d@coffeebean.com>' ]
            )
            ->setComment( 'Now we are working hard for two hours' )
            // add alarm (i.e. same as event1)
            ->setComponent( $event1->getComponent( IcalInterface::VALARM ) );

        // apply appropriate Vtimezone with Standard/DayLight components
        $vcalendar->vtimezonePopulate();

        return $vcalendar;
    }

    /**
     * Vtodo calendar sub-provider
     * @throws Exception
     */
    public static function vtodoCalendarSubProvider() : Vcalendar
    {
        // create a new calendar
        $vcalendar = Vcalendar::factory( [ IcalInterface::UNIQUE_ID => "kigkonsult.se", ] )
            // with calendaring info
            ->setMethod( IcalInterface::PUBLISH )
            ->setXprop(
                IcalInterface::X_WR_CALNAME,
                "Calendar Sample"
            )
            ->setXprop(
                IcalInterface::X_WR_CALDESC,
                "The calendar Description"
            )
            ->setXprop(
                IcalInterface::X_WR_RELCALID,
                "3E26604A-50F4-4449-8B3E-E4F4932D05B5"
            )
            ->setXprop(
                IcalInterface::X_WR_TIMEZONE,
                "Europe/Stockholm"
            );

        // create a new todo
        $todo1 = $vcalendar->newVtodo(
            new DateTime( '20190421T090000', new DateTimeZone( 'Europe/Stockholm' )),
            new DateTime( '20190421T100000', new DateTimeZone( 'Europe/Stockholm' )),
            null,
            'Scheduled meeting with six occurrences'
        )
            ->setClass( IcalInterface::P_BLIC )
            ->setSequence( 1 )
            // describe the event
            ->setDescription(
                'Agenda for the the meeting...',
                [ IcalInterface::ALTREP => 'CID:<FFFF__=0ABBE548DFE235B58f9e8a93d@coffeebean.com>' ]
            )
            ->setComment( 'It\'s going to be fun..' )
            ->setLocation( 'Kafé Ekorren Stockholm' )
            ->setGeo( '59.32206', '18.12485' )
            // with recurrence rule
            ->setRrule(
                [
                    IcalInterface::FREQ => IcalInterface::WEEKLY,
                    IcalInterface::COUNT => 5,
                ]
            )
            // and a another using a recurrence date
            ->setRdate(
                [
                    new DateTime(
                        '20190609T090000',
                        new DateTimeZone( 'Europe/Stockholm' )
                    ),
                    new DateTime(
                        '20190609T110000',
                        new DateTimeZone( 'Europe/Stockholm' )
                    ),
                ],
                [ IcalInterface::VALUE => IcalInterface::PERIOD ]
            )
            // and revoke recurrence dates
            ->setExdate(
                new DateTime(
                    '2019-05-12 09:00:00',
                    new DateTimeZone( 'Europe/Stockholm' )
                )
            )
            // organizer, chair  and some participants
            ->setContact(
                'Head Office, coffeebean Corp, Acme city',
                [ IcalInterface::ALTREP => "http://coffeebean.com/contacts.vcf" ]
            )
            ->setOrganizer(
                'secretary2@coffeebean.com',
                [ IcalInterface::SENT_BY => 'sent_by.Secretary.staff@CoffeeBean.com' ]
            )
            ->setAttendee(
                'VicePresident@coffeebean.com',
                [
                    IcalInterface::ROLE => IcalInterface::CHAIR,
                    IcalInterface::PARTSTAT => IcalInterface::ACCEPTED,
                    IcalInterface::RSVP => IcalInterface::FALSE,
                    IcalInterface::CN => 'President CoffeeBean',
                    IcalInterface::SENT_BY => 'sent_by.VicePresident@coffeebean.com',
                ]
            )
            ->setAttendee(
                'participant1.team1@coffeebean.com',
                [
                    IcalInterface::ROLE => IcalInterface::REQ_PARTICIPANT,
                    IcalInterface::PARTSTAT => IcalInterface::NEEDS_ACTION,
                    IcalInterface::RSVP => IcalInterface::TRUE,
                    IcalInterface::CN => 'Participant1 CoffeeBean',
                    IcalInterface::MEMBER => [
                        'member1@coffeebean.com',
                        'member2@coffeebean.com'
                    ],
                    IcalInterface::DELEGATED_TO => [
                        'delegated_to.1@coffeebean.com',
                        'delegated_to.2@coffeebean.com'
                    ],
                ]
            )
            ->setAttendee(
                'participant2@coffeebean.com',
                [
                    IcalInterface::ROLE => IcalInterface::REQ_PARTICIPANT,
                    IcalInterface::PARTSTAT => IcalInterface::NEEDS_ACTION,
                    IcalInterface::RSVP => IcalInterface::TRUE,
                    IcalInterface::CN => 'Participant2 CoffeeBean',
                ]
            );
        // add an alarm for the todo
        // fire off the alarm one day before
        $alarm = $todo1->newValarm( IcalInterface::DISPLAY, '-P1D' )
            ->setDescription( $todo1->getDescription());

        // alter day and time for one todo in recurrence set
        // the altered day and time with duration
        $event2 = $vcalendar->newVtodo(
            new DateTime( '20190504T100000', new DateTimeZone( 'Europe/Stockholm' )),
            null,
            'PT2H'
        )
            ->setClass( IcalInterface::P_BLIC )
            // reference to one event in recurrence set
            ->setUid( $todo1->getUid() )
            ->setSequence( 2 )
            // pointer to event in the recurrence set
            ->setRecurrenceid( '20190505T090000 Europe/Stockholm' )
            // reason text
            ->setDescription(
                'Altered day and time for event 2019-05-05',
                [ IcalInterface::ALTREP => 'CID:<FFFF__=0ABBE548DFE235B58f9e8a93d@coffeebean.com>' ]
            )
            ->setComment( 'Now we are working hard for two hours' )
            // add alarm (i.e. same as event1)
            ->setComponent( $todo1->getComponent( IcalInterface::VALARM ) );

        // apply appropriate Vtimezone with Standard/DayLight components
        $vcalendar->vtimezonePopulate();

        return $vcalendar;
    }

    /**
     * SelectComponentsTest provider
     *
     * @return mixed[]
     * @throws Exception
     */
    public function SelectCompTestProvider() : array
    {
        $veventCalendar = self::veventCalendarSubProvider();
        $todoCalendar   = self::vtodoCalendarSubProvider();

        $dataArr = [];

        $dataArr[] = [
            11,
            clone $veventCalendar,
            null
        ];

        $dataArr[] = [
            12,
            clone $veventCalendar,
            strtolower( IcalInterface::VEVENT )
        ];

        $dataArr[] = [
            13,
            clone $veventCalendar,
            [ strtolower( IcalInterface::VEVENT ), strtolower( IcalInterface::VTODO ) ]
        ];

        $dataArr[] = [
            21,
            clone $todoCalendar,
            null
        ];

        $dataArr[] = [
            22,
            clone $todoCalendar,
            strtolower( IcalInterface::VTODO )
        ];

        $dataArr[] = [
            23,
            clone $todoCalendar,
            [ strtolower( IcalInterface::VEVENT ), strtolower( IcalInterface::VTODO ) ]
        ];

        return $dataArr;
    }

    /**
     * Testing Vcalendar::selectComponents
     *
     * @test
     * @dataProvider SelectCompTestProvider
     * @param int $case
     * @param Vcalendar    $vcalendar
     * @param mixed[]|string|null $compType
     * @throws Exception
     */
    public function SelectCompTest( int $case, Vcalendar $vcalendar, array | string $compType = null ) : void
    {
        static $FMTerr = 'error in case#%d-%d, date %d-%d-%d';

        $selectComponents = $vcalendar->selectComponents(
            new DateTime( '20190421T000000', new DateTimeZone( 'Europe/Stockholm' )),
            new DateTime( '20190630T000000', new DateTimeZone( 'Europe/Stockholm' ))
            ,null, null, null, null,
            $compType
        );
        /*
        foreach( $selectComponents[2019] as $month => $monthArr ) {
            foreach( $monthArr as $day => $dayArr ) {
                foreach( $dayArr as $no => $comp ) {
                    echo 'keys found 2019-' . $month . '-' . $day . ' #' . $no . PHP_EOL; // test ###
                }
            }
        }
        */
// 2019-04-21
        $this->assertTrue(
            isset( $selectComponents[2019][4][21][0] ),
            sprintf( $FMTerr, $case, 10, 2019, 4, 21 )
        );

        $this->assertEquals(
            '2019-04-21 09:00:00 Europe/Stockholm',
            $selectComponents[2019][4][21][0]->getXprop( IcalInterface::X_CURRENT_DTSTART )[1],
            sprintf( $FMTerr, $case, 11, 2019, 4, 21 )
        );
        if( false === ( $value = $selectComponents[2019][4][21][0]->getXprop( IcalInterface::X_CURRENT_DTEND ))) {
            $value = $selectComponents[2019][4][21][0]->getXprop( IcalInterface::X_CURRENT_DUE );
        }
        $this->assertEquals(
            '2019-04-21 10:00:00 Europe/Stockholm',
            $value[1],
            sprintf( $FMTerr, $case, 12, 2019, 4, 21 )
        );

// 2019-04-28
        $this->assertTrue( isset( $selectComponents[2019][4][28][0] ));
        $this->assertEquals(
            2,
            $selectComponents[2019][4][28][0]->getXprop( IcalInterface::X_RECURRENCE )[1],
            sprintf( $FMTerr, $case, 13, 2019, 4, 28 )
        );
        $this->assertEquals(
            '2019-04-28 09:00:00 Europe/Stockholm',
            $selectComponents[2019][4][28][0]->getXprop( IcalInterface::X_CURRENT_DTSTART )[1],
            sprintf( $FMTerr, $case, 14, 2019, 4, 28 )
        );
        if( false === ( $value = $selectComponents[2019][4][28][0]->getXprop( IcalInterface::X_CURRENT_DTEND ))) {
            $value = $selectComponents[2019][4][28][0]->getXprop( IcalInterface::X_CURRENT_DUE );
        }
        $this->assertEquals(
            '2019-04-28 10:00:00 Europe/Stockholm',
            $value[1],
            sprintf( $FMTerr, $case, 15, 2019, 4, 28 )
        );

// 2019-05-04
        $this->assertTrue( isset( $selectComponents[2019][5][4][0] ));
        $this->assertEquals(
            3,
            $selectComponents[2019][5][4][0]->getXprop( IcalInterface::X_RECURRENCE )[1],
            sprintf( $FMTerr, $case, 16, 2019, 5, 4 )
        );
        $this->assertEquals(
            '2019-05-04 10:00:00 Europe/Stockholm',
            $selectComponents[2019][5][4][0]->getXprop( IcalInterface::X_CURRENT_DTSTART )[1],
            sprintf( $FMTerr, $case, 17, 2019, 5, 4 )
        );
        if( false === ( $value = $selectComponents[2019][5][4][0]->getXprop( IcalInterface::X_CURRENT_DTEND ))) {
            $value = $selectComponents[2019][5][4][0]->getXprop( IcalInterface::X_CURRENT_DUE );
        }
        $this->assertEquals(
            '2019-05-04 12:00:00 Europe/Stockholm',
            $value[1],
            sprintf( $FMTerr, $case, 18, 2019, 5, 4 )
        );

// 2019-05-19
        $this->assertTrue( isset( $selectComponents[2019][5][19][0] ));
        $this->assertEquals(
            4,
            $selectComponents[2019][5][19][0]->getXprop( IcalInterface::X_RECURRENCE )[1],
            sprintf( $FMTerr, $case, 19, 2019, 5, 19 )
        );
        $this->assertEquals(
            '2019-05-19 09:00:00 Europe/Stockholm',
            $selectComponents[2019][5][19][0]->getXprop( IcalInterface::X_CURRENT_DTSTART )[1],
            sprintf( $FMTerr, $case, 20, 2019, 5, 19 )
        );
        if( false === ( $value = $selectComponents[2019][5][19][0]->getXprop( IcalInterface::X_CURRENT_DTEND ))) {
            $value = $selectComponents[2019][5][19][0]->getXprop( IcalInterface::X_CURRENT_DUE );
        }
        $this->assertEquals(
            '2019-05-19 10:00:00 Europe/Stockholm',
            $value[1],
            sprintf( $FMTerr, $case, 21, 2019, 5, 19 )
        );

// 2019-06-09
        $this->assertTrue( isset( $selectComponents[2019][6][9][0] ));
        $this->assertEquals(
            5,
            $selectComponents[2019][6][9][0]->getXprop( IcalInterface::X_RECURRENCE )[1],
            sprintf( $FMTerr, $case, 22, 2019, 6, 9 )
        );
        $this->assertEquals(
            '2019-06-09 09:00:00 Europe/Stockholm',
            $selectComponents[2019][6][9][0]->getXprop( IcalInterface::X_CURRENT_DTSTART )[1],
            sprintf( $FMTerr, $case, 23, 2019, 6, 9 )
        );
        if( false === ( $value = $selectComponents[2019][6][9][0]->getXprop( IcalInterface::X_CURRENT_DTEND ))) {
            $value = $selectComponents[2019][6][9][0]->getXprop( IcalInterface::X_CURRENT_DUE );
        }
        $this->assertEquals(
            '2019-06-09 11:00:00 Europe/Stockholm',
            $value[1],
            sprintf( $FMTerr, $case, 24, 2019, 6, 9 )
        );
    }
}
