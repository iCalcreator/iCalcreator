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
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use PHPUnit\Framework\TestCase;

/**
 * class VcalendarTest, testing Vcalendar properties AND (the default) components UID/DTSTAMP properties
 *    CALSCALE
 *    METHOD
 *    VERSION
 *    PRODID (implicit)
 *    Not X-property, tested in MiscTest
 *
 * @since  2.39.1 - 2021-06-26
 */
class VcalendarTest extends TestCase
{
    private static $ERRFMT = "Error %sin case #%s, %s <%s>->%s()";

    /**
     * Testing Vcalendar config
     *
     * @test
     */
    public function vcalendarTest1()
    {
        $config = [
            Vcalendar::ALLOWEMPTY => false,
            Vcalendar::UNIQUE_ID  => 'kigkonsult.se',
        ];
        $vcalendar    = new Vcalendar( $config );

        $this->assertEquals( $config[Vcalendar::ALLOWEMPTY], $vcalendar->getConfig( Vcalendar::ALLOWEMPTY ));
        $this->assertEquals( $config[Vcalendar::UNIQUE_ID],  $vcalendar->getConfig( Vcalendar::UNIQUE_ID ));

        $vcalendar    = new Vcalendar();

        $this->assertEquals( true, $vcalendar->getConfig( Vcalendar::ALLOWEMPTY ));
        $this->assertEquals( '', $vcalendar->getConfig( Vcalendar::UNIQUE_ID ));

        $vcalendar->setConfig( Vcalendar::LANGUAGE, 'EN' );
        $this->assertEquals( 'EN',                 $vcalendar->getConfig( Vcalendar::LANGUAGE ));
        $vcalendar->deleteConfig( Vcalendar::LANGUAGE );
        $this->assertFalse( $vcalendar->getConfig( Vcalendar::LANGUAGE ));

        $vcalendar->deleteConfig( Vcalendar::ALLOWEMPTY );
        $this->assertTrue( $vcalendar->getConfig( Vcalendar::ALLOWEMPTY ));

        $vcalendar->deleteConfig( Vcalendar::UNIQUE_ID );
        $this->assertEquals( '', $vcalendar->getConfig( Vcalendar::UNIQUE_ID ));
    }

    /**
     * Testing Component with empty config, issue #91
     *
     * @test
     */
    public function vcalendarTest2()
    {
        $vTimezone = new Vtimezone();
        $standard  = $vTimezone->newStandard();
        $this->assertTrue( $standard instanceof Standard );
    }

