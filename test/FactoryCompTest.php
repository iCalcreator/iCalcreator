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

use DateInterval;
use DateTime;
use DateTimeZone;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use PHPUnit\Framework\TestCase;

class FactoryCompTest extends TestCase
{
    private static string $ERR1 = '%s error case %d (%s)';
    private static string $ERR2 = '%s error case %d-%d (%s)';
    
    private static ? DateTime $dtStart;
    private static ? string $dtStartTxt;
    private static ? DateTime $dtEnd;
    private static ? string $dtEndTxt;
    private static ? string $PT1H     = 'PT1H';
    private static ? DateInterval $duration;
    private static ? string $extra    = 'This is a summary';
    private static ? string $attendee = 'MAILTO:some.one@internet.com';
    private static ? string $dtStartTxtUtc;
    private static ? string $dtEndTxtUtc;

    private static ? Vcalendar $calendar;

    public static function setUpBeforeClass() : void
    {
        self::$dtStart       = new DateTime( 'now' );
        self::$dtStartTxt    = self::$dtStart->format( DateTimeFactory::$YMDHISe );
        self::$dtEnd         = new DateTime( '+1 hour' );
        self::$dtEndTxt      = self::$dtEnd->format( DateTimeFactory::$YMDHISe );
        self::$duration      = new DateInterval( self::$PT1H );
        $utcTz               = new DateTimeZone( 'UTC' );
        self::$dtStartTxtUtc = ( clone self::$dtStart )->setTimezone( $utcTz )->format( DateTimeFactory::$YMDHISe );
        self::$dtEndTxtUtc   = ( clone self::$dtEnd )->setTimezone( $utcTz )->format( DateTimeFactory::$YMDHISe );
    }

    public static function tearDownAfterClass() : void
    {
        self::$dtStart       = null;
        self::$dtStartTxt    = null;
        self::$dtEnd         = null;
        self::$dtEndTxt      = null;
        self::$duration      = null;
        self::$dtStartTxtUtc = null;
        self::$dtEndTxtUtc   = null;
        self::$calendar      = null;
    }

    public function setUp() : void
    {
        self::$calendar = new Vcalendar();
    }

    /**
     * Test Vcalendar::newVevent() all args
     *
     * @test
     */
    public function factoryVeventTest() : void
    {

        $this->assertFalse(
            self::$calendar->newVevent()->getDtstart(),
            sprintf( self::$ERR1, __FUNCTION__, 0, 'no args' )
        );

        $this->assertSame(
            self::$dtStartTxt,
            self::$calendar->newVevent( self::$dtStart )
                ->getDtstart()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 1,'dtstart' )
        );

