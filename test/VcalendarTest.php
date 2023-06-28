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

use Exception;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use PHPUnit\Framework\TestCase;

/**
 * class VcalendarTest, testing Vcalendar properties AND (the default) components UID/DTSTAMP properties
 *    CALSCALE
 *    METHOD
 *    VERSION
 *    PRODID (implicit)
 *    Not X-property, tested in PropEmptyTest
 *
 * @since  2.39.1 - 2021-06-26
 */
class VcalendarTest extends TestCase
{
    use GetPropMethodNamesTrait;
    /**
     * @var string
     */
    private static string $ERRFMT = "Error %sin case #%s, %s <%s>->%s()";

    /**
     * Testing Vcalendar config
     *
     * @test
     */
    public function vcalendarTest1() : void
    {
        $config = [
            IcalInterface::ALLOWEMPTY => false,
            IcalInterface::UNIQUE_ID  => 'kigkonsult.se',
        ];
        $vcalendar    = new Vcalendar( $config );

        $this->assertEquals( $config[IcalInterface::ALLOWEMPTY], $vcalendar->getConfig( IcalInterface::ALLOWEMPTY ));
        $this->assertEquals( $config[IcalInterface::UNIQUE_ID],  $vcalendar->getConfig( IcalInterface::UNIQUE_ID ));

        $vcalendar    = new Vcalendar();

        $this->assertEquals( true, $vcalendar->getConfig( IcalInterface::ALLOWEMPTY ));
        $this->assertEquals( '', $vcalendar->getConfig( IcalInterface::UNIQUE_ID ));

        $vcalendar->setConfig( IcalInterface::LANGUAGE, 'EN' );
        $this->assertEquals( 'EN',                 $vcalendar->getConfig( IcalInterface::LANGUAGE ));
        $vcalendar->deleteConfig( IcalInterface::LANGUAGE );
        $this->assertFalse( $vcalendar->getConfig( IcalInterface::LANGUAGE ));

        $vcalendar->deleteConfig( IcalInterface::ALLOWEMPTY );
        $this->assertTrue( $vcalendar->getConfig( IcalInterface::ALLOWEMPTY ));

        $vcalendar->deleteConfig( IcalInterface::UNIQUE_ID );
        $this->assertEquals( '', $vcalendar->getConfig( IcalInterface::UNIQUE_ID ));
    }

    /**
     * Testing Component with empty config, issue #91
     *
     * @test
     */
    public function vcalendarTest2() : void
    {
        $vTimezone = new Vtimezone();
        $standard  = $vTimezone->newStandard();
        $this->assertInstanceOf( Standard::class, $standard );
    }

