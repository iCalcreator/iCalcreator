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

use PHPUnit\Framework\TestCase;

/**
 * class Prop3Comp2PropTest
 *
 * Test (sub-)components to properties
 */
class Prop3Comp2PropTest extends TestCase
{
    /**
     * @var string
     */
    private static string $ERRFMT   = "Error %sin case #%s, %s <%s>->%s";

    /**
     * Test Vevent::vresourceNames2Resources() etc
     *
     * Found in V3component (Vevent, Vtodo)
     *
     * @test
     */
    public function vresourceNames2ResourcesTest() : void
    {
        $case         = 100;
        $resourceType = 'resource ' . IcalInterface::NAME;
        $resourceName = 'any ' . IcalInterface::RESOURCE_TYPE;
        $calendar     = new Vcalendar();
        $event        = $calendar->newVevent();
        $vresource    = $event->newVresource( $resourceType, $resourceName );

        $this->assertTrue(
            $vresource->isNameSet(),
            sprintf( self::$ERRFMT, null, $case . '-11-1-name', __FUNCTION__, IcalInterface::VRESOURCE, IcalInterface::NAME )
        );
        $this->assertSame(
            $resourceName,
            $vresource->getName(),
            sprintf( self::$ERRFMT, null, $case . '-11-2-name', __FUNCTION__, IcalInterface::VRESOURCE, IcalInterface::NAME )
        );
        $vresource->deleteName();
        $this->assertfalse(
            $vresource->isNameSet(),
            sprintf( self::$ERRFMT, null, $case . '-11-3-name', __FUNCTION__, IcalInterface::VRESOURCE, IcalInterface::NAME )
        );
        $vresource->setName( $resourceName );

        $this->assertTrue(
            $vresource->isResourcetypeSet(),
            sprintf( self::$ERRFMT, null, $case . '-11-4-type', __FUNCTION__, IcalInterface::VRESOURCE, IcalInterface::NAME )
        );
        $this->assertSame(
            $resourceType,
            $vresource->getResourcetype(),
            sprintf( self::$ERRFMT, null, $case . '-11-5-type', __FUNCTION__, IcalInterface::VRESOURCE, IcalInterface::RESOURCE_TYPE )
        );

        $this->assertFalse(
            $event->isResourcesSet(),
            sprintf( self::$ERRFMT, null, $case . '-11-6-resurces', __FUNCTION__, IcalInterface::VEVENT, IcalInterface::RESOURCES )
        );
        $event->vresourceNames2Resources();
        $this->assertTrue(
            $event->isResourcesSet(),
            sprintf( self::$ERRFMT, null, $case . '-11-7-resurces', __FUNCTION__, IcalInterface::VEVENT, IcalInterface::RESOURCES )
        );

        $resource = $event->getResources( null, true );

        $this->assertSame(
            $resourceName,
            $resource->getValue(),
            sprintf( self::$ERRFMT, null, $case . '-11-8-name', __FUNCTION__, IcalInterface::VEVENT, 'vresourceNames2Resources name' )
        );
        $this->assertSame(
            $resourceType,
            $resource->getParams( IcalInterface::X_RESOURCE_TYPE ),
            sprintf( self::$ERRFMT, null, $case . '-11-9-type', __FUNCTION__, IcalInterface::VEVENT, 'vresourceNames2Resources type' )
        );
        $this->assertSame(
            $vresource->getUid(),
            $resource->getParams( IcalInterface::X_VRESOURCEID ),
            sprintf( self::$ERRFMT, null, $case . '-11-10-uid', __FUNCTION__, IcalInterface::VEVENT, 'vresourceNames2Resources uid' )
        );
    }

