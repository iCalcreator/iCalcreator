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

use DateTime;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use PHPUnit\Framework\TestCase;

/**
 * class PropSortTest
 *
 * @since  2.41.68 - 2022-10-09
 */
class PropSortTest extends TestCase
{
    private static function createExp( $createNo, $expNo ) : string
    {
        static $createExp = ' created %d, expected %d';
        return sprintf( $createExp, $createNo, $expNo );
    }
    
    /**
     * @var string
     */
    private static string $xPos = 'X-pos';

    /**
     * Test sorting on ATTENDEE
     *
     * @test
     */
    public function sortAttendeeTest() : void
    {
        $calendar  = new Vcalendar();

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 3 ))
            ->setAttendee( 'test2@kigkonsult.se' )
            ->setXprop( self::$xPos, 3 );

        $calendar->newVevent()
            ->setComment( self::createExp( 2, 4 ))
            ->setAttendee( 'test4@kigkonsult.se' )
            ->setXprop( self::$xPos, 4 );

        $calendar->newVevent()
            ->setComment( self::createExp( 3, 2 ))
            ->setAttendee( 'test1@kigkonsult.se' )
            ->setAttendee( 'test3@kigkonsult.se' )
            ->setXprop( self::$xPos, 2 );

        $calendar->newVevent()
            ->setComment( self::createExp( 4, 2 ))
            ->setAttendee()
            ->setXprop( self::$xPos, 1 );

        $calendar->sort( Vcalendar::ATTENDEE );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on CATEGORIES
     *
     * @test
     */
    public function sortCategoriesTest() : void
    {
        $calendar  = new Vcalendar();

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 4 ))
            ->setCategories( 'category3' )
            ->setXprop( self::$xPos, 4 );

        $calendar->newVevent()
            ->setComment( self::createExp( 2, 5 ))
            ->setCategories( 'category4' )
            ->setXprop( self::$xPos, 5 );

        $calendar->newVevent()
            ->setComment( self::createExp( 3,3 ))
            ->setCategories( 'category2, category6' )
            ->setXprop( self::$xPos, 3 );

        $calendar->newVavailability()
            ->setComment( self::createExp( 4, 2 ))
            ->setCategories( 'category1' )
            ->setXprop( self::$xPos, 2 );

        $calendar->newVevent()
            ->setComment( self::createExp( 5, 1 ))
            ->setCategories()
            ->setXprop( self::$xPos, 1 );

        $calendar->sort( Vcalendar::CATEGORIES );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on CONTACT
     *
     * @test
     */
    public function sortContactTest() : void
    {
        $calendar  = new Vcalendar();

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 4 ))
            ->setContact( 'contact3@kigkonsult.se' )
            ->setXprop( self::$xPos, 4 );

        $calendar->newVevent()
            ->setComment( self::createExp( 2, 5 ))
            ->setContact( 'contact4@kigkonsult.se' )
            ->setXprop( self::$xPos, 5 );

        $calendar->newVevent()
            ->setComment( self::createExp( 3, 3 ))
            ->setContact( 'contact2@kigkonsult.se' )
            ->setXprop( self::$xPos, 3 );

        $calendar->newVavailability()
            ->setComment( self::createExp( 4, 2 ))
            ->setContact( 'contact1@kigkonsult.se' )
            ->setXprop( self::$xPos, 2 );

        $calendar->newVevent()
            ->setComment( self::createExp( 5, 1 ))
            ->setContact()
            ->setXprop( self::$xPos, 1 );

        $calendar->sort( Vcalendar::CONTACT );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on DTSTAMP
     *
     * @test
     */
    public function sortDtstampTest() : void
    {
        $calendar  = new Vcalendar();

        $timeStamp = DateTimeFactory::factory( null, Vcalendar::UTC )->setTime( 1, 1, 1 );
        $e1 = new Vevent( $calendar->getConfig());
        $e1->setDtstamp( $timeStamp )
            ->setComment( 'DTSTAMP=' . $timeStamp->format( 'YmdHis e' ) . self::createExp( 1, 1 ))
            ->setXprop( self::$xPos, 1 );

        $timeStamp = DateTimeFactory::factory( null, Vcalendar::UTC )
            ->setTime( 2, 2, 2 );
        $e2 = new Vevent( $calendar->getConfig());
        $e2->setDtstamp( $timeStamp )
            ->setComment( 'DTSTAMP=' . $timeStamp->format( 'YmdHis e' ) . self::createExp( 2, 2 ))
            ->setXprop( self::$xPos, 2 );

        $timeStamp = DateTimeFactory::factory( null, Vcalendar::UTC )
            ->setTime( 3, 3, 3 );
        $e3 = new Vevent( $calendar->getConfig());
        $e3->setDtstamp( $timeStamp )
            ->setComment( 'DTSTAMP=' . $timeStamp->format( 'YmdHis e' ) . self::createExp( 3, 3 ))
            ->setXprop( self::$xPos, 3 );

        $timeStamp = DateTimeFactory::factory( null, Vcalendar::UTC )
            ->setTime( 4, 4, 4 );
        $e4 = new Vevent( $calendar->getConfig());
        $e4->setDtstamp( $timeStamp )
            ->setComment( 'DTSTAMP=' . $timeStamp->format( 'YmdHis e' ) . self::createExp( 4, 4 ))
            ->setXprop( self::$xPos, 4 );

        $calendar->setComponent( $e4 );
        $calendar->setComponent( $e3 );
        $calendar->setComponent( $e2 );
        $calendar->setComponent( $e1 );

        $calendar->sort( Vcalendar::DTSTAMP );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on DTSTART
     *
     * @test
     */
    public function sortDtstartTest() : void
    {
        $calendar  = new Vcalendar();

        $e1 = $calendar->newVevent( new DateTime( '2003-03-03 03:03:03' ));
        $e1->setComment( 'DTSTART=' . $e1->getDtstart()->format( 'YmdHis e' ) . self::createExp( 1, 3 ))
            ->setXprop( self::$xPos, 3 );

        $e2 = $calendar->newVevent(
            new DateTime( '2003-03-03 03:03:03' ),
            new DateTime( '2003-03-03 05:05:05' )
        );
        $e2->setComment( 'DTSTART=' . $e2->getDtstart()->format( 'YmdHis e' ) . self::createExp( 2, 5 ))
            ->setXprop( self::$xPos, 5 );

        $e3 = $calendar->newVevent(
            new DateTime( '2003-03-03 03:03:03' ),
            new DateTime( '2003-03-03 04:04:04' )
        );
        $e3->setComment( 'DTSTART=' . $e3->getDtstart()->format( 'YmdHis e' ) . self::createExp( 3, 4 ))
            ->setXprop( self::$xPos, 4 );

        $e4 = $calendar->newVevent(
            new DateTime( '2003-03-03 03:03:03' ),
            null,
            'PT3H'
        );
        $e4->setComment( 'DTSTART=' . $e4->getDtstart()->format( 'YmdHis e' ) . self::createExp( 4, 6 ))
            ->setXprop( self::$xPos, 6 );

        $e5 = $calendar->newVevent( new DateTime( '2002-02-02 02:02:02' ));
        $e5->setComment( 'DTSTART=' . $e5->getDtstart()->format( 'YmdHis e' ) . self::createExp( 5, 2 ))
            ->setXprop( self::$xPos, 2 );

        $e6 = $calendar->newVavailability( 'BUSY', new DateTime( '2001-01-01 01:01:01' ));
        $e6->setComment( 'DTSTART=' . $e6->getDtstart()->format( 'YmdHis e' ) . self::createExp( 6, 1 ))
            ->setXprop( self::$xPos, 1 );

        $calendar->sort(); //  Vcalendar::DTSTART );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on LOCATION
     *
     * @test
     */
    public function sortLocationTest() : void
    {
        $calendar  = new Vcalendar();

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 4 ))
            ->setLocation( 'location3' )
            ->setXprop( self::$xPos, 4 );

        $calendar->newVevent()
            ->setComment( self::createExp( 2, 5 ))
            ->setLocation( 'location4' )
            ->setXprop( self::$xPos, 5 );

        $calendar->newVevent()
            ->setComment( self::createExp( 3, 3 ))
            ->setLocation( 'location2' )
            ->setXprop( self::$xPos, 3 );

        $calendar->newVavailability()
            ->setComment( self::createExp( 4, 2 ))
            ->setLocation( 'location1' )
            ->setXprop( self::$xPos, 2 );

        $calendar->newVevent()
            ->setComment( self::createExp( 5, 1 ))
            ->setLocation()
            ->setXprop( self::$xPos, 1 );

        $calendar->sort( Vcalendar::LOCATION );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on ORGANIZER
     *
     * @test
     */
    public function sortOrganizerTest() : void
    {
        $calendar  = new Vcalendar();

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 4 ))
            ->setOrganizer( 'chair3@kigkonsult.se' )
            ->setXprop( self::$xPos, 4 );

        $calendar->newVevent()
            ->setComment( self::createExp( 2, 5 ))
            ->setOrganizer( 'chair4@kigkonsult.se' )
            ->setXprop( self::$xPos, 5 );

        $calendar->newVevent()
            ->setComment( self::createExp( 3, 3 ))
            ->setOrganizer( 'chair2@kigkonsult.se' )
            ->setXprop( self::$xPos, 3 );

        $calendar->newVavailability()
            ->setComment( self::createExp( 4, 2 ))
            ->setOrganizer( 'chair1@kigkonsult.se' )
            ->setXprop( self::$xPos, 2 );

        $calendar->newVevent()
            ->setComment( self::createExp( 5, 1 ))
            ->setOrganizer()
            ->setXprop( self::$xPos, 1 );

        $calendar->sort( Vcalendar::ORGANIZER );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on PRIORITY
     *
     * @test
     */
    public function sortPriorityTest() : void
    {
        $calendar  = new Vcalendar();

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 4 ))
            ->setPriority( 4 )
            ->setXprop( self::$xPos, 4 );

        $calendar->newVevent()
            ->setComment( self::createExp( 2, 5 ))
            ->setPriority( 5 )
            ->setXprop( self::$xPos, 5 );

        $calendar->newVevent()
            ->setComment( self::createExp( 3, 3 ))
            ->setPriority( 3 )
            ->setXprop( self::$xPos, 3 );

        $calendar->newVavailability()
            ->setComment( self::createExp( 4, 2 ))
            ->setPriority( 2 )
            ->setXprop( self::$xPos, 2 );

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 1 ) )
            ->setPriority()
            ->setXprop( self::$xPos, 1 );

        $calendar->sort( Vcalendar::PRIORITY );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on RELATED-TO
     *
     * @test
     */
    public function sortRelatedToTest() : void
    {
        $calendar  = new Vcalendar();

        $e1   = new Vevent( $calendar->getConfig());
        $uid1 = 'UID-3';
        $e1->setUid( $uid1 )->setComment( self::createExp( 1, 3 ))
            ->setXprop( self::$xPos, 3 );

        $e2   = new Vevent( $calendar->getConfig());
        $uid2 = 'UID-2';
        $e2->setUid( $uid2 )
            ->setComment( self::createExp( 2, 2 ))
            ->setXprop( self::$xPos, 2 );

        $e3   = new Vevent( $calendar->getConfig());
        $uid3 = 'UID-1';
        $e3->setUid( $uid3 )
            ->setComment( self::createExp( 3, 1 ))
            ->setComment( 'related-to created 1 AND created 2' )
            ->setRelatedto( $uid1 )
            ->setRelatedto( $uid2 )
            ->setXprop( self::$xPos, 1 );

        $calendar->setComponent( $e1 );
        $calendar->setComponent( $e2 );
        $calendar->setComponent( $e3 );

        $calendar->sort( Vcalendar::RELATED_TO );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on RESOURCES
     *
     * @test
     */
    public function sortResourcesTest() : void
    {
        $calendar  = new Vcalendar();

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 3 ))
            ->setResources( 'resource2 ' )
            ->setXprop( self::$xPos, 3 );

        $calendar->newVevent()
            ->setComment( self::createExp( 2, 4 ))
            ->setResources( 'resource3' )
            ->setXprop( self::$xPos, 4 );

        $calendar->newVevent()
            ->setComment( self::createExp( 3, 2 ))
            ->setResources( 'resource1' )
            ->setXprop( self::$xPos, 2 );

        $calendar->newVevent()
            ->setComment( self::createExp( 4, 1 ))
            ->setResources()
            ->setXprop( self::$xPos, 1 );

        $calendar->sort( Vcalendar::RESOURCES );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on STATUS
     *
     * @test
     */
    public function sortStatusTest() : void
    {
        $calendar  = new Vcalendar();

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 3 ))
            ->setStatus( 'CONFIRMED' )
            ->setXprop( self::$xPos, 3 );

        $calendar->newVevent()
            ->setComment( self::createExp( 2, 4 ))
            ->setStatus( 'TENTATIVE' )
            ->setXprop( self::$xPos, 4 );

        $calendar->newVevent()
            ->setComment( self::createExp( 3, 2 ))
            ->setStatus( 'CANCELLED' )
            ->setXprop( self::$xPos, 2 );

        $calendar->newVevent()
            ->setComment( self::createExp( 4, 1 ))
            ->setStatus()
            ->setXprop( self::$xPos, 1 );

        $calendar->sort( Vcalendar::STATUS );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on SUMMARY
     *
     * @test
     */
    public function sortSummaryTest() : void
    {
        $calendar  = new Vcalendar();

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 4 ))
            ->setSummary( 'SUMMARY 3' )
            ->setXprop( self::$xPos, 4 );

        $calendar->newVevent()
            ->setComment( self::createExp( 2, 5 ))
            ->setSummary( 'SUMMARY 4' )
            ->setXprop( self::$xPos, 5 );

        $calendar->newVevent()
            ->setComment( self::createExp( 3, 3 ))
            ->setSummary( 'SUMMARY 2' )
            ->setXprop( self::$xPos, 3 );

        $calendar->newVavailability()
            ->setComment( self::createExp( 4, 2 ))
            ->setSummary( 'SUMMARY 1' )
            ->setXprop( self::$xPos, 2 );

        $calendar->newVevent()
            ->setComment( self::createExp( 5, 1 ))
            ->setSummary()
            ->setXprop( self::$xPos, 1 );

        $calendar->sort( Vcalendar::SUMMARY );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on UID
     *
     * @test
     */
    public function sortUidTest() : void
    {
        $calendar  = new Vcalendar();
        $config    = $calendar->getConfig();

        $e1  = new Vevent( $config);
        $uid = 'UID-1';
        $e1->setUid( $uid );
        $e1->setComment( self::createExp( 1, 1 ))
            ->setComment( 'UID=' . $uid )
            ->setXprop( self::$xPos, 1 );

        $e2 = new Vevent( $config );
        $uid = 'UID-2';
        $e2->setUid( $uid )
            ->setComment( self::createExp( 2, 2 ))
            ->setComment( 'UID=' . $uid )
            ->setXprop( self::$xPos, 2 );

        $e3 = new Vevent( $config );
        $uid = 'UID-3';
        $e3->setUid( $uid )
            ->setComment( self::createExp( 3, 3 ))
            ->setComment( 'UID=' . $uid )
            ->setXprop( self::$xPos, 3 );

        $e4 = new Vavailability( $config );
        $uid = 'UID-4';
        $e4->setUid( $uid )
            ->setComment( self::createExp( 4, 4 ))
            ->setComment( 'UID=' . $uid )
            ->setXprop( self::$xPos, 4 );

        $calendar->setComponent( $e4 )
            ->setComponent( $e3 )
            ->setComponent( $e2 )
            ->setComponent( $e1 )
            ->sort( Vcalendar::UID );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }

    /**
     * Test sorting on URL
     *
     * @test
     */
    public function sortUrlTest() : void
    {
        $calendar  = new Vcalendar();

        $calendar->newVevent()
            ->setComment( self::createExp( 1, 4 ))
            ->setUrl( 'http://URL3.kigkonsult.se' )
            ->setXprop( self::$xPos, 4 );

        $calendar->newVevent()
            ->setComment( self::createExp( 2, 5 ))
            ->setUrl( 'http://URL4.kigkonsult.se' )
            ->setXprop( self::$xPos, 5 );

        $calendar->newVevent()
            ->setComment( self::createExp( 3, 3 ))
            ->setUrl( 'http://URL2.kigkonsult.se' )
            ->setXprop( self::$xPos, 3 );

        $calendar->newVavailability()
            ->setComment( self::createExp( 4, 2 ))
            ->setUrl( 'http://URL1.kigkonsult.se' )
            ->setXprop( self::$xPos, 2 );

        $calendar->newVevent()
            ->setComment( self::createExp( 5, 1 ))
            ->setUrl()
            ->setXprop( self::$xPos, 1 );

        $calendar->sort( Vcalendar::URL );

        foreach( $calendar->getComponents() as $xc =>$component ) {
            $this->assertSame(
                (string) ( 1 + $xc ),
                $component->getXprop( self::$xPos )[1]
            );
        } // end foreach
    }
}