        $this->assertSame(
            self::$dtEndTxt,
            self::$calendar->newVevent( null, self::$dtEnd )
                ->getDtend()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 2, 'dtend' )
        );

        $this->assertSame(
            self::$PT1H,
            DateIntervalFactory::dateInterval2String(
                self::$calendar->newVevent( null, null, self::$PT1H )
                    ->getDuration()
            ),
            sprintf( self::$ERR1, __FUNCTION__, 3, 'duration' )
        );

        $vevent = self::$calendar->newVevent( null, self::$dtEnd, self::$duration );
        $this->assertSame(
            self::$dtEndTxt,
            $vevent->getDtend()->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 4, 'dtend' )
        );
        $this->assertFalse(
            $vevent->getDuration(),
            sprintf( self::$ERR1, __FUNCTION__, 5, 'duraton' )
        );

        $this->assertSame(
            self::$extra,
            self::$calendar->newVevent( null, null, null, self::$extra )
                ->getSummary(),
            sprintf( self::$ERR1, __FUNCTION__, 6, 'summary' )
        );
    }

    /**
     * Test Vcalendar::newVtodo() all args
     *
     * @test
     */
    public function factoryVtodoTest() : void
    {

        $this->assertFalse(
            self::$calendar->newVtodo()->getDtstart(),
            sprintf( self::$ERR1, __FUNCTION__, 0, 'no args' )
        );

        $this->assertSame(
            self::$dtStartTxt,
            self::$calendar->newVtodo( self::$dtStart )
                ->getDtstart()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 1, 'dtstart' )
        );

        $this->assertSame(
            self::$dtEndTxt,
            self::$calendar->newVtodo( null, self::$dtEnd )
                ->getDue()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1,__FUNCTION__, 2, 'due' )
        );

        $this->assertSame(
            self::$PT1H,
            DateIntervalFactory::dateInterval2String(
                self::$calendar->newVtodo( null, null, self::$PT1H )
                    ->getDuration()
            ),
            sprintf( self::$ERR1, __FUNCTION__, 3, 'duration' )
        );

        $vtodo = self::$calendar->newVtodo( null, self::$dtEnd, self::$duration );
        $this->assertSame(
            self::$dtEndTxt,
            $vtodo->getDue()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 4, 'due' )
        );
        $this->assertFalse(
            $vtodo->getDuration(),
            sprintf( self::$ERR1, __FUNCTION__, 5, 'duration' )
        );

        $this->assertSame(
            self::$extra,
            self::$calendar->newVtodo( null, null, null, self::$extra )
                ->getSummary(),
            sprintf( self::$ERR1, __FUNCTION__, 6, 'summary' )
        );
    }

    /**
     * Test Vcalendar::newVfreebusy() all args
     *
     * @test
     */
    public function factoryVfreebusyTest() : void
    {
        $this->assertFalse(
            self::$calendar->newVfreebusy()->getAttendee(),
            sprintf( self::$ERR1, __FUNCTION__, 0, 'no args' )
        );

        $this->assertSame(
            self::$attendee,
            self::$calendar->newVfreebusy( self::$attendee )
                ->getAttendee(),
            sprintf( self::$ERR1, __FUNCTION__, 1, 'attendee' )
        );
        $this->assertSame(
            self::$dtStartTxtUtc,
            self::$calendar->newVfreebusy( null, self::$dtStart )
                ->getDtstart()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 2, 'dtstart' )
        );

        $this->assertSame(
            self::$dtEndTxtUtc,
            self::$calendar->newVfreebusy( null, null, self::$dtEnd )
                ->getDtend()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1,__FUNCTION__, 3, 'dtend' )
        );
    }

    /**
     * Test Vcalendar::newVjournal() all args
     *
     * @test
     */
    public function factoryVjournalTest() : void
    {
        $this->assertFalse(
            self::$calendar->newVjournal()->getDtstart(),
            sprintf( self::$ERR1, __FUNCTION__, 0, 'no args' )
        );

        $this->assertSame(
            self::$dtStartTxt,
            self::$calendar->newVjournal( self::$dtStart )
                ->getDtstart()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 1, 'dtstart')
        );
        $this->assertSame(
            self::$extra,
            self::$calendar->newVjournal( null, self::$extra )
                ->getSummary(),
            sprintf( self::$ERR1, __FUNCTION__, 2, 'summary' )
        );
    }

    /**
     * Test Vevent/Vtodo/Vjournal/Vfreebusy::newValarm() all args
     *
     * @test
     */
    public function factoryValarmTest() : void
    {
        static $comps  = [
            IcalInterface::VEVENT,
            IcalInterface::VTODO,
        ];
        static $NEW    = 'new';
        static $action = IcalInterface::DISPLAY;
        foreach(  $comps as $x => $comp ) {
            $newComp = $NEW . $comp;
            $this->assertFalse(
                self::$calendar->{$newComp}()->newValarm()->getAction(),
                sprintf( self::$ERR2, __FUNCTION__, 0, ( 1 + $x ), 'no args' )
            );
            $this->assertSame(
                $action,
                self::$calendar->{$newComp}()->newValarm( $action )
                    ->getAction(),
                sprintf( self::$ERR2, __FUNCTION__, 1, ( 1 + $x ), 'action' )
            );
            $this->assertSame(
                self::$dtStartTxtUtc,
                self::$calendar->{$newComp}()->newValarm( null, self::$dtStart )
                    ->getTrigger()
                    ->format( DateTimeFactory::$YMDHISe ),
                sprintf( self::$ERR2, __FUNCTION__, 2, ( 1 + $x ), 'trigger' )
            );
        }
    }

    /**
     * Test Vcalendar::newVavailability() all args
     *
     * @test
     */
    public function factoryVavailabilityTest() : void
    {
        static $busyType = 'BUSY';

        $this->assertFalse(
            self::$calendar->newVavailability()
                ->getBusytype(),
            sprintf( self::$ERR1, __FUNCTION__, 0, 'no args' )
        );

        $this->assertSame(
            $busyType,
            self::$calendar->newVavailability( $busyType )
                ->getBusytype(),
            sprintf( self::$ERR1, __FUNCTION__, 1, 'busytype' )
        );

        $this->assertSame(
            self::$dtStartTxt,
            self::$calendar->newVavailability( $busyType, self::$dtStart )
                ->getDtstart()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 2, 'dtstart' )
        );

        $this->assertSame(
            self::$dtEndTxt,
            self::$calendar->newVavailability( null, null, self::$dtEnd )
                ->getDtend()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 3, 'dtend' )
        );

        $this->assertSame(
            self::$PT1H,
            DateIntervalFactory::dateInterval2String(
                self::$calendar->newVavailability( null, null, null, self::$PT1H )
                    ->getDuration()
            ),
            sprintf( self::$ERR1, __FUNCTION__, 4, 'duration' )
        );

        $availability = self::$calendar->newVevent( null, self::$dtEnd, self::$duration );
        $this->assertSame(
            self::$dtEndTxt,
            $availability->getDtend()->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 5, 'dtend' )
        );
        $this->assertFalse(
            $availability->getDuration(),
            sprintf( self::$ERR1, __FUNCTION__, 6, 'duration' )
        );
    }

    /**
     * Test Vavailability::newAvailable() all args
     *
     * @test
     */
    public function factoryAvailableTest() : void
    {
        $this->assertFalse(
            self::$calendar->newVavailability()->newAvailable()
                ->getDtstart(),
            sprintf( self::$ERR1, __FUNCTION__, 0, 'no args' )
        );

        $this->assertSame(
            self::$dtStartTxt,
            self::$calendar->newVavailability()->newAvailable( self::$dtStart )
                ->getDtstart()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 1, 'dtstart' )
        );

        $this->assertSame(
            self::$dtEndTxt,
            self::$calendar->newVavailability()->newAvailable( null, self::$dtEnd )
                ->getDtend()
                ->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 2, 'dtend' )
        );

        $this->assertSame(
            self::$PT1H,
            DateIntervalFactory::dateInterval2String(
                self::$calendar->newVavailability()->newAvailable( null, null, self::$PT1H )
                    ->getDuration()
            ),
            sprintf( self::$ERR1, __FUNCTION__, 3, 'duration' )
        );

        $available = self::$calendar->newVavailability()->newAvailable( null, self::$dtEnd, self::$duration );
        $this->assertSame(
            self::$dtEndTxt,
            $available->getDtend()->format( DateTimeFactory::$YMDHISe ),
            sprintf( self::$ERR1, __FUNCTION__, 4, 'dend' )
        );
        $this->assertFalse(
            $available->getDuration(),
            sprintf( self::$ERR1, __FUNCTION__, 5, 'duration' )
        );
    }

    /**
     * Test Vevent/Vtodo/Vournal/Vfreebusy::newParticipant() all args
     *
     * @test
     */
    public function factoryParticipantTest() : void
    {
        static $comps = [
            IcalInterface::VEVENT,
            IcalInterface::VTODO,
            IcalInterface::VJOURNAL,
            IcalInterface::VFREEBUSY
        ];
        static $NEW            = 'new';
        static $particpantType = IcalInterface::CONTACT;
        foreach(  $comps as $x => $comp ) {
            $newComp = $NEW . $comp;
            $this->assertFalse(
                self::$calendar->{$newComp}()->newParticipant()
                    ->getParticipanttype(),
                sprintf( self::$ERR2, __FUNCTION__, 0, ( 1 + $x ), 'no args' )
            );

            $this->assertSame(
                $particpantType,
                self::$calendar->{$newComp}()->newParticipant( $particpantType )
                    ->getParticipanttype(),
                sprintf( self::$ERR2, __FUNCTION__, 1, ( 1 + $x ), 'particpantType' )
            );
            $this->assertSame(
                self::$attendee,
                self::$calendar->{$newComp}()->newParticipant( null, self::$attendee )
                    ->getCalendaraddress(),
                sprintf( self::$ERR2, __FUNCTION__, 2, ( 1 + $x ), 'calendaraddress' )
            );
        } // end foreach
    }

    /**
     * Test Vevent/Vtodo/Vournal/Vfreebusy/Participant::newVlocation() all args
     *
     * @test
     */
    public function factoryVlocationTest() : void
    {
        static $comps = [
            IcalInterface::VEVENT,
            IcalInterface::VTODO,
            IcalInterface::VJOURNAL,
            IcalInterface::VFREEBUSY
        ];
        static $NEW          = 'new';
        static $locationType = 'residence';
        static $name         = 'Any One';
        foreach(  $comps as $x => $theComp ) {
            $newComp = $NEW . $theComp;
            $comp = self::$calendar->{$newComp}();
            $this->assertFalse(
                $comp->newVlocation()
                    ->getLocationtype(),
                sprintf( self::$ERR2, __FUNCTION__, 0, ( 1 + $x ), 'no args' )
            );
            $this->assertSame(
                $locationType,
                $comp->newVlocation( $locationType )
                    ->getLocationtype(),
                sprintf( self::$ERR2, __FUNCTION__, 1, ( 1 + $x ), 'locationType' )
            );
            $this->assertSame(
                $name,
                $comp->newVlocation( null, $name )
                    ->getName(),
                sprintf( self::$ERR2, __FUNCTION__, 2, ( 1 + $x ), 'name' )
            );

            // Valarm
            if( in_array( $theComp, [ IcalInterface::VEVENT, IcalInterface::VTODO ], true ) ) {
                $comp = self::$calendar->{$newComp}()->newValarm();
                $this->assertSame(
                    $locationType,
                    $comp->newVlocation( $locationType )
                        ->getLocationtype(),
                    sprintf( self::$ERR2, __FUNCTION__, 3, ( 1 + $x ), 'locationType' )
                );
                $this->assertSame(
                    $name,
                    $comp->newVlocation( null, $name )
                        ->getName(),
                    sprintf( self::$ERR2,__FUNCTION__, 4, ( 1 + $x ), 'name' )
                );
            }

            // Participant
            $comp = self::$calendar->{$newComp}()->newParticipant();
            $this->assertSame(
                $locationType,
                $comp->newVlocation( $locationType )
                    ->getLocationtype(),
                sprintf( self::$ERR2, __FUNCTION__, 5, ( 1 + $x ), 'locationType' )
            );
            $this->assertSame(
                $name,
                $comp->newVlocation( null, $name )
                    ->getName(),
                sprintf( self::$ERR2,__FUNCTION__, 6, ( 1 + $x ), 'name' )
            );
        } // end foreach
    }

    /**
     * Test Vevent/Vtodo/Vournal/Vfreebusy/Participant::newVresource() all args
     *
     * @test
     */
    public function factoryVresourceTest() : void
    {
        static $comps = [
            IcalInterface::VEVENT,
            IcalInterface::VTODO,
            IcalInterface::VJOURNAL,
            IcalInterface::VFREEBUSY
        ];
        static $NEW          = 'new';
        static $resourceType = IcalInterface::ROOM;
        static $name         = 'Any One';
        foreach(  $comps as $x => $comp ) {
            $newComp = $NEW . $comp;
            $this->assertFalse(
                self::$calendar->{$newComp}()->newVresource()
                    ->getResourcetype(),
                sprintf( self::$ERR2, __FUNCTION__, 0, ( 1 + $x ), 'no args' )
            );
            $this->assertSame(
                $resourceType,
                self::$calendar->{$newComp}()->newVresource( $resourceType )
                    ->getResourcetype(),
                sprintf( self::$ERR2,__FUNCTION__, 1, ( 1 + $x ), 'resourceType' )
            );
            $this->assertSame(
                $name,
                self::$calendar->{$newComp}()->newVresource( null, $name )
                    ->getName(),
                sprintf( self::$ERR2, __FUNCTION__, 2, ( 1 + $x ), 'name' )
            );

            $this->assertSame(
                $resourceType,
                self::$calendar->{$newComp}()->newParticipant()->newVresource( $resourceType )
                    ->getResourcetype(),
                sprintf( self::$ERR2, __FUNCTION__, 3, ( 1 + $x ), 'resourceType' )
            );
            $this->assertSame(
                $name,
                self::$calendar->{$newComp}()->newParticipant()->newVresource( null, $name )
                    ->getName(),
                sprintf( self::$ERR2, __FUNCTION__, 4, ( 1 + $x ), 'name' )
            );
        } // end foreach
    }

    /**
     * Test Vcalendar::newVtimezone() arg
     *
     * @test
     */
    public function factoryVtimezoneTest() : void
    {
        $tz = 'Europe/Stckholm';

        $this->assertFalse(
            self::$calendar->newVtimezone()->getTzid(),
            sprintf( self::$ERR1, __FUNCTION__, 1, 'no arg' )
        );

        $this->assertSame(
            $tz,
            self::$calendar->newVtimezone( $tz )->getTzid(),
            sprintf( self::$ERR1, __FUNCTION__, 2, 'tz' )
        );

    }
}
