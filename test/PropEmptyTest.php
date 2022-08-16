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

class PropEmptyTest extends DtBase
{
    /**
     * Testing empty properties
     *
     * @test
     * @throws Exception
     * @since 2.41.44 2022-04-21
     */
    public function emptyTest5() : void
    {
        $c = Vcalendar::factory()
            ->setCalscale( 'gregorian' )
            ->setMethod( 'testing' )
            ->setXprop( 'X-vcalendar-empty' )

            ->setUid()
            ->setLastmodified()
            ->setUrl()
            ->setRefreshinterval()
            ->setSource()
            ->setColor()

            ->setName()
            ->setDescription()
            ->setCategories()
            ->setImage();

        $tz = $c->newVtimezone()
            ->setTzid()
            ->setTzuntil()
            ->setTzurl()
            ->setTzidAliasOf();

        $o1 = $c->newVevent()
               ->setClass()
               ->setComment()
               ->setCreated()
               ->setDtstart()
               ->setDuration()
               ->setGeo()
               ->setExrule()
               ->setRrule()
               ->setExdate()
               ->setOrganizer()
               ->setRdate()
               ->setPriority()
               ->setResources()
               ->setSummary()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-1-vevent1-empty' );

        $a1 = $o1->newValarm()
                ->setAction()
                ->setAttach()
                ->setDuration()
                ->setRepeat()
                ->setTrigger()
                ->setXprop( 'X-2-valarm1-1-empty' );

        $a2 = $o1->newValarm()
                ->setAction()
                ->setDescription()
                ->setDuration()
                ->setRepeat()
                ->setTrigger()
                ->setXprop( 'X-3-valarm1-2-empty' );

        $o2 = $c->newVevent()
               ->setConfig( 'language', 'fr' )
               ->setAttendee()
               ->setAttendee()
               ->setComment()
               ->setComment()
               ->setComment()
               ->setDtstart()
               ->setDuration()
               ->setOrganizer()
               ->setStatus()
               ->setTransp()
               ->setUid()
               ->setUrl()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-ABC-MMSUBJ' )
               ->setXprop( 'X-4-vevent2-empty' );

        $o3 = $c->newVtodo()
               ->setComment()
               ->setCompleted()
               ->setDtstart()
               ->setDuration()
               ->setLocation()
               ->setOrganizer()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-5-vtodo1-empty' );

        $o4 = $c->newVevent()
               ->setCategories()
               ->setCategories()
               ->setComment()
               ->setDtstart()
               ->setDtend()
               ->setExdate()
               ->setRrule()
               ->setExdate()
               ->setRdate()
               ->setLastmodified()
               ->setOrganizer()
               ->setRecurrenceid()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-6-vevent3-empty' );

        $o5 = $c->newVjournal()
               ->setComment()
               ->setContact()
               ->setContact()
               ->setDtstart()
               ->setLastmodified()
               ->setRecurrenceid()
               ->setRequeststatus()

               ->setImage()
               ->setColor()

               ->setXprop( 'X-7-vjournal1-empty' );

        $o6 = $c->newVfreebusy()
               ->setComment()
               ->setContact()
               ->setDtstart()
               ->setDuration()
               ->setFreebusy()
               ->setOrganizer()
               ->setXprop( 'X-8-vfreebusy-empty' );

        $o7 = $c->newVtodo()
               ->setComment()
               ->setContact()
               ->setDtstart()
               ->setDue()
               ->setOrganizer()
               ->setPercentcomplete()
               ->setRelatedto()
               ->setSequence()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-9-vtodo2-empty' );

        $o8 = $c->newVjournal()
               ->setComment()
               ->setContact()
               ->setContact()
               ->setDtstart()
               ->setLastmodified()
               ->setRequeststatus()

               ->setImage()
               ->setColor()

               ->setXprop( 'X-10-vjournal2-empty' );

        $o9 = $c->newVtodo()
               ->setComment()
               ->setContact()
               ->setDtstart()
               ->setDuration()
               ->setOrganizer()
               ->setPercentcomplete()
               ->setRelatedto()
               ->setSequence()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-11-vtodo3-empty' );

        $o10 = $c->newVevent()
            ->setXprop( 'X-12-vevent4-empty' );
        $o11 = $o10->newParticipant()
            ->setParticipanttype()
            ->setCalendaraddress()
            ->setContact()
            ->setLocation()
            ->setCreated()
            ->setSummary()
            ->setDescription()
            ->setStyleddescription()
            ->setStructureddata()
            ->setGeo()
            ->setLastmodified()
            ->setPriority()
            ->setSequence()
            ->setStatus()
            ->setUrl()
            ->setAttach()
            ->setCategories()
            ->setComment()
            ->setRequeststatus()
            ->setRelatedto()
            ->setResources()
            ->setXprop( 'X-13-vevent4-participant1-empty' );
        $o12 = $o11->newVlocation()
            ->setDescription()
            ->setGeo()
            ->setLocationtype()
            ->setName()
            ->setStructureddata()
            ->setXprop( 'X-14-vevent4-participant1-vlocation1-empty' );
        $o13 = $o11->newVresource()
            ->setDescription()
            ->setGeo()
            ->setName()
            ->setResourcetype()
            ->setStructureddata()
            ->setXprop( 'X-15-vevent4-participant1-vresource1-empty' );
        $o14 = $o10->newVlocation()
            ->setDescription()
            ->setGeo()
            ->setLocationtype()
            ->setName()
            ->setStructureddata()
            ->setXprop( 'X-16-vevent4-vlocation1-empty' );
        $o15 = $o10->newVresource()
            ->setDescription()
            ->setGeo()
            ->setName()
            ->setResourcetype()
            ->setStructureddata()
            ->setXprop( 'X-17-vevent4-vresource1-empty' );

        $o16 = $c->newVtodo()
            ->setXprop( 'X-18-todo4-empty' );
        $o17 = $o16->newVlocation()
            ->setXprop( 'X-19-todo4-vlocation1-empty' );
        $o18 = $o16->newParticipant()
            ->setXprop( 'X-19-todo4-participant1-empty' );
        $o19 = $o18->newVlocation()
            ->setXprop( 'X-20-todo4-participant1-vlocation1-empty' );
        $o20 = $o16->newVlocation()
            ->setXprop( 'X-21-todo4-vlocation2-empty' );
        $o21 = $o16->newVlocation()
            ->setXprop( 'X-21-vtodo4-vlocation3-empty' );

        $this->parseCalendarTest( 1, $c );
    }
}