    /**
     * vcalendarTest10 provider
     */
    public function vcalendarTest10Provider()
    {
        $dataArr = [];

        $value     = 'GREGORIAN';
        $dataArr[] = [
            1,
            Vcalendar::CALSCALE,
            $value,
            $value,
            ':' . $value
        ];

        $value = Vcalendar::P_BLIC;
        $dataArr[] = [
            5,
            Vcalendar::METHOD,
            $value,
            $value,
            ':' . $value
        ];
/*
        $value = 'Hejsan-Hopp';
        $dataArr[] = [
            9,
            Vcalendar::PRODID,
            $value,
            $value,
            ':' . $value
        ];
*/
        $value = '2.1';
        $dataArr[] = [
            19,
            Vcalendar::VERSION,
            $value,
            $value,
            ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing Vcalendar
     *
     * @test
     * @dataProvider vcalendarTest10Provider
     * @param int    $case
     * @param string $propName
     * @param mixed  $value
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function vcalendarTest10( $case, $propName, $value, $expectedGet, $expectedString )
    {
        $vcalendar = Vcalendar::factory();

        $getMethod    = StringFactory::getGetMethodName( $propName );
        $createMethod = StringFactory::getCreateMethodName( $propName );
        $deleteMethod = StringFactory::getDeleteMethodName( $propName );
        $setMethod    = StringFactory::getSetMethodName( $propName );
        $vcalendar->{$setMethod}( $value );
        $getValue = $vcalendar->{$getMethod}();
        $this->assertEquals(
            $expectedGet,
            $getValue,
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'Vcalendar', $getMethod )
        );
        $this->assertEquals(
            strtoupper( $propName ) . $expectedString,
            trim( $vcalendar->{$createMethod}() ),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'Vcalendar', $createMethod )
        );

        switch( $propName ) {
            case Vcalendar::CALSCALE :
                $vcalendar->{$deleteMethod}();
                $this->assertNotFalse(
                    $vcalendar->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, 'Vcalendar', $getMethod )
                );
                break;
            case Vcalendar::METHOD :
                $vcalendar->{$deleteMethod}();
                $this->assertFalse(
                    $vcalendar->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, 'Vcalendar', $getMethod )
                );
                $vcalendar->{$setMethod}( $value );
                break;
            case Vcalendar::VERSION :
                break;
        }

        $v = $vcalendar->newVevent();
        $v->deleteUid();
        $this->assertNotFalse(
            $v->getUid(),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'VEVENT', 'getUid' )
        );
        $v->deleteDtstamp();
        $this->assertNotFalse(
            $v->getDtstamp(),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'VEVENT', 'getDtstamp' )
        );

        $calendar1String = $vcalendar->createCalendar();

        $vcalendar2 = new Vcalendar();
        $vcalendar2->parse( $calendar1String );
        if( Vcalendar::VERSION == $propName ) {
            $vcalendar2->{$setMethod}( $value );
        }
        $this->assertEquals(
            $calendar1String,
            $vcalendar2->createCalendar(),
            sprintf( self::$ERRFMT, null, $case, __FUNCTION__, 'Error in calendar compare', null )
        );

        unset( $vcalendar, $vcalendar2 );
    }

    /**
     * Testing Vcalendar component management
     *
     * @test
     * @throws Exception
     */
    public function vcalendarTest20()
    {
        $vcalendar = new Vcalendar();

        $v = new Vevent();
        $uid = $v->getUid();
        $vcalendar->setComponent( $v, 6 );

        $v2 = $vcalendar->getComponent( 6 );
        $this->assertEquals( $uid,  $v2->getUid());

        $date = DateTimeFactory::factory( 'now', Vcalendar::UTC );
        $v2->setDtstart( $date );
        $vcalendar->setComponent( $v2, 6 );
        $v2 = $vcalendar->getComponent( 6 );
        $this->assertEquals( $date, $v2->getDtstart());

        $vcalendar->deleteComponent( 6, false );
        $this->assertFalse( $vcalendar->getComponent( 6 ));
        $this->assertFalse( $vcalendar->getComponent());

        $this->assertTrue(
            ( 0 == $vcalendar->countComponents()),
            'deleteComponent-error 1, has ' . $vcalendar->countComponents()
        );


        for( $x = 1; $x <= 12; $x++ ) {
            $vx1   = $vcalendar->newVevent();
            $vx1->setXprop( 'X-SET_NO', (string) $x );
        }

        for( $x = 13; $x <= 14; $x++ ) {
            $vx1   = $vcalendar->newVtodo();
            $vx1->setXprop( 'X-SET_NO', (string) $x );
        }
        for( $x = 15; $x <= 30; $x++ ) {
            $vx1   = $vcalendar->newVevent();
            $vx1->setXprop( 'X-SET_NO', (string) $x );
        }
        $this->assertTrue(
            ( 30 == $vcalendar->countComponents()),
            'deleteComponent-error 2, has ' . $vcalendar->countComponents()
        );

        $testStr = 'Testing this #';

        $testArr = [];

        $value = $testStr . 1;
        $testArr[Vcalendar::CATEGORIES] = [ 1, $value ];
        $v     = $vcalendar->getComponent( 1 );
        $v->setCategories( $value );
        $v->setXprop( 'X-VALUE', $value );
        $v->setComment( 1 ); // remember $x
        $v->setXprop( 'X-UPD_NO', 1 );
        $vcalendar->replaceComponent( $v );

        $value = $testStr . 2;
        $testArr[Vcalendar::LOCATION] = [ 2, $value ];
        $v     = $vcalendar->getComponent( 2 );
        $v->setLocation( $value );
        $v->setComment( 2 ); // remember $x
        $v->setXprop( 'X-VALUE', $value );
        $v->setXprop( 'X-UPD_NO', 2 );
        $vcalendar->replaceComponent( $v );

        $value = $testStr . 3;
        $testArr[Vcalendar::SUMMARY] = [ 3, $value ];
        $v     = $vcalendar->getComponent( 3 );
        $v->setSummary( $value );
        $v->setComment( 3 ); // remember $x
        $v->setXprop( 'X-VALUE', $value );
        $v->setXprop( 'X-UPD_NO', 3 );
        $vcalendar->replaceComponent( $v );

        $value = $testStr . 4;
        $testArr[Vcalendar::RESOURCES] = [ 4, $value ];
        $v     = $vcalendar->getComponent( 4 );
        $v->setResources( $value );
        $v->setComment( 4 ); // remember $x
        $v->setXprop( 'X-VALUE', $value );
        $v->setXprop( 'X-UPD_NO', 4 );
        $vcalendar->replaceComponent( $v );


        $testArr[Vcalendar::PRIORITY] = [ 5, 5 ];
        $v = $vcalendar->getComponent( 5 );
        $v->setPriority( 5 );
        $v->setComment( 5 ); // remember $x
        $v->setXprop( 'X-VALUE', 5 );
        $v->setXprop( 'X-UPD_NO', 5 );
        $vcalendar->replaceComponent( $v );

        $testArr[Vcalendar::STATUS] = [ 6, Vcalendar::TENTATIVE ];
        $v = $vcalendar->getComponent( 6 );
        $v->setStatus( Vcalendar::TENTATIVE );
        $v->setComment( 6 ); // remember $x
        $v->setXprop( 'X-VALUE', Vcalendar::TENTATIVE );
        $v->setXprop( 'X-UPD_NO', 6 );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 7 . ' days', Vcalendar::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[Vcalendar::DTSTART] = [ 7, $dateStr ];
        $v = $vcalendar->getComponent( 7 );
        $v->setDtstart( $date );
        $v->setComment( 7 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $v->setXprop( 'X-UPD_NO', 7 );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 8 . ' days', Vcalendar::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[Vcalendar::DTSTAMP] = [ 8, $dateStr ];
        $v = $vcalendar->getComponent( 8 );
        $v->setDtstamp( $date );
        $v->setComment( 8 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $v->setXprop( 'X-UPD_NO', 8 );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 9 . ' days', Vcalendar::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[Vcalendar::DTEND] = [ 9, $dateStr ];
        $v = $vcalendar->getComponent( 9 );
        $v->setDtend( $date );
        $v->setComment( 9 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 10 . ' days', Vcalendar::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[Vcalendar::CREATED] = [ 10, $dateStr ];
        $v = $vcalendar->getComponent( 10 );
        $v->setCreated( $date );
        $v->setComment( 10 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 11 . ' days', Vcalendar::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[Vcalendar::LAST_MODIFIED] = [ 11, $dateStr ];
        $v = $vcalendar->getComponent( 11 );
        $v->setLastmodified( $date );
        $v->setComment( 11 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 7 . ' days', Vcalendar::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[Vcalendar::RECURRENCE_ID] = [ 12, $dateStr ];
        $v = $vcalendar->getComponent( 12 );
        $v->setRecurrenceid( $date );
        $v->setComment( 12 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );


        $date    = DateTimeFactory::factory( '+' . 13 . ' days', Vcalendar::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[Vcalendar::COMPLETED] = [ 13, $dateStr ]; // Vtodo
        $v = $vcalendar->getComponent( 13 );
        $v->setCompleted( $date );
        $v->setComment( 13 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 14 . ' days', Vcalendar::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[Vcalendar::DUE] = [ 14, $dateStr ]; // Vtodo
        $v = $vcalendar->getComponent( 14 );
        $v->setDue( $date );
        $v->setComment( 14 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );


        $contact  = 'test.this.contact@exsample.com';
        $testArr[Vcalendar::CONTACT] = [ 15, $contact ];
        $v  = $vcalendar->getComponent( 15 );
        $v->setContact( $contact );
        $v->setComment( 15 ); // remember $x
        $v->setXprop( 'X-VALUE', $contact );
        $vcalendar->replaceComponent( $v );

        $attendee = 'MAILTO:test.this.attendee@exsample.com';
        $testArr[Vcalendar::ATTENDEE] = [ 16, $attendee ];
        $v = $vcalendar->getComponent( 16 );
        $v->setAttendee( $attendee );
        $v->setComment( 16 ); // remember $x
        $v->setXprop( 'X-VALUE', $attendee );
        $vcalendar->replaceComponent( $v );

        $organizer = 'MAILTO:test.this.organizer@exsample.com';
        $testArr[Vcalendar::ORGANIZER] = [ 17, $organizer ];
        $v         = $vcalendar->getComponent( 17 );
        $v->setOrganizer( $organizer );
        $v->setComment( 17 ); // remember $x
        $v->setXprop( 'X-VALUE', $organizer );
        $vcalendar->replaceComponent( $v );

        $relatedTo = 'test this related-to';
        $testArr[Vcalendar::RELATED_TO] = [ 18, $relatedTo ];
        $v         = $vcalendar->getComponent( 18 );
        $v->setRelatedto( $relatedTo );
        $v->setComment( 18 ); // remember $x
        $v->setXprop( 'X-VALUE', $relatedTo );
        $vcalendar->replaceComponent( $v );

        $url = 'http://test.this.url@exsample.com';
        $testArr[Vcalendar::URL] = [ 19, $url ];
        $v   = $vcalendar->getComponent( 19 );
        $v->setUrl( $url );
        $v->setComment( 19 ); // remember $x
        $v->setXprop( 'X-VALUE', $url );
        $vcalendar->replaceComponent( $v );

        $uid = 'test this uid';
        $testArr[Vcalendar::UID] = [ 20, $uid ];
        $v   = $vcalendar->getComponent( 20 );
        $v->setUid( $uid );
        $v->setComment( 20 ); // remember $x
        $v->setXprop( 'X-VALUE', $uid );
        $vcalendar->setComponent( $v, 20 );

//        error_log( __FUNCTION__ . ' calendar : ' . var_export( $vcalendar, true )); // test ###

        foreach( $testArr as $propName => $testValues ) {
            // fetch on uid
            $v = $vcalendar->getComponent( [ $propName => $testValues[1] ] );
            $this->assertNotFalse(
                $v,
                'getComponent not-found-error 1 for #' . $testValues[0] . ' : ' . $propName
            );
            // check test case number
            $ordNo = $v->getComment();
            $this->assertEquals(
                $testValues[0],
                $ordNo,
                'getComponent-error 2 for #' . $testValues[0] . ' : ' . $propName
            );
            // check xProp values
            $this->assertEquals(
                $testValues[1],
                $v->getXprop( 'X-VALUE' )[1],
                'getComponent-error 3 for #' . $testValues[0] . ' : ' . $propName
            );
        } // end foreach

        // check fetch on config compsinfo
        foreach( $vcalendar->getConfig( Vcalendar::COMPSINFO ) as $cix => $compInfo ) {

            $v = $vcalendar->getComponent( $compInfo['uid'] ); // note lower case

            $this->assertEquals(
                $compInfo['type'],
                $v->getCompType(),
                'getComponent-error 5 for #' . $testValues[0] . ' : ' . $propName
            );

        }

        // fetch all components
        $compArr = [];
        while( $comp = $vcalendar->getComponent()) {
            $compArr[] = $comp;
        }

        // check fetch on type and order number
        $v = $vcalendar->getComponent( Vcalendar::VTODO, 1 );
        $v = $vcalendar->getComponent( Vcalendar::VTODO, 2 );
        $this->assertFalse( $vcalendar->getComponent( Vcalendar::VTODO, 3 ) );

        // check number of components
        $this->assertTrue(
            ( 30 == $vcalendar->countComponents() ),
            'deleteComponent-error 6, has ' . $vcalendar->countComponents()
        );

        for( $x = 18; $x <= 1; $x-- ) {
            $this->assertTrue(
                $vcalendar->deleteComponent(  Vcalendar::VEVENT, $x ),
                'deleteComponent-error 7 on #' . $x
            );
        }
        while( $vcalendar->deleteComponent(  Vcalendar::VEVENT, false ) ) {
            continue;
        }
        $this->assertFalse(
            $vcalendar->deleteComponent(  Vcalendar::VEVENT, false ),
            'deleteComponent-error 8'
        );
        $this->assertTrue(
            ( 2 == $vcalendar->countComponents() ),
            'deleteComponent-error 9, has ' . $vcalendar->countComponents()
        );

        while( $vcalendar->deleteComponent(  Vcalendar::VTODO, false ) ) {
            continue;
        }
        $this->assertFalse(
            $vcalendar->deleteComponent(  Vcalendar::VTODO, false ),
            'deleteComponent-error 10'
        );
        $this->assertTrue(
            ( 0 == $vcalendar->countComponents() ),
            'deleteComponent-error 11, has ' . $vcalendar->countComponents()
        );

        // check components are set in order
        foreach( $compArr as $comp ) {
            $vcalendar->setComponent( $comp );
        }
        $x = 0;
        while( $comp = $vcalendar->getComponent()) {
            $x += 1;
            $this->assertEquals(
                $x,
                $comp->getXprop( 'X-SET_NO' )[1],
                'setComponent-error 12, comp . ' . $x . ' is not in order'
            );
        }
        // check number of components
        $this->assertTrue(
            ( 30 == $vcalendar->countComponents() ),
            'deleteComponent-error 13, has ' . $vcalendar->countComponents()
        );
    }
}