    /**
     * vcalendarTest10 provider
     */
    public function vcalendarTest10Provider() : array
    {
        $dataArr = [];

        $value     = 'JULIAN'; // 'GREGORIAN';
        $dataArr[] = [
            1,
            IcalInterface::CALSCALE,
            $value,
            $value,
            ':' . $value
        ];

        $value = IcalInterface::P_BLIC;
        $dataArr[] = [
            5,
            IcalInterface::METHOD,
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
            IcalInterface::VERSION,
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
     * @param int $case
     * @param string $propName
     * @param mixed  $value
     * @param string $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function vcalendarTest10( int $case, string $propName, mixed $value, string $expectedGet, string $expectedString ) : void
    {
        $vcalendar = Vcalendar::factory();

        [ $createMethod, $deleteMethod, $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
        if( IcalInterface::VERSION !== $propName ) {
            $this->assertFalse(
                $vcalendar->{$isMethod}(),
                sprintf( self::$ERRFMT, null, $case . '-1', __FUNCTION__, Vcalendar::VCALENDAR, $isMethod )
            );
        }
        $vcalendar->{$setMethod}( $value );
        if( IcalInterface::VERSION !== $propName ) {
            $this->assertTrue(
                $vcalendar->{$isMethod}(),
                sprintf( self::$ERRFMT, null, $case . '-2', __FUNCTION__, Vcalendar::VCALENDAR, $isMethod )
            );
        }

        $getValue = $vcalendar->{$getMethod}();
        $this->assertEquals(
            $expectedGet,
            $getValue,
            sprintf( self::$ERRFMT, null, $case . '-3', __FUNCTION__, Vcalendar::VCALENDAR, $getMethod )
        );
        $this->assertEquals(
            strtoupper( $propName ) . $expectedString,
            trim( $vcalendar->{$createMethod}()),
            sprintf( self::$ERRFMT, null, $case . '-4', __FUNCTION__, Vcalendar::VCALENDAR, $createMethod )
        );

        switch( $propName ) {
            case IcalInterface::CALSCALE :
                $vcalendar->{$deleteMethod}();
                $this->assertNotFalse(
                    $vcalendar->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case . '-5', __FUNCTION__, Vcalendar::VCALENDAR, $getMethod )
                );
                break;
            case IcalInterface::METHOD :
                $vcalendar->{$deleteMethod}();
                $this->assertFalse(
                    $vcalendar->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case . '-6', __FUNCTION__, Vcalendar::VCALENDAR, $getMethod )
                );
                $vcalendar->{$setMethod}( $value );
                break;
            case IcalInterface::VERSION :
                break;
        }

        $calendar1String = $vcalendar->createCalendar();

        $vcalendar2 = new Vcalendar();
        $vcalendar2->parse( $calendar1String );
        if( IcalInterface::VERSION === $propName ) {
            $vcalendar2->{$setMethod}( $value );
        }
        $this->assertEquals(
            $calendar1String,
            $vcalendar2->createCalendar(),
            sprintf( self::$ERRFMT, null, $case . '-9', __FUNCTION__, 'Error in calendar compare', null )
        );
    }

    /**
     * Testing Vcalendar component management
     *
     * @test
     * @throws Exception
     */
    public function vcalendarTest20() : void
    {
        $vcalendar = new Vcalendar();

        $v = new Vevent();
        $uid = $v->getUid();
        $vcalendar->setComponent( $v, 6 );

        $v2 = $vcalendar->getComponent( 6 );
        $this->assertEquals( $uid,  $v2->getUid());

        $date = DateTimeFactory::factory( DateTimeFactory::$AT . time(), IcalInterface::UTC );
        $v2->setDtstart( $date );
        $vcalendar->setComponent( $v2, 6 );
        $v2 = $vcalendar->getComponent( 6 );
        $this->assertEquals( $date, $v2->getDtstart());

        $vcalendar->deleteComponent( 6 );
        $this->assertFalse( $vcalendar->getComponent( 6 ));
        $this->assertFalse( $vcalendar->getComponent());

        $this->assertSame(
            0, $vcalendar->countComponents(), 'deleteComponent-error 1, has ' . $vcalendar->countComponents()
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
        $this->assertSame(
            30, $vcalendar->countComponents(), 'deleteComponent-error 2, has ' . $vcalendar->countComponents()
        );

        $testStr = 'Testing this #';

        $testArr = [];

        $value = $testStr . 1;
        $testArr[IcalInterface::CATEGORIES] = [ 1, $value ];
        $v     = $vcalendar->getComponent( 1 ); // Vevent
        $v->setCategories( $value );
        $v->setXprop( 'X-VALUE', $value );
        $v->setComment( 1 ); // remember $x
        $v->setXprop( 'X-UPD_NO', 1 );
        $vcalendar->replaceComponent( $v );

        $value = $testStr . 2;
        $testArr[IcalInterface::LOCATION] = [ 2, $value ];
        $v     = $vcalendar->getComponent( 2 );
        $v->setLocation( $value );
        $v->setComment( 2 ); // remember $x
        $v->setXprop( 'X-VALUE', $value );
        $v->setXprop( 'X-UPD_NO', 2 );
        $vcalendar->replaceComponent( $v );

        $value = $testStr . 3;
        $testArr[IcalInterface::SUMMARY] = [ 3, $value ];
        $v     = $vcalendar->getComponent( 3 );
        $v->setSummary( $value );
        $v->setComment( 3 ); // remember $x
        $v->setXprop( 'X-VALUE', $value );
        $v->setXprop( 'X-UPD_NO', 3 );
        $vcalendar->replaceComponent( $v );

        $value = $testStr . 4;
        $testArr[IcalInterface::RESOURCES] = [ 4, $value ];
        $v     = $vcalendar->getComponent( 4 );
        $v->setResources( $value );
        $v->setComment( 4 ); // remember $x
        $v->setXprop( 'X-VALUE', $value );
        $v->setXprop( 'X-UPD_NO', 4 );
        $vcalendar->replaceComponent( $v );


        $testArr[IcalInterface::PRIORITY] = [ 5, 5 ];
        $v = $vcalendar->getComponent( 5 );
        $v->setPriority( 5 );
        $v->setComment( 5 ); // remember $x
        $v->setXprop( 'X-VALUE', 5 );
        $v->setXprop( 'X-UPD_NO', 5 );
        $vcalendar->replaceComponent( $v );

        $testArr[IcalInterface::STATUS] = [ 6, IcalInterface::TENTATIVE ];
        $v = $vcalendar->getComponent( 6 );
        $v->setStatus( IcalInterface::TENTATIVE );
        $v->setComment( 6 ); // remember $x
        $v->setXprop( 'X-VALUE', IcalInterface::TENTATIVE );
        $v->setXprop( 'X-UPD_NO', 6 );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 7 . ' days', IcalInterface::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[IcalInterface::DTSTART] = [ 7, $dateStr ];
        $v = $vcalendar->getComponent( 7 );
        $v->setDtstart( $date );
        $v->setComment( 7 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $v->setXprop( 'X-UPD_NO', 7 );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 8 . ' days', IcalInterface::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[IcalInterface::DTSTAMP] = [ 8, $dateStr ];
        $v = $vcalendar->getComponent( 8 );
        $v->setDtstamp( $date );
        $v->setComment( 8 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $v->setXprop( 'X-UPD_NO', 8 );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 9 . ' days', IcalInterface::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[IcalInterface::DTEND] = [ 9, $dateStr ];
        $v = $vcalendar->getComponent( 9 );
        $v->setDtend( $date );
        $v->setComment( 9 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 10 . ' days', IcalInterface::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[IcalInterface::CREATED] = [ 10, $dateStr ];
        $v = $vcalendar->getComponent( 10 );
        $v->setCreated( $date );
        $v->setComment( 10 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 11 . ' days', IcalInterface::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[IcalInterface::LAST_MODIFIED] = [ 11, $dateStr ];
        $v = $vcalendar->getComponent( 11 );
        $v->setLastmodified( $date );
        $v->setComment( 11 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 7 . ' days', IcalInterface::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[IcalInterface::RECURRENCE_ID] = [ 12, $dateStr ];
        $v = $vcalendar->getComponent( 12 );
        $v->setRecurrenceid( $date );
        $v->setComment( 12 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );


        $date    = DateTimeFactory::factory( '+' . 13 . ' days', IcalInterface::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[IcalInterface::COMPLETED] = [ 13, $dateStr ]; // Vtodo
        $v = $vcalendar->getComponent( 13 );
        $v->setCompleted( $date );
        $v->setComment( 13 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );

        $date    = DateTimeFactory::factory( '+' . 14 . ' days', IcalInterface::UTC );
        $dateStr = $date->format( DateTimeFactory::$YmdHis );
        $testArr[IcalInterface::DUE] = [ 14, $dateStr ]; // Vtodo
        $v = $vcalendar->getComponent( 14 );
        $v->setDue( $date );
        $v->setComment( 14 ); // remember $x
        $v->setXprop( 'X-VALUE', $dateStr );
        $vcalendar->replaceComponent( $v );


        $contact  = 'test.this.contact@exsample.com';
        $testArr[IcalInterface::CONTACT] = [ 15, $contact ];
        $v  = $vcalendar->getComponent( 15 );
        $v->setContact( $contact );
        $v->setComment( 15 ); // remember $x
        $v->setXprop( 'X-VALUE', $contact );
        $vcalendar->replaceComponent( $v );

        $attendee = 'MAILTO:test.this.attendee@exsample.com';
        $testArr[IcalInterface::ATTENDEE] = [ 16, $attendee ];
        $v = $vcalendar->getComponent( 16 );
        $v->setAttendee( $attendee );
        $v->setComment( 16 ); // remember $x
        $v->setXprop( 'X-VALUE', $attendee );
        $vcalendar->replaceComponent( $v );

        $organizer = 'MAILTO:test.this.organizer@exsample.com';
        $testArr[IcalInterface::ORGANIZER] = [ 17, $organizer ];
        $v         = $vcalendar->getComponent( 17 );
        $v->setOrganizer( $organizer );
        $v->setComment( 17 ); // remember $x
        $v->setXprop( 'X-VALUE', $organizer );
        $vcalendar->replaceComponent( $v );

        $relatedTo = 'test this related-to';
        $testArr[IcalInterface::RELATED_TO] = [ 18, $relatedTo ];
        $v         = $vcalendar->getComponent( 18 );
        $v->setRelatedto( $relatedTo );
        $v->setComment( 18 ); // remember $x
        $v->setXprop( 'X-VALUE', $relatedTo );
        $vcalendar->replaceComponent( $v );

        $url = 'http://test.this.url@exsample.com';
        $testArr[IcalInterface::URL] = [ 19, $url ];
        $v   = $vcalendar->getComponent( 19 );
        $v->setUrl( $url );
        $v->setComment( 19 ); // remember $x
        $v->setXprop( 'X-VALUE', $url );
        $vcalendar->replaceComponent( $v );

        $uid = 'test this uid';
        $testArr[IcalInterface::UID] = [ 20, $uid ];
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
                'getComponent not-found-error 1 for #' . $testValues[0] . ' : ' . $propName . ', search: ' . $testValues[1]
//               . ', has ' . PHP_EOL . $vcalendar->createCalendar()
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
        foreach( $vcalendar->getConfig( IcalInterface::COMPSINFO ) as $cix => $compInfo ) {

            $v = $vcalendar->getComponent( $compInfo['uid'] ); // note lower case

            $this->assertEquals(
                $compInfo['type'],
                $v->getCompType(),
                'getComponent-error 5 for #' . $testValues[0] . ' : ' . $propName
            );

        }

        // fetch all components
        $compArr = [];
        while( $component = $vcalendar->getComponent()) {
            $compArr[] = $component;
        }

        // check fetch on type and order number
        $v1 = $vcalendar->getComponent( IcalInterface::VTODO, 1 );
        $v2 = $vcalendar->getComponent( IcalInterface::VTODO, 2 );
        $this->assertFalse( $vcalendar->getComponent( IcalInterface::VTODO, 3 ));

        // check number of components
        $this->assertSame(
            30,
            $vcalendar->countComponents(),
            'deleteComponent-error 6, has ' . $vcalendar->countComponents()
        );

        for( $x = 18; $x <= 1; $x-- ) {
            $this->assertTrue(
                $vcalendar->deleteComponent(  IcalInterface::VEVENT, $x ),
                'deleteComponent-error 7 on #' . $x
            );
        }
//      while( $vcalendar->deleteComponent(  IcalInterface::VEVENT, false )) {
        while( $vcalendar->deleteComponent(  IcalInterface::VEVENT )) {
            continue;
        }
        $this->assertFalse(
//          $vcalendar->deleteComponent(  IcalInterface::VEVENT, false ),
            $vcalendar->deleteComponent(  IcalInterface::VEVENT ),
            'deleteComponent-error 8'
        );
        $this->assertSame(
            2,
            $vcalendar->countComponents(),
            'deleteComponent-error 9, has ' . $vcalendar->countComponents()
        );

        while( $vcalendar->deleteComponent(  IcalInterface::VTODO )) {
            continue;
        }
        $this->assertFalse(
            $vcalendar->deleteComponent(  IcalInterface::VTODO ),
            'deleteComponent-error 10'
        );
        $this->assertSame(
            0, $vcalendar->countComponents(), 'deleteComponent-error 11, has ' . $vcalendar->countComponents()
        );

        // check components are set in order
        foreach( $compArr as $component ) {
            $vcalendar->setComponent( $component );
        }

        foreach( $vcalendar->getComponents( Vcalendar::VEVENT ) as $component ) {
            $this->assertEquals(
                Vcalendar::VEVENT,
                $component->getCompType(),
                'getComponents-error 12, Vevent expected, got ' . $component->getCompType()
            );
        }

        $x = 0;
        foreach( $vcalendar->getComponents() as $component ) {
            ++$x;
            $this->assertEquals(
                $x,
                $component->getXprop( 'X-SET_NO' )[1],
                'getComponents-error 13, comp . ' . $x . ' is not in order'
            );
        }

        $x = 0;
        while( $component = $vcalendar->getComponent()) {
            ++$x;
            $this->assertEquals(
                $x,
                $component->getXprop( 'X-SET_NO' )[1],
                'getComponent-error 14, comp . ' . $x . ' is not in order'
            );
        }
        // check number of components
        $this->assertSame(
            30,
            $vcalendar->countComponents(),
            'countComponent-error 15, has ' . $vcalendar->countComponents()
        );
    }
}