    /**
     * Test Vevent::vlocationNames2Location() etc
     *
     * Found in V3component (Vevent, Vtodo)
     *
     * @test
     */
    public function vlocationNames2LocationTest() : void
    {
        $case         = 200;
        $locationName = 'any ' . IcalInterface::LOCATION;
        $locationType = 'any ' . IcalInterface::LOCATION_TYPE;
        $calendar     = new Vcalendar();
        $event        = $calendar->newVevent();
        $vlocation    = $event->newVlocation( $locationType, $locationName );

        $this->assertFalse(
            $event->isLocationSet(),
            sprintf( self::$ERRFMT, null, $case . '-12-1-resurces', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );

        $event->vlocationNames2Location();
        $this->assertTrue(
            $event->isLocationSet(),
            sprintf( self::$ERRFMT, null, $case . '-12-2-resurces', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );

        $location     = $event->getLocation( null, true );
        $this->assertEquals(
            $locationName,
            $location->value,
            sprintf( self::$ERRFMT, null, $case . '-12-3', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );

        $this->assertTrue(
            $location->hasXparamKey( IcalInterface::X_VLOCATIONID ),
            sprintf( self::$ERRFMT, null, $case . '-12-4', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );
        $this->assertEquals(
            $vlocation->getUid(),
            $location->getParams( IcalInterface::X_VLOCATIONID ),
            sprintf( self::$ERRFMT, null, $case . '-12-5', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );

        $this->assertTrue(
            $location->hasXparamKey( IcalInterface::X_LOCATION_TYPE ),
            sprintf( self::$ERRFMT, null, $case . '-12-6', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );
        $this->assertEquals(
            $locationType,
            $location->getParams( IcalInterface::X_LOCATION_TYPE ),
            sprintf( self::$ERRFMT, null, $case . '-12-7', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );
    }

    /**
     * Test Vevent::participants2Attendees() etc
     *
     * Found in V3component (Vevent, Vtodo), Vfreebusy, Vjournal
     *
     * @test
     */
    public function participants2AttendeesTest() : void
    {
        $case            = 300;
        $calendarAddress = 'MAILTO::calendar.address@internet.com';
        $calendar        = new Vcalendar();
        $event           = $calendar->newVevent();
        $participant     = $event->newParticipant( IcalInterface::PARTICIPANT_TYPE, $calendarAddress );

        $this->assertFalse(
            $event->isAttendeeSet(),
            sprintf( self::$ERRFMT, null, $case . '-13-1-attendee', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );
        $event->participants2Attendees();
        $this->assertTrue(
            $event->isAttendeeSet(),
            sprintf( self::$ERRFMT, null, $case . '-13-2-attendee', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );

        $attendee = $event->getAttendee( null, true );
        $this->assertEquals(
            $calendarAddress,
            $attendee->value,
            sprintf( self::$ERRFMT, null, $case . '-13-2', __FUNCTION__, IcalInterface::VEVENT, 'participants2Attendees' )
        );

        $this->assertTrue(
            $attendee->hasXparamKey( IcalInterface::X_PARTICIPANTID ),
            sprintf( self::$ERRFMT, null, $case . '-13-3', __FUNCTION__, IcalInterface::VEVENT, 'participants2Attendees' )
        );
        $this->assertEquals(
            $participant->getUid(),
            $attendee->getParams( IcalInterface::X_PARTICIPANTID ),
            sprintf( self::$ERRFMT, null, $case . '-13-4', __FUNCTION__, IcalInterface::VEVENT, 'participants2Attendees' )
        );

        $this->assertTrue(
            $attendee->hasXparamKey( IcalInterface::X_PARTICIPANT_TYPE ),
            sprintf( self::$ERRFMT, null, $case . '-13-5', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );
        $this->assertEquals(
            IcalInterface::PARTICIPANT_TYPE,
            $attendee->getParams( IcalInterface::X_PARTICIPANT_TYPE ),
            sprintf( self::$ERRFMT, null, $case . '-13-6', __FUNCTION__, IcalInterface::VEVENT, 'vlocationNames2Location' )
        );
    }
}
